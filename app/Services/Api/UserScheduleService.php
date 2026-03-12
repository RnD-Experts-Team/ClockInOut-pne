<?php

namespace App\Services\Api;

use Carbon\Carbon;
use App\Models\Clocking;
use App\Models\TaskAssignment;
use App\Models\ScheduleShift;
use Illuminate\Support\Facades\Auth;

class UserScheduleService
{
    public function index($request)
    {
        $user = Auth::user();

        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);
        $currentWeekStart = $startDate->copy();
        $currentWeekEnd = $endDate->copy();
        $previousWeek = $startDate->copy()->subWeek();
        $nextWeek = $startDate->copy()->addWeek();


        $scheduleShifts = ScheduleShift::with([
            'schedule',
            'user',
            'taskAssignments.maintenanceRequest.store',
            'taskAssignments.assignedUser'
        ])
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();


        $taskAssignments = TaskAssignment::with([
            'maintenanceRequest.store',
            'maintenanceRequest.urgencyLevel',
            'assignedUser'
        ])
            ->where('assigned_user_id', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereBetween('due_date', [
                $startDate->format('Y-m-d H:i:s'),
                $endDate->format('Y-m-d 23:59:59')
            ])
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->get();


        $clockingRecords = Clocking::where('user_id', $user->id)
            ->whereBetween('clock_in', [
                $startDate->format('Y-m-d H:i:s'),
                $endDate->endOfDay()->format('Y-m-d H:i:s')
            ])
            ->whereNotNull('clock_out')
            ->get();

        // NEW: Calculate actual worked hours from clocking data

        $actualWorkedSeconds = 0;
        $daysWorked = 0;
        $weeklyEarnings = 0;

        foreach ($clockingRecords as $clocking) {
            if ($clocking->clock_in && $clocking->clock_out) {
                $clockIn = Carbon::parse($clocking->clock_in);
                $clockOut = Carbon::parse($clocking->clock_out);
                $diffInSeconds = $clockOut->timestamp - $clockIn->timestamp;

                if ($diffInSeconds > 0) {
                    $actualWorkedSeconds += $diffInSeconds;
                    $daysWorked++;

                    if ($user->hourly_pay) {
                        $hoursDecimal = $diffInSeconds / 3600;
                        $weeklyEarnings += ($hoursDecimal * $user->hourly_pay);
                    }
                }
            }
        }

        // NEW: Format actual worked hours

        $actualHoursWorked = sprintf(
            '%02d:%02d',
            floor($actualWorkedSeconds / 3600),
            floor(($actualWorkedSeconds % 3600) / 60)
        );

        // NEW: Calculate average daily hours

        $averageDailyHours = $daysWorked > 0
            ? sprintf(
                '%02d:%02d',
                floor(($actualWorkedSeconds / $daysWorked) / 3600),
                floor((($actualWorkedSeconds / $daysWorked) % 3600) / 60)
            )
            : '00:00';

        // Create week calendar with proper date filtering (EXISTING)

        $weekDays = collect();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
               // Filter shifts for this specific day

                 $dayShifts = $scheduleShifts->filter(function ($shift) use ($date) {
                return $shift->date && $shift->date->isSameDay($date);
            });

            $weekDays->push([
                'date' => $date->copy(),
                'shifts' => $dayShifts
            ]);
        }


        $weeklyHours = $scheduleShifts->sum('duration_hours');
        $tasksThisWeek = $taskAssignments->count();


        return [
            'scheduleShifts' => $scheduleShifts,
            'taskAssignments' => $taskAssignments,
            'weekDays' => $weekDays,
            'weeklyHours' => $weeklyHours,
            'tasksThisWeek' => $tasksThisWeek,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'actualHoursWorked' => $actualHoursWorked,
            'daysWorked' => $daysWorked,
            'weeklyEarnings' => $weeklyEarnings,
            'averageDailyHours' => $averageDailyHours,
            'currentWeekStart' => $currentWeekStart,
            'currentWeekEnd' => $currentWeekEnd,
            'previousWeek' => $previousWeek,
            'nextWeek' => $nextWeek
        ];
    }
}