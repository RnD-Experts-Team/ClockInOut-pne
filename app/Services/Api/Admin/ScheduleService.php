<?php

namespace App\Services\Api\Admin;

use App\Models\MaintenanceRequest;
use App\Models\Schedule;
use App\Models\ScheduleShift;
use App\Models\TaskAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScheduleService
{
    public function getSchedules($request)
    {
        return Schedule::with(['creator', 'shifts.user'])

            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })

            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })

            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }
    public function getScheduleDetails(Schedule $schedule)
    {
        $schedule->load([
            'shifts.user',
            'shifts.taskAssignments.maintenanceRequest.store',
            'creator'
        ]);

        $availableTasks = MaintenanceRequest::where('status', 'open')
            ->with(['store', 'urgencyLevel'])
            ->get();

        // Get distinct shift types
        $shiftTypes = ScheduleShift::select('shift_type')
            ->whereNotNull('shift_type')
            ->distinct()
            ->pluck('shift_type')
            ->toArray();

        // Get distinct roles
        $scheduleRoles = ScheduleShift::select('role')
            ->whereNotNull('role')
            ->distinct()
            ->pluck('role')
            ->toArray();

        return [
            'schedule' => $schedule,
            'available_tasks' => $availableTasks,
            'shift_types' => $shiftTypes,
            'schedule_roles' => $scheduleRoles
        ];
    }
    public function createSchedule($request)
    {
        $scheduleData = json_decode($request->schedule_data, true);

        if (empty($scheduleData)) {
            throw new \Exception('Please add at least one shift to the schedule.');
        }

        $cleanName = trim(preg_replace('/\s+/', ' ', $request->name));

        $schedule = Schedule::create([
            'name' => $cleanName,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'week_start_date' => Carbon::parse($request->start_date)->startOfWeek(),
            'status' => $request->has('publish') ? 'published' : 'draft',
            'created_by' => Auth::id(),
        ]);

        foreach ($scheduleData as $item) {

            foreach ($item['shifts'] as $shift) {
                // Create the shift
                $scheduleShift = $schedule->shifts()->create([
                    'user_id' => $item['employee_id'],
                    'date' => $item['start_date'],
                    'start_time' => $shift['start'],
                    'end_time' => $shift['end'],
                    'shift_type' => $shift['type'] ?? 'regular',
                    'role' => $shift['role'] ?? 'general',
                    'color' => $shift['color'] ?? '#3b82f6',
                    'assignment_notes' => $shift['assignment_notes'] ?? null,
                ]);
                // ✅ Create task assignment AND update MaintenanceRequest
                if (!empty($shift['task_id'])) {

                    $taskAssignment = TaskAssignment::create([
                        'maintenance_request_id' => $shift['task_id'],
                        'assigned_user_id' => $item['employee_id'],
                        'schedule_shift_id' => $scheduleShift->id,
                        'assigned_at' => now(),
                        'due_date' => $item['start_date'],
                        'status' => 'pending',
                        'priority' => 'normal',
                        'assignment_notes' => $shift['assignment_notes'] ?? null,
                        'assigned_by' => Auth::id(),
                    ]);
                    // ✅ NEW: Update the MaintenanceRequest to reflect task assignment
                    $maintenanceRequest = MaintenanceRequest::find($shift['task_id']);

                    if ($maintenanceRequest) {

                        $maintenanceRequest->update([
                            'assignment_source' => 'task_assignment',
                            'current_task_assignment_id' => $taskAssignment->id,
                        ]);

                        Log::debug('Updated MaintenanceRequest from schedule creation', [
                            'maintenance_request_id' => $shift['task_id'],
                            'assignment_source' => 'task_assignment',
                            'task_assignment_id' => $taskAssignment->id,
                        ]);
                    }
                }
            }
        }

        return [
            'schedule' => $schedule,
            'message' => $request->has('publish')
                ? 'Schedule published successfully! Employees have been notified.'
                : 'Schedule saved as draft successfully.'
        ];
    }
    public function deleteSchedule(Schedule $schedule)
    {
        return DB::transaction(function () use ($schedule) {

            $schedule->shifts()->delete();
            $schedule->delete();

            return true;
        });
    }
    public function updateSchedule($request, Schedule $schedule)
    {
        $scheduleData = json_decode($request->schedule_data, true);

        $deletedShiftIds = $request->input('deleted_shift_ids')
            ? explode(',', $request->input('deleted_shift_ids'))
            : [];

        if (empty($scheduleData)) {
            throw new \Exception('Please add at least one shift to the schedule.');
        }

        $cleanName = trim(preg_replace('/\s+/', ' ', $request->name));

        DB::transaction(function () use ($request, $schedule, $scheduleData, $cleanName, $deletedShiftIds) {
            // Update schedule basic info
            $schedule->update([
                'name' => $cleanName,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'week_start_date' => Carbon::parse($request->start_date)->startOfWeek(),
                'status' => $request->has('publish') ? 'published' : $schedule->status,
            ]);
            // Get all existing shifts with their task assignments

            $existingShifts = $schedule->shifts()->with('taskAssignments')->get();
            $updatedShiftIds = [];

            foreach ($scheduleData as $item) {

                foreach ($item['shifts'] as $shiftData) {
                    // Try to find existing shift by exact match first
                    $existingShift = $existingShifts->where('user_id', $item['employee_id'])
                        ->where('date', $item['start_date'])
                        ->where('start_time', $shiftData['start'])
                        ->where('end_time', $shiftData['end'])
                        ->first();
                    // If no exact match, try to find any shift for this employee/date
                    if (!$existingShift) {
                        $existingShift = $existingShifts->where('user_id', $item['employee_id'])
                            ->where('date', $item['start_date'])
                            ->whereNotIn('id', $updatedShiftIds)
                            ->first();
                    }

                    if ($existingShift) {

                        $existingShift->update([
                            'start_time' => $shiftData['start'],
                            'end_time' => $shiftData['end'],
                            'shift_type' => $shiftData['type'] ?? $existingShift->shift_type ?? 'regular',
                            'role' => $shiftData['role'] ?? $existingShift->role ?? 'general',
                            'color' => $shiftData['color'] ?? $existingShift->color ?? '#3b82f6',
                            'assignment_notes' => $shiftData['assignment_notes'] ?? $existingShift->assignment_notes,
                        ]);

                        if (!empty($shiftData['task_id'])) {

                            $existingTaskAssignment = $existingShift->taskAssignments->first();

                            $scheduleDueDate = Carbon::parse(
                                $item['start_date'] . ' ' . $shiftData['end']
                            );

                            if ($existingTaskAssignment) {
                                // Update existing task assignment
                                $existingTaskAssignment->update([
                                    'maintenance_request_id' => $shiftData['task_id'],
                                    'assigned_user_id' => $item['employee_id'],
                                    'due_date' => $scheduleDueDate,
                                    'assignment_notes' => $shiftData['assignment_notes'] ?? null,
                                ]);

                                $taskAssignmentId = $existingTaskAssignment->id;
                                $taskAssignmentDueDate = $existingTaskAssignment->due_date;

                            } else {
                                // Create new task assignment

                                $newTaskAssignment = TaskAssignment::create([
                                    'maintenance_request_id' => $shiftData['task_id'],
                                    'assigned_user_id' => $item['employee_id'],
                                    'schedule_shift_id' => $existingShift->id,
                                    'assigned_at' => now(),
                                    'status' => 'pending',
                                    'due_date' => $scheduleDueDate,
                                    'priority' => 'normal',
                                    'assignment_notes' => $shiftData['assignment_notes'] ?? null,
                                    'assigned_by' => Auth::id(),
                                ]);

                                $taskAssignmentId = $newTaskAssignment->id;
                                $taskAssignmentDueDate = $newTaskAssignment->due_date;
                            }

                            $maintenanceRequest = MaintenanceRequest::find($shiftData['task_id']);
                            // ✅ UPDATE MAINTENANCE REQUEST
                            if ($maintenanceRequest) {
                                $maintenanceRequest->update([
                                    'due_date' => $taskAssignmentDueDate,
                                    'assignment_source' => 'task_assignment',
                                    'current_task_assignment_id' => $taskAssignmentId,
                                ]);
                            }

                        } else {
                            // ✅ REMOVE TASK ASSIGNMENT IF NO TASK

                            foreach ($existingShift->taskAssignments as $taskAssignment) {

                                $maintenanceRequest = $taskAssignment->maintenanceRequest;

                                if ($maintenanceRequest &&
                                    $maintenanceRequest->current_task_assignment_id == $taskAssignment->id) {

                                    $maintenanceRequest->update([
                                        'assignment_source' => null,
                                        'current_task_assignment_id' => null,
                                        'assigned_to' => null,
                                        'due_date' => null,
                                    ]);
                                }

                                $taskAssignment->delete();
                            }
                        }

                        $updatedShiftIds[] = $existingShift->id;

                    } else {

                        $scheduleShift = $schedule->shifts()->create([
                            'user_id' => $item['employee_id'],
                            'date' => $item['start_date'],
                            'start_time' => $shiftData['start'],
                            'end_time' => $shiftData['end'],
                            'shift_type' => $shiftData['type'] ?? 'regular',
                            'role' => $shiftData['role'] ?? 'general',
                            'color' => $shiftData['color'] ?? '#3b82f6',
                            'assignment_notes' => $shiftData['assignment_notes'] ?? null,
                        ]);

                        $updatedShiftIds[] = $scheduleShift->id;

                        if (!empty($shiftData['task_id'])) {

                            $scheduleDueDate = Carbon::parse(
                                $item['start_date'] . ' ' . $shiftData['end']
                            );

                            $taskAssignment = TaskAssignment::create([
                                'maintenance_request_id' => $shiftData['task_id'],
                                'assigned_user_id' => $item['employee_id'],
                                'schedule_shift_id' => $scheduleShift->id,
                                'assigned_at' => now(),
                                'status' => 'pending',
                                'due_date' => $scheduleDueDate,
                                'priority' => 'normal',
                                'assignment_notes' => $shiftData['assignment_notes'] ?? null,
                                'assigned_by' => Auth::id(),
                            ]);

                            $maintenanceRequest = MaintenanceRequest::find($shiftData['task_id']);

                            if ($maintenanceRequest) {

                                $maintenanceRequest->update([
                                    'due_date' => $taskAssignment->due_date,
                                    'assignment_source' => 'task_assignment',
                                    'current_task_assignment_id' => $taskAssignment->id,
                                ]);
                            }
                        }
                    }
                }
            }

            if (!empty($deletedShiftIds)) {

                $shiftsToDelete = $existingShifts->whereIn('id', $deletedShiftIds);

                foreach ($shiftsToDelete as $shift) {

                    foreach ($shift->taskAssignments as $taskAssignment) {

                        $maintenanceRequest = $taskAssignment->maintenanceRequest;

                        if ($maintenanceRequest &&
                            $maintenanceRequest->current_task_assignment_id == $taskAssignment->id) {

                            $maintenanceRequest->update([
                                'assignment_source' => null,
                                'current_task_assignment_id' => null,
                                'assigned_to' => null,
                                'due_date' => null,
                            ]);
                        }
                    }

                    $shift->taskAssignments()->delete();
                    $shift->delete();
                }
            }

            $shiftsToDelete = $existingShifts
                ->whereNotIn('id', $updatedShiftIds)
                ->whereNotIn('id', $deletedShiftIds);

            foreach ($shiftsToDelete as $shift) {

                foreach ($shift->taskAssignments as $taskAssignment) {

                    $maintenanceRequest = $taskAssignment->maintenanceRequest;

                    if ($maintenanceRequest &&
                        $maintenanceRequest->current_task_assignment_id == $taskAssignment->id) {

                        $maintenanceRequest->update([
                            'assignment_source' => null,
                            'current_task_assignment_id' => null,
                            'assigned_to' => null,
                            'due_date' => null,
                        ]);
                    }
                }

                $shift->taskAssignments()->delete();
                $shift->delete();
            }
        });

        return $request->has('publish')
            ? 'Schedule updated and published successfully!'
            : 'Schedule updated successfully.';
    }
    public function deleteShiftType(string $shiftType)
    {
        $shiftsCount = ScheduleShift::where('shift_type', $shiftType)->count();

        if ($shiftsCount > 0) {
            throw new \Exception(
                "Cannot delete shift type '{$shiftType}' because it's being used in {$shiftsCount} shift(s)."
            );
        }

        return true;
    }

    public function deleteRole(string $role)
    {
        $shiftsCount = ScheduleShift::where('role', $role)->count();

        if ($shiftsCount > 0) {
            throw new \Exception(
                "Cannot delete role '{$role}' because it's being used in {$shiftsCount} shift(s)."
            );
        }

        return true;
    }
    public function publish(Schedule $schedule)
    {
        if ($schedule->status !== 'draft') {
            throw new \Exception('Only draft schedules can be published.');
        }

        $schedule->update([
            'status' => 'published'
        ]);

        // NotificationService::sendScheduleNotification($schedule);

        return $schedule;
    }

    public function activate(Schedule $schedule)
    {
        if ($schedule->status !== 'published') {
            throw new \Exception('Only published schedules can be activated.');
        }

        $weekStart = Carbon::parse($schedule->start_date)->startOfWeek();

        Schedule::where('status', 'active')
            ->whereBetween('start_date', [
                $weekStart->format('Y-m-d'),
                $weekStart->copy()->endOfWeek()->format('Y-m-d')
            ])
            ->update([
                'status' => 'archived'
            ]);

        $schedule->update([
            'status' => 'active'
        ]);

        return $schedule;
    }
}