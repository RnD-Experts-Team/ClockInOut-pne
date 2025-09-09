<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Clocking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ScorecardController extends Controller
{
    public function index(Request $request)
    {
        // Start with users query (excluding admins)
        $query = User::where('role', '!=', 'admin');

        // Filter by specific user if selected
        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('id', $request->user_id);
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Get users first
        $users = $query->get();

        // Calculate scorecard data for each user with date filtering
        $scorecards = $users->map(function($user) use ($request) {
            return $this->calculateUserScorecard($user, $request);
        })->filter(function($scorecard) {
            // Only include users who have worked hours in the selected period
            return $scorecard->total_hours > 0 || $scorecard->payments_to_company > 0 || $scorecard->fuel_cost > 0;
        });

        // Get all users for filter dropdown
        $allUsers = User::where('role', '!=', 'admin')->orderBy('name')->get();

        return view('admin.scorecards.index', compact('scorecards', 'allUsers'));
    }

    private function calculateUserScorecard($user, $request)
    {
        // Build date range constraints
        $clockingQuery = Clocking::where('user_id', $user->id);

        // Apply date filtering to clockings
        if ($request->filled('date_range') && $request->date_range !== 'all') {
            switch ($request->date_range) {
                case 'this_week':
                    $clockingQuery->whereBetween('clock_in', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'this_month':
                    $clockingQuery->whereBetween('clock_in', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth()
                    ]);
                    break;
                case 'last_week':
                    $clockingQuery->whereBetween('clock_in', [
                        Carbon::now()->subWeek()->startOfWeek(),
                        Carbon::now()->subWeek()->endOfWeek()
                    ]);
                    break;
                case 'last_month':
                    $clockingQuery->whereBetween('clock_in', [
                        Carbon::now()->subMonth()->startOfMonth(),
                        Carbon::now()->subMonth()->endOfMonth()
                    ]);
                    break;
                case 'custom':
                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $clockingQuery->whereBetween('clock_in', [
                            Carbon::parse($request->start_date)->startOfDay(),
                            Carbon::parse($request->end_date)->endOfDay()
                        ]);
                    }
                    break;
            }
        }

        // Get the filtered clockings
        $clockings = $clockingQuery->get();

        // Calculate totals
        $totalHours = 0;
        $totalPaymentsToCompany = 0;
        $totalFuelCost = 0;

        foreach ($clockings as $clocking) {
            // Calculate hours worked
            if ($clocking->clock_in && $clocking->clock_out) {
                $clockIn = Carbon::parse($clocking->clock_in);
                $clockOut = Carbon::parse($clocking->clock_out);
                $hoursWorked = $clockIn->diffInHours($clockOut, false);
                $totalHours += $hoursWorked;
            }

            // Add payments made to company (if user bought something)
            if ($clocking->bought_something && $clocking->purchase_cost) {
                $totalPaymentsToCompany += $clocking->purchase_cost;
            }

            // Add gas payments (fuel cost)
            if ($clocking->gas_payment) {
                $totalFuelCost += $clocking->gas_payment;
            }
        }

        $hourlyRate = $user->hourly_pay ?? 0;
        $totalHourlyPay = $totalHours * $hourlyRate;
        $total = $totalHourlyPay + $totalPaymentsToCompany + $totalFuelCost;

        return (object) [
            'user' => $user,
            'hourly_rate' => $hourlyRate,
            'total_hours' => round($totalHours, 2),
            'total_hourly_pay' => $totalHourlyPay,
            'payments_to_company' => $totalPaymentsToCompany,
            'fuel_cost' => $totalFuelCost,
            'total' => $total,
            'period' => $this->getDateRangeText($request),
            'clockings_count' => $clockings->count() // For debugging
        ];
    }

    private function getDateRangeText($request)
    {
        if (!$request->filled('date_range') || $request->date_range === 'all') {
            return 'All Time';
        }

        switch ($request->date_range) {
            case 'this_week':
                return 'This Week (' . Carbon::now()->startOfWeek()->format('M j') . ' - ' . Carbon::now()->endOfWeek()->format('M j, Y') . ')';
            case 'this_month':
                return 'This Month (' . Carbon::now()->format('F Y') . ')';
            case 'last_week':
                return 'Last Week (' . Carbon::now()->subWeek()->startOfWeek()->format('M j') . ' - ' . Carbon::now()->subWeek()->endOfWeek()->format('M j, Y') . ')';
            case 'last_month':
                return 'Last Month (' . Carbon::now()->subMonth()->format('F Y') . ')';
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    return 'Custom (' . Carbon::parse($request->start_date)->format('M j, Y') . ' - ' . Carbon::parse($request->end_date)->format('M j, Y') . ')';
                }
                return 'Custom Range';
            default:
                return 'All Time';
        }
    }

    public function export(Request $request)
    {
        // Same logic as index
        $query = User::where('role', '!=', 'admin');

        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->get();
        $scorecards = $users->map(function($user) use ($request) {
            return $this->calculateUserScorecard($user, $request);
        })->filter(function($scorecard) {
            // Only include users who have activity in the selected period
            return $scorecard->total_hours > 0 || $scorecard->payments_to_company > 0 || $scorecard->fuel_cost > 0;
        });

        return view('admin.scorecards.export', compact('scorecards'));
    }
}
