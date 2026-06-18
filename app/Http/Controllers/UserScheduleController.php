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
            ? Carbon::parse($request->input('start_date'))->startOfWeek(Carbon::TUESDAY)
            : Carbon::now()->startOfWeek(Carbon::TUESDAY);

        $endDate = $startDate->copy()->endOfWeek(Carbon::MONDAY);
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
        // NOTE: do NOT filter out open shifts here — payments-to-company are recorded
        // per clocking even when clock_out is still null (matches the Scorecard logic).
        $clockingRecords = \App\Models\Clocking::where('user_id', $user->id)
            ->whereBetween('clock_in', [
                $startDate->format('Y-m-d H:i:s'),
                $endDate->endOfDay()->format('Y-m-d H:i:s')
            ])
            ->get();

        // NEW: Calculate actual worked hours + full settlement from clocking data
        // Mirrors App\Http\Controllers\Admin\ScorecardController::calculateUserScorecard()
        $gasPaymentRate = \App\Models\Configuration::getGasPaymentRate();
        $actualWorkedSeconds = 0;
        $daysWorked = 0;
        $weeklyEarnings = 0;  // Hourly pay only
        $weeklyFuelCost = 0;  // Mileage reimbursement
        $weeklyPayments = 0;  // Payments made to company (purchase_cost)

        foreach ($clockingRecords as $clocking) {
            // Payments to company are counted regardless of clock_out (matches Scorecard)
            $weeklyPayments += $clocking->purchase_cost;

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

                    // Fuel reimbursement based on miles driven this shift
                    $miles = $clocking->miles_out - $clocking->miles_in;
                    $weeklyFuelCost += ($miles * $gasPaymentRate);
                }
            }
        }

        // NEW: Full weekly total — matches the Scorecard TOTAL column
        $weeklyTotal = $weeklyEarnings + $weeklyFuelCost + $weeklyPayments;

        // NEW: Format actual worked hours
        $actualHoursWorked = sprintf('%02d:%02d',
            floor($actualWorkedSeconds / 3600),
            floor(($actualWorkedSeconds % 3600) / 60)
        );

        // NEW: Calculate average daily hours
        $averageDailySeconds = $daysWorked > 0 ? (int) ($actualWorkedSeconds / $daysWorked) : 0;
        $averageDailyHours = $daysWorked > 0 ?
            sprintf('%02d:%02d',
                floor($averageDailySeconds / 3600),
                floor(($averageDailySeconds % 3600) / 60)
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
            'weeklyEarnings',     // Hourly pay based on actual hours
            'weeklyFuelCost',     // Mileage reimbursement (matches Scorecard)
            'weeklyPayments',     // Payments made to company (matches Scorecard)
            'weeklyTotal',        // Full settlement total (matches Scorecard TOTAL)
            'averageDailyHours',   // Average hours per working day
         'currentWeekStart',
        'currentWeekEnd',
        'previousWeek',
        'nextWeek'
        ));
    }

}
