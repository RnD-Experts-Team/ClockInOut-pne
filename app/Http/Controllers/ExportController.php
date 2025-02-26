<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clocking;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function exportCSV(Request $request): StreamedResponse
    {
        // Ensure only admins can access this function
        if (Auth::user()->role !== 'admin') {
            return abort(403, 'Unauthorized access.');
        }
    
        // Get filter parameters from the request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $selectedUser = $request->input('user_id');
    
        // Get gas payment rate from configuration
        $gasPaymentRate = \App\Models\Configuration::getGasPaymentRate();
    
        $query = Clocking::with('user')->latest();
    
        if ($startDate) {
            $query->whereDate('clock_in', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('clock_in', '<=', $endDate);
        }
        if ($selectedUser) {
            $query->where('user_id', $selectedUser);
        }
    
        $clockings = $query->get();
    
        // Stream the CSV response
        $response = new StreamedResponse(function () use ($clockings, $gasPaymentRate) {
            $handle = fopen('php://output', 'w');
    
            // CSV Headers
            fputcsv($handle, [
                'Name',
                'Date',
                'Clock In',
                'Clock Out',
                'Miles In',
                'Miles Out',
                'Total Miles',
                'Gas Payment ($)',
                'Purchase Cost ($)',
                'Total Hours',
                'Total Salary ($)',
                'Hourly Pay Rate ($)'
            ]);
    
            foreach ($clockings as $clocking) {
                // Calculate total miles
                $totalMiles = (!is_null($clocking->miles_in) && !is_null($clocking->miles_out))
                    ? ($clocking->miles_out - $clocking->miles_in)
                    : 0;
    
                // Calculate gas payment
                $gasPayment = $totalMiles * $gasPaymentRate;
    
                // Calculate hours and earnings
                $totalHours = '-';
                $earnings = 0;
                if ($clocking->clock_in && $clocking->clock_out) {
                    $start = Carbon::parse($clocking->clock_in);
                    $end = Carbon::parse($clocking->clock_out);
                    $totalHours = $end->diff($start)->format('%H:%I:%S');
                    
                    // Calculate earnings based on hourly rate
                    $hoursDecimal = $end->diffInSeconds($start) / 3600;
                    $earnings = $hoursDecimal * ($clocking->user->hourly_pay ?? 0);
                }
    
                // Calculate total salary
                $totalSalary = $earnings + $gasPayment + ($clocking->purchase_cost ?? 0);
    
                fputcsv($handle, [
                    $clocking->user->name ?? 'N/A',
                    $clocking->clock_in ? Carbon::parse($clocking->clock_in)->format('M d, Y') : '-',
                    $clocking->clock_in ? Carbon::parse($clocking->clock_in)->format('g:i A') : '-',
                    $clocking->clock_out ? Carbon::parse($clocking->clock_out)->format('g:i A') : '-',
                    $clocking->miles_in ?? '-',
                    $clocking->miles_out ?? '-',
                    $totalMiles ?: '-',
                    number_format($gasPayment, 2),
                    number_format($clocking->purchase_cost ?? 0, 2),
                    $totalHours,
                    number_format($totalSalary, 2),
                    number_format($clocking->user->hourly_pay ?? 0, 2)
                ]);
            }
    
            fclose($handle);
        });
    
        // Set CSV headers with current date in filename
        $filename = 'clocking_records_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
    
        return $response;
    }
}
