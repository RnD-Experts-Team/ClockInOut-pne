<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduleShift;
use App\Models\TaskAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get current week or requested week
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfWeek()
            : Carbon::now()->startOfWeek();

        $endDate = $startDate->copy()->endOfWeek();
        $currentWeekStart = $startDate->copy();
        $currentWeekEnd = $endDate->copy();
        $previousWeek = $startDate->copy()->subWeek();
        $nextWeek = $startDate->copy()->addWeek();


        // Get user's shifts for the week with relationships (EXISTING)
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

        // Get user's active task assignments (EXISTING)
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

        // NEW: Get user's actual clocking records for this week
        $clockingRecords = \App\Models\Clocking::where('user_id', $user->id)
            ->whereBetween('clock_in', [
                $startDate->format('Y-m-d H:i:s'),
                $endDate->endOfDay()->format('Y-m-d H:i:s')
            ])
            ->whereNotNull('clock_out') // Only completed shifts
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

                // Skip invalid records (clock_out before clock_in)
                if ($diffInSeconds > 0) {
                    $actualWorkedSeconds += $diffInSeconds;
                    $daysWorked++;

                    // Calculate earnings for this shift
                    if ($user->hourly_pay) {
                        $hoursDecimal = $diffInSeconds / 3600;
                        $weeklyEarnings += ($hoursDecimal * $user->hourly_pay);
                    }
                }
            }
        }

        // NEW: Format actual worked hours
        $actualHoursWorked = sprintf('%02d:%02d',
            floor($actualWorkedSeconds / 3600),
            floor(($actualWorkedSeconds % 3600) / 60)
        );

        // NEW: Calculate average daily hours
        $averageDailyHours = $daysWorked > 0 ?
            sprintf('%02d:%02d',
                floor(($actualWorkedSeconds / $daysWorked) / 3600),
                floor((($actualWorkedSeconds / $daysWorked) % 3600) / 60)
            ) : '00:00';

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

        // Calculate weekly stats (EXISTING + NEW)
        $weeklyHours = $scheduleShifts->sum('duration_hours'); // Scheduled hours
        $tasksThisWeek = $taskAssignments->count();

        return view('user.schedule.index', compact(
            'scheduleShifts',
            'taskAssignments',
            'weekDays',
            'weeklyHours',        // Scheduled hours (existing)
            'tasksThisWeek',
            'startDate',
            'endDate',
            // NEW: Add actual clocking data
            'actualHoursWorked',  // Real worked hours from clocking
            'daysWorked',         // Number of days actually worked
            'weeklyEarnings',     // Earnings based on actual hours
            'averageDailyHours',   // Average hours per working day
         'currentWeekStart',
        'currentWeekEnd',
        'previousWeek',
        'nextWeek'
        ));
    }

}
