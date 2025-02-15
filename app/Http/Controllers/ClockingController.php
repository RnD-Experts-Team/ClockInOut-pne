<?php

namespace App\Http\Controllers;

use App\Models\Clocking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClockingController extends Controller
{
    public function index()
    {
        // Retrieve the latest clocking record for the authenticated user
        $clocking = Clocking::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->where('is_clocked_in', true)
            ->first();

        // Pass the clocking status to the view
        return view('clocking', compact('clocking'));
    }

    public function ClockingTable(Request $request)
{
    // Check if the user is an admin
    if (Auth::user()->role !== 'admin') {
        return redirect()->route('dashboard'); // Redirect non-admins
    }

    // Get filter dates from request
    $startDate = $request->input('start_date');
    $endDate   = $request->input('end_date');

    // Query with optional date filters
    $query = Clocking::with('user')->latest();

    if ($startDate) {
        $query->whereDate('clock_in', '>=', $startDate);
    }

    if ($endDate) {
        $query->whereDate('clock_in', '<=', $endDate);
    }

    // Paginate results
    $clockings = $query->paginate(10);

    // Variables to accumulate totals
    $totalMilesIn = 0;
    $totalMilesOut = 0;
    $totalMiles = 0;
    $totalSeconds = 0;
    $totalEarnings = 0;

    // Calculate per-record values + accumulate totals
    foreach ($clockings as $clocking) {
        // Miles In / Miles Out
        if (!is_null($clocking->miles_in)) {
            $totalMilesIn += $clocking->miles_in;
        }
        if (!is_null($clocking->miles_out)) {
            $totalMilesOut += $clocking->miles_out;
        }

        // Total Miles
        if (!is_null($clocking->miles_in) && !is_null($clocking->miles_out)) {
            $clocking->total_miles = $clocking->miles_out - $clocking->miles_in;
            $totalMiles += $clocking->total_miles;
        } else {
            $clocking->total_miles = '-';
        }

        // Total Hours & Earnings
        if ($clocking->clock_in && $clocking->clock_out) {
            $start = Carbon::parse($clocking->clock_in);
            $end   = Carbon::parse($clocking->clock_out);
            $diffInSeconds = $end->diffInSeconds($start);

            // Format total hours as HH:MM:SS
            $clocking->total_hours = gmdate('H:i:s', $diffInSeconds);

            // Calculate hours in decimal
            $hoursDecimal = $diffInSeconds / 3600;

            // Multiply by user's hourly pay if available
            if (isset($clocking->user->hourly_pay)) {
                $clocking->earnings = $hoursDecimal * $clocking->user->hourly_pay;
                $totalEarnings += $clocking->earnings; // accumulate
            } else {
                $clocking->earnings = '-';
            }

            // Accumulate total seconds for all records
            $totalSeconds += $diffInSeconds;
        } else {
            $clocking->total_hours = '-';
            $clocking->earnings = '-';
        }
    }

    // Format the accumulated total hours as HH:MM:SS
    $hours   = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    $seconds = $totalSeconds % 60;
    $totalHoursFormatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

    // Retrieve all employees (users)
    $users = User::all();

    // Return the view with data + totals
    return view('clockingTable', compact(
        'clockings',
        'startDate',
        'endDate',
        'users',
        // Totals
        'totalMilesIn',
        'totalMilesOut',
        'totalMiles',
        'totalHoursFormatted',
        'totalEarnings'
    ));
}




    public function clockIn(Request $request)
    {
        $request->validate([
            'miles_in' => 'nullable|integer',
            'image_in' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        // Check if the clock-in image exists and store it
        $imagePath = null;
        if ($request->hasFile('image_in')) {
            $imagePath = $request->file('image_in')->store('clocking_images', 'public');
        }

        // Create a new clocking record with or without an image
        Clocking::create([
            'user_id'       => Auth::id(),
            'clock_in'      => now(),
            'miles_in'      => $request->miles_in,
            'image_in'      => $imagePath,
            'is_clocked_in' => true,
        ]);

        return back()->with('success', 'تم تسجيل الحضور بنجاح');
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'miles_out' => 'nullable|integer',
            'image_out' => 'nullable|image|mimes:jpg,png,jpeg',
        ]);

        // Check if the clock-out image exists and store it
        $imagePath = null;
        if ($request->hasFile('image_out')) {
            $imagePath = $request->file('image_out')->store('clocking_images', 'public');
        }

        // Retrieve the existing clocking record for clock out
        $clocking = Clocking::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->where('is_clocked_in', true)
            ->firstOrFail();

        // Update the record with clock-out details
        $clocking->update([
            'clock_out'     => now(),
            'miles_out'     => $request->miles_out,
            'image_out'     => $imagePath,
            'is_clocked_in' => false,
        ]);

        return back()->with('success', 'تم تسجيل الإنصراف بنجاح');
    }

    public function updateClocking(Request $request)
    {
        $request->validate([
            'clocking_id' => 'required|exists:clockings,id',
            'clock_in'    => 'nullable|date',
            'clock_out'   => 'nullable|date',
            'miles_in'    => 'nullable|integer',
            'miles_out'   => 'nullable|integer',
        ]);

        $clocking = Clocking::findOrFail($request->clocking_id);

      

        $clocking->update([
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'miles_in'  => $request->miles_in,
            'miles_out' => $request->miles_out,
        ]);

          // Optional: Add a check to ensure clock_out is after clock_in
          if (
            $request->clock_out && $request->clock_in &&
            Carbon::parse($request->clock_out)->lessThanOrEqualTo(Carbon::parse($request->clock_in))
        ) {
            return back()->withErrors([
                'clock_out' => 'Clock-out time cannot be before or equal to clock-in time.'
            ]);
        }

        return redirect()->back()->with('success', 'Clocking record updated successfully.');
    }
}
