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
       
        // Get filter dates from the request.
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Build the query with optional date filters.
        $query = Clocking::with('user')->latest();

        if ($startDate) {
            $query->whereDate('clock_in', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('clock_in', '<=', $endDate);
        }

        $clockings = $query->paginate(10); // Use paginate if needed

        // Calculate total hours and total miles for each clocking record.
        foreach ($clockings as $clocking) {
            if ($clocking->clock_in && $clocking->clock_out) {
                $start = Carbon::parse($clocking->clock_in);
                $end = Carbon::parse($clocking->clock_out);
                $clocking->total_hours = $end->diff($start)->format('%H:%I:%S');
            } else {
                $clocking->total_hours = '-';
            }
            
            if (!is_null($clocking->miles_in) && !is_null($clocking->miles_out)) {
                $clocking->total_miles = $clocking->miles_out - $clocking->miles_in;
            } else {
                $clocking->total_miles = '-';
            }
        }

        // Retrieve all employees.
        $users = User::all();

        // Return the view with the data.
        return view('attendance', compact('clockings'));    }
}
