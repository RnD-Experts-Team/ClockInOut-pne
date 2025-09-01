<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ScheduleShift;
use App\Models\User;
use App\Models\MaintenanceRequest;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $schedules = Schedule::with(['creator', 'shifts.user'])
            ->when($request->search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.schedules.index', compact('schedules'));
    }

    public function create(Request $request)
    {
        // Get the week dates
        $startDate = $request->start_date ?
            Carbon::parse($request->start_date)->startOfWeek(Carbon::MONDAY) :
            Carbon::now()->startOfWeek(Carbon::MONDAY);

        $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);

        // Generate current week days
        $currentWeek = collect();
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $currentWeek->push($date->copy());
        }

        // Get active employees
        $employees = User::where('is_active', true)
            ->whereIn('role', ['user', 'admin'])
            ->orderBy('name')
            ->get();

        // Get available tasks for assignment
        $availableTasks = MaintenanceRequest::with(['store', 'urgencyLevel'])
            ->whereNotIn('status', ['done', 'canceled'])
            ->whereDoesntHave('taskAssignments', function($query) {
                $query->whereIn('status', ['pending', 'in_progress']);
            })
            ->orderBy('urgency_level_id', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
        $shiftTypes = ScheduleShift::select('shift_type')
            ->whereNotNull('shift_type')
            ->distinct()
            ->pluck('shift_type')
            ->toArray();

        $scheduleRoles = ScheduleShift::select('role')
            ->whereNotNull('role')
            ->distinct()
            ->pluck('role')
            ->toArray();
        $userRoles = User::whereNotNull('role')
            ->distinct()
            ->pluck('role')
            ->toArray();
        return view('admin.schedules.create', compact(
            'startDate',
            'endDate',
            'currentWeek',
            'employees',
            'shiftTypes',
            'scheduleRoles',
            'availableTasks',
            'userRoles'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'schedule_data' => 'required|json',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'name' => 'required|string',
        ]);

        $scheduleData = json_decode($request->schedule_data, true);

        if (empty($scheduleData)) {
            return back()->withErrors(['schedule_data' => 'Please add at least one shift to the schedule.']);
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

                // Create task assignment (now required)
                if (!empty($shift['task_id'])) {
                    TaskAssignment::create([
                        'maintenance_request_id' => $shift['task_id'],
                        'assigned_user_id' => $item['employee_id'],
                        'schedule_shift_id' => $scheduleShift->id,
                        'assigned_at' => now(),
                        'status' => 'pending',
                        'priority' => 'normal',
                        'assignment_notes' => $shift['assignment_notes'] ?? null,
                        'assigned_by' => Auth::id(),
                    ]);
                }
            }
        }

        $message = $request->has('publish')
            ? 'Schedule published successfully! Employees have been notified.'
            : 'Schedule saved as draft successfully.';

        return redirect()->route('admin.schedules.index')->with('success', $message);
    }


    public function show(Schedule $schedule)
    {
        $schedule->load([
            'shifts.user',
            'shifts.taskAssignments.maintenanceRequest.store',
            'creator'
        ]);

        $availableTasks = MaintenanceRequest::where('status', 'open')
            ->with(['store', 'urgencyLevel'])->get();

        // ✅ Get distinct values from database
        $shiftTypes = ScheduleShift::select('shift_type')
            ->whereNotNull('shift_type')
            ->distinct()
            ->pluck('shift_type')
            ->toArray();

        $scheduleRoles = ScheduleShift::select('role')
            ->whereNotNull('role')
            ->distinct()
            ->pluck('role')
            ->toArray();
        return view('admin.schedules.show', compact(
            'schedule',
            'availableTasks',
            'shiftTypes',
            'scheduleRoles'
        ));
    }


    public function edit(Schedule $schedule, Request $request)
    {
        // Optional: Uncomment if you want to restrict editing
        // if ($schedule->status !== 'draft') {
        //     return redirect()->route('admin.schedules.index')
        //         ->withErrors(['error' => 'Only draft schedules can be edited.']);
        // }

        // Get the week dates from the schedule
        $startDate = Carbon::parse($schedule->start_date);
        $endDate = Carbon::parse($schedule->end_date);

        // Generate current week days (same as create)
        $currentWeek = collect();
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $currentWeek->push($date->copy());
        }

        // Get active employees (same as create)
        $employees = User::where('is_active', true)
            ->whereIn('role', ['user', 'admin'])
            ->orderBy('name')
            ->get();

        // Get available tasks for assignment (same as create)
        $availableTasks = MaintenanceRequest::with(['store', 'urgencyLevel'])
            ->whereNotIn('status', ['done', 'canceled'])
            ->orderBy('urgency_level_id', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Get shift types and roles (same as create)
        $shiftTypes = ScheduleShift::select('shift_type')
            ->whereNotNull('shift_type')
            ->distinct()
            ->pluck('shift_type')
            ->toArray();

        $scheduleRoles = ScheduleShift::select('role')
            ->whereNotNull('role')
            ->distinct()
            ->pluck('role')
            ->toArray();

        $userRoles = User::whereNotNull('role')
            ->distinct()
            ->pluck('role')
            ->toArray();

        // ✅ Load schedule with existing shifts for pre-population
        $schedule->load([
            'shifts.user',
            'shifts.taskAssignments.maintenanceRequest.store',
            'creator'
        ]);

        // ✅ Convert existing shifts to the same format as create form expects
        $existingScheduleData = [];

        foreach ($schedule->shifts as $shift) {
            $key = $shift->user_id . '_' . $shift->date;

            if (!isset($existingScheduleData[$key])) {
                $existingScheduleData[$key] = [];
            }

            // ✅ Handle case where taskAssignments might be empty
            $taskAssignment = $shift->taskAssignments->first();

            $existingScheduleData[$key][] = [
                'id' => $shift->id, // ✅ Add this line - actual shift ID
                'start' => $shift->start_time,
                'end' => $shift->end_time,
                'type' => $shift->shift_type,
                'role' => $shift->role,
                'color' => $shift->color,
                'task_id' => $taskAssignment ? $taskAssignment->maintenance_request_id : null,
                'assignment_notes' => $shift->assignment_notes
            ];
        }

        // ✅ Debug: Check if data is being loaded
        Log::info('Existing Schedule Data:', $existingScheduleData);

        return view('admin.schedules.edit', compact(
            'schedule',
            'startDate',
            'endDate',
            'currentWeek',
            'employees',
            'availableTasks',
            'shiftTypes',
            'scheduleRoles',
            'userRoles',
            'existingScheduleData'
        ));
    }


    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'schedule_data' => 'required|json',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'name' => 'required|string',
        ]);

        $scheduleData = json_decode($request->schedule_data, true);
        $deletedShiftIds = $request->input('deleted_shift_ids') ? explode(',', $request->input('deleted_shift_ids')) : [];

        if (empty($scheduleData)) {
            return back()->withErrors(['schedule_data' => 'Please add at least one shift to the schedule.']);
        }

        $cleanName = trim(preg_replace('/\s+/', ' ', $request->name));

        DB::transaction(function() use ($request, $schedule, $scheduleData, $cleanName, $deletedShiftIds) {
            // Update schedule basic info
            $schedule->update([
                'name' => $cleanName,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'week_start_date' => Carbon::parse($request->start_date)->startOfWeek(),
                'status' => $request->has('publish') ? 'published' : $schedule->status,
            ]);

            // Get all existing shifts with their IDs for updating
            $existingShifts = $schedule->shifts()->with('taskAssignments')->get();
            $updatedShiftIds = [];

            // Process incoming shifts - UPDATE or CREATE
            foreach ($scheduleData as $item) {
                foreach ($item['shifts'] as $shiftData) {
                    $existingShift = $existingShifts->where('user_id', $item['employee_id'])
                        ->where('date', $item['start_date'])
                        ->where('start_time', $shiftData['start'])
                        ->where('end_time', $shiftData['end'])
                        ->first();

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
                        $updatedShiftIds[] = $existingShift->id;
                        Log::info("Updated existing shift #{$existingShift->id}");
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
                        Log::info("Created new shift #{$scheduleShift->id}");

                        // Create task assignment
                        if (!empty($shiftData['task_id'])) {
                            TaskAssignment::create([
                                'maintenance_request_id' => $shiftData['task_id'],
                                'assigned_user_id' => $item['employee_id'],
                                'schedule_shift_id' => $scheduleShift->id,
                                'assigned_at' => now(),
                                'status' => 'pending',
                                'priority' => 'normal',
                                'assignment_notes' => $shiftData['assignment_notes'] ?? null,
                                'assigned_by' => Auth::id(),
                            ]);
                        }
                    }
                }
            }

            // Delete shifts marked for deletion by the user
            if (!empty($deletedShiftIds)) {
                $shiftsToDelete = $existingShifts->whereIn('id', $deletedShiftIds);
                foreach ($shiftsToDelete as $shift) {
                    $shift->taskAssignments()->delete();
                    $shift->delete();
                    Log::info("Deleted shift #{$shift->id} and its assignments (user-initiated)");
                }
            }

            // Delete remaining unused shifts and their assignments
            $shiftsToDelete = $existingShifts->whereNotIn('id', $updatedShiftIds)->whereNotIn('id', $deletedShiftIds);
            foreach ($shiftsToDelete as $shift) {
                $shift->taskAssignments()->delete();
                $shift->delete();
                Log::info("Deleted unused shift #{$shift->id} and its assignments");
            }
        });

        $message = $request->has('publish')
            ? 'Schedule updated and published successfully!'
            : 'Schedule updated successfully.';

        return redirect()->route('admin.schedules.index')->with('success', $message);
    }


    public function destroy(Schedule $schedule)
    {
        // Only allow deletion of draft schedules
       

        $schedule->shifts()->delete();
        $schedule->delete();

        return redirect()->route('admin.schedules.index')->with('success', 'Schedule deleted successfully.');
    }

    public function publish(Schedule $schedule)
    {
        if ($schedule->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft schedules can be published.']);
        }

        $schedule->update(['status' => 'published']);

        // Here you would send notifications to employees
        // NotificationService::sendScheduleNotification($schedule);

        return back()->with('success', 'Schedule published successfully!');
    }

    public function activate(Schedule $schedule)
    {
        if ($schedule->status !== 'published') {
            return back()->withErrors(['error' => 'Only published schedules can be activated.']);
        }

        // Use start_date instead of week_start_date for comparison
        $weekStart = Carbon::parse($schedule->start_date)->startOfWeek();

        // Deactivate other active schedules for the same week
        Schedule::where('status', 'active')
            ->whereBetween('start_date', [
                $weekStart->format('Y-m-d'),
                $weekStart->copy()->endOfWeek()->format('Y-m-d')
            ])
            ->update(['status' => 'archived']);

        $schedule->update(['status' => 'active']);

        return back()->with('success', 'Schedule activated successfully!');
    }
    public function deleteShiftType(Request $request)
    {
        // Add debugging
        Log::info('Delete Shift Type Request:', $request->all());

        try {
            $request->validate([
                'shift_type' => 'required|string|min:1'
            ]);

            $shiftType = $request->input('shift_type');

            // Check if any schedules are using this shift type
            $shiftsCount = \App\Models\ScheduleShift::where('shift_type', $shiftType)->count();

            if ($shiftsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete shift type '{$shiftType}' because it's being used in {$shiftsCount} shift(s)."
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Shift type removed successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            Log::error('Delete Shift Type Error:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error removing shift type: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteRole(Request $request)
    {
        // Add debugging
        Log::info('Delete Role Request:', $request->all());

        try {
            $request->validate([
                'role' => 'required|string|min:1'
            ]);

            $role = $request->input('role');

            // Check if any schedules are using this role
            $shiftsCount = \App\Models\ScheduleShift::where('role', $role)->count();

            if ($shiftsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete role '{$role}' because it's being used in {$shiftsCount} shift(s)."
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role removed successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            Log::error('Delete Role Error:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error removing role: ' . $e->getMessage()
            ], 500);
        }
    }
}
