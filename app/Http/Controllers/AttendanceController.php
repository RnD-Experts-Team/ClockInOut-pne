<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clocking;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display the attendance records for the authenticated user.
     */
  /**
     * Display the clocking records for admin with optional filters.
     */
    /**
     * Display the clocking records for admin with optional filters.
     */
    public function index(Request $request)
    {
        // Get filter dates from the request or set default to current week
        $startDate = $request->input('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfWeek()->format('Y-m-d'));

        // Get gas payments rate from configuration
        $gasPaymentRate = \App\Models\Configuration::getGasPaymentRate();

        // Build the query with date filters
        $query = Clocking::with('user')
            ->where('user_id', Auth::id())
            ->whereDate('clock_in', '>=', $startDate)
            ->whereDate('clock_in', '<=', $endDate)
            ->latest();

        $clockings = $query->paginate(7); // Show 7 records per page (one week)

        // Calculate values for each clocking record
        foreach ($clockings as $clocking) {
            // Calculate total hours
            if ($clocking->clock_in && $clocking->clock_out) {
                $start = Carbon::parse($clocking->clock_in);
                $end = Carbon::parse($clocking->clock_out);
                $diffInSeconds = $end->timestamp - $start->timestamp;
                $clocking->total_hours = gmdate('H:i:s', $diffInSeconds);

                // Calculate earnings based on hourly rate
                if (isset($clocking->user->hourly_pay)) {
                    $hoursDecimal = $diffInSeconds / 3600;
                    $clocking->earnings = $hoursDecimal * $clocking->user->hourly_pay;
                }
            } else {
                $clocking->total_hours = '-';
                $clocking->earnings = 0;
            }

            // Calculate miles and gas payments
            if (!is_null($clocking->miles_in) && !is_null($clocking->miles_out)) {
                $clocking->total_miles = $clocking->miles_out - $clocking->miles_in;
                $clocking->gas_payment = $clocking->total_miles * $gasPaymentRate;
            } else {
                $clocking->total_miles = '-';
                $clocking->gas_payment = 0;
            }

            // Calculate total salary (earnings + gas payments + purchase cost)
            $clocking->total_salary = ($clocking->earnings ?? 0) +
                                     ($clocking->gas_payment ?? 0) +
                                     ($clocking->purchase_cost ?? 0);

            // Format dates for display
            $clocking->formatted_date = $clocking->clock_in ? Carbon::parse($clocking->clock_in)->format('M d, Y') : '';
            $clocking->formatted_clock_in = $clocking->clock_in ? Carbon::parse($clocking->clock_in)->format('g:i A') : '';
            $clocking->formatted_clock_out = $clocking->clock_out ? Carbon::parse($clocking->clock_out)->format('g:i A') : '';
        }

        return view('attendance', compact('clockings', 'startDate', 'endDate'));
    }
}
