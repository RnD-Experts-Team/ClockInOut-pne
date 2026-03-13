<?php

namespace App\Services\Api\Admin;

use App\Models\User;
use App\Models\Clocking;
use App\Models\Configuration;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScorecardService
{
    public function getScorecards(Request $request)
    {
        // Same logic as index

        $query = User::where('role', '!=', 'admin');

        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search; 
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->get();

        $scorecards = $users->map(function ($user) use ($request) {
            return $this->calculateUserScorecard($user, $request);
        })->filter(function ($scorecard) {
        // Only include users who have activity in the selected period

            return $scorecard['total_hours'] > 0
                || $scorecard['payments_to_company'] > 0
                || $scorecard['fuel_cost'] > 0;
        })->values();

        return $scorecards;
    }

    public function getAllUsers()
    {
        return User::where('role', '!=', 'admin')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function calculateUserScorecard($user, Request $request): array
    {
        $gasPaymentRate = Configuration::getGasPaymentRate();

        $clockingQuery = Clocking::where('user_id', $user->id);

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
                $miles = ($clocking->miles_out ?? 0) - ($clocking->miles_in ?? 0);
                $gasPayment = $miles * $gasPaymentRate;
                $totalFuelCost += $gasPayment;
            }

            $totalPaymentsToCompany += $clocking->purchase_cost ?? 0;
        }

        $hourlyRate = $user->hourly_pay ?? 0;
        $totalHourlyPay = $totalHours * $hourlyRate;
        $total = $totalHourlyPay + $totalPaymentsToCompany + $totalFuelCost;

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'hourly_pay' => $user->hourly_pay,
            ],
            'hourly_rate' => $hourlyRate,
            'total_hours' => round($totalHours, 2),
            'total_hourly_pay' => round($totalHourlyPay, 2),
            'payments_to_company' => round($totalPaymentsToCompany, 2),
            'fuel_cost' => round($totalFuelCost, 2),
            'total' => round($total, 2),
            'period' => $this->getDateRangeText($request),
            'clockings_count' => $clockings->count(),
        ];
    }

    private function getDateRangeText(Request $request): string
    {
        if (!$request->filled('date_range') || $request->date_range === 'all') {
            return 'All Time';
        }

        switch ($request->date_range) {
            case 'this_week':
                return 'This Week (' .
                    Carbon::now()->startOfWeek()->format('M j') .
                    ' - ' .
                    Carbon::now()->endOfWeek()->format('M j, Y') .
                    ')';

            case 'this_month':
                return 'This Month (' . Carbon::now()->format('F Y') . ')';

            case 'last_week':
                return 'Last Week (' .
                    Carbon::now()->subWeek()->startOfWeek()->format('M j') .
                    ' - ' .
                    Carbon::now()->subWeek()->endOfWeek()->format('M j, Y') .
                    ')';

            case 'last_month':
                return 'Last Month (' . Carbon::now()->subMonth()->format('F Y') . ')';

            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    return 'Custom (' .
                        Carbon::parse($request->start_date)->format('M j, Y') .
                        ' - ' .
                        Carbon::parse($request->end_date)->format('M j, Y') .
                        ')';
                }

                return 'Custom Range';

            default:
                return 'All Time';
        }
    }
}