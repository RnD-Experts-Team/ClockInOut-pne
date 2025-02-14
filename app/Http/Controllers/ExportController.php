<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clocking;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    public function exportCSV(Request $request)
    {
        // Ensure only admins can access this function
        if (Auth::user()->role !== 'admin') {
            return abort(403, 'Unauthorized access.');
        }

        // Get filter parameters from the request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

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

            // CSV Header
            fputcsv($handle, ['Name', 'Clock In', 'Clock Out', 'Miles In', 'Miles Out']);

            // CSV Data
            foreach ($clockings as $clocking) {
                fputcsv($handle, [
                    $clocking->user->name ?? 'N/A',
                    $clocking->clock_in,
                    $clocking->clock_out,
                    $clocking->miles_in,
                    $clocking->miles_out,

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
