<?php

namespace App\Services\Api\Admin;

use App\Models\Clocking;
use App\Models\Configuration;
use Carbon\Carbon;

class ExportService
{
    public function exportData(?string $startDate, ?string $endDate): array
    {
        $gasPaymentRate = Configuration::getGasPaymentRate();

        $query = Clocking::with('user')->latest('clock_in');

        if ($startDate) {
            $query->whereDate('clock_in', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('clock_in', '<=', $endDate);
        }

        $clockings = $query->get();

        $data = $clockings->map(function ($clocking) use ($gasPaymentRate) {

            $totalMiles =
                (!is_null($clocking->miles_in) && !is_null($clocking->miles_out))
                    ? ($clocking->miles_out - $clocking->miles_in)
                    : 0;

            $gasPayment = $totalMiles * $gasPaymentRate;

            $totalHours = null;
            $earnings = 0;

            if ($clocking->clock_in && $clocking->clock_out) {
                $start = Carbon::parse($clocking->clock_in);
                $end = Carbon::parse($clocking->clock_out);

                $totalHours = $end->diff($start)->format('%H:%I:%S');

                $hoursDecimal = $end->diffInSeconds($start) / 3600;
                $earnings = $hoursDecimal * ($clocking->user->hourly_pay ?? 0);
            }

            $totalSalary = $earnings + $gasPayment + ($clocking->purchase_cost ?? 0);

            return [
                'name' => $clocking->user->name ?? 'N/A',
                'date' => $clocking->clock_in
                    ? Carbon::parse($clocking->clock_in)->format('M d, Y')
                    : null,
                'clock_in' => $clocking->clock_in
                    ? Carbon::parse($clocking->clock_in)->format('g:i A')
                    : null,
                'clock_out' => $clocking->clock_out
                    ? Carbon::parse($clocking->clock_out)->format('g:i A')
                    : null,
                'miles_in' => $clocking->miles_in,
                'miles_out' => $clocking->miles_out,
                'total_miles' => $totalMiles,
                'gas_payment' => round($gasPayment, 2),
                'purchase_cost' => round($clocking->purchase_cost ?? 0, 2),
                'total_hours' => $totalHours,
                'total_salary' => round($totalSalary, 2),
                'hourly_rate' => round($clocking->user->hourly_pay ?? 0, 2),
            ];
        });

        return [
            'success' => true,
            'data' => $data,
            'meta' => [
                'record_count' => $clockings->count(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'gas_rate' => $gasPaymentRate,
            ]
        ];
    }
}