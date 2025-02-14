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
        $endDate   = $request->input('end_date');

        // Query clocking records with filters
        $query = Clocking::with('user')->latest();

        if ($startDate) {
            $query->whereDate('clock_in', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('clock_in', '<=', $endDate);
        }

        $clockings = $query->get();

        // Stream the CSV response
        $response = new StreamedResponse(function () use ($clockings) {
            $handle = fopen('php://output', 'w');

            // CSV Header includes Date, Total Miles and Total Hours
            fputcsv($handle, [
                'Name', 
                'Date', 
                'Clock In', 
                'Clock Out', 
                'Miles In', 
                'Miles Out', 
                'Total Miles', 
                'Total Hours'
            ]);

            // CSV Data
            foreach ($clockings as $clocking) {
                // Calculate total miles if both values are available
                $totalMiles = (!is_null($clocking->miles_in) && !is_null($clocking->miles_out))
                    ? $clocking->miles_out - $clocking->miles_in
                    : '-';

                // Calculate total hours if both clock_in and clock_out exist
                if ($clocking->clock_in && $clocking->clock_out) {
                    $totalHours = Carbon::parse($clocking->clock_out)
                        ->diff(Carbon::parse($clocking->clock_in))
                        ->format('%H:%I:%S');
                } else {
                    $totalHours = '-';
                }

                // Derive date from clock_in (if available)
                $date = $clocking->clock_in 
                    ? Carbon::parse($clocking->clock_in)->format('Y-m-d') 
                    : '-';

                fputcsv($handle, [
                    $clocking->user->name ?? 'N/A',
                    $date,
                    $clocking->clock_in,
                    $clocking->clock_out,
                    $clocking->miles_in ?? '-',
                    $clocking->miles_out ?? '-',
                    $totalMiles,
                    $totalHours,
                ]);
            }

            fclose($handle);
        });

        // Set CSV headers
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="clocking_records.csv"');

        return $response;
    }
}
