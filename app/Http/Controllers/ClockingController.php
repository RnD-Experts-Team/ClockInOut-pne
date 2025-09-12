<?php

namespace App\Http\Controllers;

use App\Models\Clocking;
use App\Models\User;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


use Carbon\Carbon;

class ClockingController extends Controller
{

    public function index()
    {

        date_default_timezone_set(config('app.timezone'));
//dd(now());
        // Retrieve the latest clocking record for the authenticated user
        $clocking = Clocking::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->where('is_clocked_in', true)
            ->first();

        // Get the using_car value from the current clocking record
        $using_car = $clocking ? $clocking->using_car : false;

        return view('clocking', compact('clocking', 'using_car'));
    }

    public function ClockingTable(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard');
        }

        // Get gas payments rate from configuration
        $gasPaymentRate = Configuration::getGasPaymentRate();

        // Get all users for the dropdown
        $users = User::orderBy('name')->get();

        // Get filter parameters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $selectedUser = $request->input('user_id');

        // Build the query for totals (without pagination)
        $query = Clocking::with('user');

        // Apply filters
        if ($startDate) {
            $query->whereDate('clock_in', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('clock_in', '<=', $endDate);
        }
        if ($selectedUser) {
            $query->where('user_id', $selectedUser);
        }

        // Clone the query for totals before pagination
        $totalQuery = clone $query;

        // Get paginated results
        $clockings = $query->latest()->paginate(25);

        // Initialize totals
        $totalMilesIn = $totalMilesOut = $totalMiles = $totalSeconds = 0;
        $totalGasPayment = $totalPurchaseCost = $totalEarnings = $totalSalary = 0;
        $totalFixCount = 0; // Track total fixes

        // Calculate totals from all filtered records
        $allFilteredClockings = $totalQuery->get();

        foreach ($allFilteredClockings as $clocking) {
            // Add miles to totals
            $totalMilesIn += $clocking->miles_in ?? 0;
            $totalMilesOut += $clocking->miles_out ?? 0;

            // Calculate total miles and gas payments
            if (!is_null($clocking->miles_in) && !is_null($clocking->miles_out)) {
                $miles = $clocking->miles_out - $clocking->miles_in;
                $totalMiles += $miles;
                $gasPayment = $miles * $gasPaymentRate;
                $totalGasPayment += $gasPayment;
            }

            // Calculate hours and earnings - WITH DATA VALIDATION
            if ($clocking->clock_in && $clocking->clock_out) {
                $start = Carbon::parse($clocking->clock_in);
                $end = Carbon::parse($clocking->clock_out);
                $diffInSeconds = $end->timestamp - $start->timestamp;

                // Skip records with invalid time (clock_out before clock_in)
                if ($diffInSeconds <= 0) {
                    continue;
                }

                $totalSeconds += $diffInSeconds;

                if (isset($clocking->user->hourly_pay)) {
                    $hoursDecimal = $diffInSeconds / 3600;
                    $earnings = $hoursDecimal * $clocking->user->hourly_pay;
                    $totalEarnings += $earnings;
                }
            }

            // Add purchase cost to totals
            $totalPurchaseCost += $clocking->purchase_cost ?? 0;

            // ✅ Count fixes
            if ($clocking->fixed_something) {
                $totalFixCount++;
            }
        }

        // Calculate total salary for all filtered records
        $totalSalary = $totalEarnings + $totalGasPayment + $totalPurchaseCost;

        // Format total hours to handle hours exceeding 24
        $totalHoursFormatted = sprintf('%02d:%02d:%02d',
            floor($totalSeconds / 3600),
            floor(($totalSeconds % 3600) / 60),
            $totalSeconds % 60
        );

        // Process individual records for display
        foreach ($clockings as $clocking) {
            // Calculate individual record totals
            if (!is_null($clocking->miles_in) && !is_null($clocking->miles_out)) {
                $clocking->total_miles = $clocking->miles_out - $clocking->miles_in;
                $clocking->gas_payment = $clocking->total_miles * $gasPaymentRate;
            }

            if ($clocking->clock_in && $clocking->clock_out) {
                $start = Carbon::parse($clocking->clock_in);
                $end = Carbon::parse($clocking->clock_out);
                $diffInSeconds = $end->timestamp - $start->timestamp;

                if ($diffInSeconds <= 0) {
                    $clocking->total_hours = 'Invalid Time';
                    $clocking->earnings = 0;
                } else {
                    // Calculate actual total hours (can exceed 24 hours)
                    $hours = floor($diffInSeconds / 3600);
                    $minutes = floor(($diffInSeconds % 3600) / 60);
                    $seconds = $diffInSeconds % 60;
                    $clocking->total_hours = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

                    if (isset($clocking->user->hourly_pay)) {
                        $hoursDecimal = $diffInSeconds / 3600;
                        $clocking->earnings = $hoursDecimal * $clocking->user->hourly_pay;
                    }
                }
            }

            // Calculate total salary for individual record
            $clocking->total_salary = ($clocking->earnings ?? 0) +
                ($clocking->gas_payment ?? 0) +
                ($clocking->purchase_cost ?? 0);

            // ✅ FIXED: Process fix description for display
            if ($clocking->fix_description) {
                // Create shortened version (first 50 characters)
                $clocking->fix_description_short = strlen($clocking->fix_description) > 50
                    ? substr($clocking->fix_description, 0, 50) . '...'
                    : $clocking->fix_description;
            } else {
                $clocking->fix_description_short = null;
            }

            // Format dates for display
            $clocking->formatted_date = $clocking->clock_in ? Carbon::parse($clocking->clock_in)->format('M d, Y') : '';
            $clocking->formatted_clock_in = $clocking->clock_in ? Carbon::parse($clocking->clock_in)->format('g:i A') : '';
            $clocking->formatted_clock_out = $clocking->clock_out ? Carbon::parse($clocking->clock_out)->format('g:i A') : '';
        }

        return view('clockingTable', compact(
            'clockings',
            'users',
            'startDate',
            'endDate',
            'selectedUser',
            'gasPaymentRate',
            'totalMilesIn',
            'totalMilesOut',
            'totalMiles',
            'totalHoursFormatted',
            'totalGasPayment',
            'totalPurchaseCost',
            'totalEarnings',
            'totalFixCount',
            'totalSalary'
        ));
    }




    public function updateGasRate(Request $request)
    {
        $request->validate([
            'gas_payment_rate' => 'required|numeric|min:0'
        ]);

        Configuration::where('key', 'gas_payment_rate')
            ->update(['value' => $request->gas_payment_rate]);

        return back()->with('success', 'Gas payments rate updated successfully');
    }


    // Add this method to the ClockingController class
    public function destroy($id)
    {
        $clocking = Clocking::findOrFail($id);

        // Delete associated images if they exist
        if ($clocking->image_in) {
            Storage::disk('public')->delete($clocking->image_in);
        }
        if ($clocking->image_out) {
            Storage::disk('public')->delete($clocking->image_out);
        }
        if ($clocking->purchase_receipt) {
            Storage::disk('public')->delete($clocking->purchase_receipt);
        }
        // NEW: Delete fix image if it exists
        if ($clocking->fix_image) {
            Storage::disk('public')->delete($clocking->fix_image);
        }

        $clocking->delete();

        return back()->with('success', 'Record deleted successfully');
    }






    public function clockIn(Request $request)
    {
        date_default_timezone_set(config('app.timezone'));

        $request->validate([
            'using_car' => 'required|boolean',
            'miles_in' => 'required_if:using_car,1|nullable|integer',
            'image_in' => 'required_if:using_car,1|nullable|image|mimes:jpg,png,jpeg',
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
            'using_car'     => $request->using_car,
        ]);
        return back()->with('success', 'تم تسجيل الحضور بنجاح');
    }

    public function clockOut(Request $request)
    {
        date_default_timezone_set(config('app.timezone'));

        // Log all incoming request data for debugging
        Log::info('Clock-out request received', [
            'user_id' => Auth::id(),
            'all_request_data' => $request->all(),
            'files' => $request->allFiles(),
            'has_image_out' => $request->hasFile('image_out'),
            'has_purchase_receipt' => $request->hasFile('purchase_receipt'),
            'has_fix_image' => $request->hasFile('fix_image'), // New logging
            'bought_something_value' => $request->input('bought_something'),
            'fixed_something_value' => $request->input('fixed_something'), // New logging
        ]);

        // Log fix details when fixed_something is true
        $fixedSomething = $request->input('fixed_something');
        if ($fixedSomething == '1' || $fixedSomething === true || $fixedSomething === 'true') {
            Log::info('Fix detected - detailed validation check', [
                'fixed_something' => $fixedSomething,
                'fix_description' => $request->input('fix_description'),
                'has_fix_image_file' => $request->hasFile('fix_image'),
                'fix_image_file_info' => $request->hasFile('fix_image') ? [
                    'name' => $request->file('fix_image')->getClientOriginalName(),
                    'size' => $request->file('fix_image')->getSize(),
                    'mime' => $request->file('fix_image')->getMimeType()
                ] : null
            ]);
        }

        try {
            Log::info('Applying validation rules with fix fields');

            // Updated validation rules
            $request->validate([
                'miles_out'         => 'nullable|integer',
                'image_out'         => 'nullable|image|mimes:jpg,png,jpeg',
                'bought_something'  => 'required|boolean',
                'purchase_cost'     => 'required_if:bought_something,1|nullable|numeric',
                'purchase_receipt'  => 'required_if:bought_something,1|nullable|image|mimes:jpg,png,jpeg',
                'fixed_something'   => 'required|boolean', // New validation
                'fix_description'   => 'nullable|string|max:1000', // New validation
                'fix_image'         => 'required_if:fixed_something,1|nullable|image|mimes:jpg,png,jpeg', // New validation
            ]);

            Log::info('Clock-out validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Clock-out validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'fix_validation' => [
                    'fixed_something' => $request->input('fixed_something'),
                    'fix_description' => $request->input('fix_description'),
                    'has_fix_image' => $request->hasFile('fix_image'),
                    'fix_errors' => [
                        'fixed_something' => $e->errors()['fixed_something'] ?? null,
                        'fix_description' => $e->errors()['fix_description'] ?? null,
                        'fix_image' => $e->errors()['fix_image'] ?? null,
                    ]
                ]
            ]);
            throw $e;
        }

        // Handle clock-out image
        $imagePath = null;
        if ($request->hasFile('image_out')) {
            $imagePath = $request->file('image_out')->store('clocking_images', 'public');
        }

        // Handle purchase receipt image
        $receiptPath = null;
        if ($request->hasFile('purchase_receipt')) {
            $receiptPath = $request->file('purchase_receipt')->store('purchase_receipts', 'public');
        }

        // Handle fix image (NEW)
        $fixImagePath = null;
        if ($request->hasFile('fix_image')) {
            $fixImagePath = $request->file('fix_image')->store('fix_images', 'public');
            Log::info('Fix image uploaded', ['path' => $fixImagePath]);
        }

        // Retrieve the existing clocking record for clock out
        Log::info('Looking for active clocking record', ['user_id' => Auth::id()]);
        $clocking = Clocking::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->where('is_clocked_in', true)
            ->first();

        if (!$clocking) {
            Log::error('No active clocking record found for user', ['user_id' => Auth::id()]);
            return back()->withErrors(['error' => 'No active clock-in record found. Please clock in first.']);
        }

        Log::info('Found active clocking record', ['clocking_id' => $clocking->id]);

        // Prepare update data with new fix fields
        $updateData = [
            'clock_out'        => now(),
            'miles_out'        => $request->miles_out,
            'image_out'        => $imagePath,
            'is_clocked_in'    => false,
            'bought_something' => $request->bought_something,
            'purchase_cost'    => $request->purchase_cost,
            'purchase_receipt' => $receiptPath,
            'fixed_something'  => $request->fixed_something, // New field
            'fix_description'  => $request->fix_description, // New field
            'fix_image'        => $fixImagePath,            // New field
        ];

        Log::info('Updating clocking record with data including fix fields', $updateData);

        // Update the record with clock-out details
        $clocking->update($updateData);

        Log::info('Clock-out completed successfully with fix data', ['clocking_id' => $clocking->id]);

        return back()->with('success', 'تم تسجيل الإنصراف بنجاح');
    }


    public function updateClocking(Request $request)
    {
        $request->validate([
            'clocking_id'     => 'required|exists:clockings,id',
            'clock_in'        => 'nullable|date',
            'clock_out'       => 'nullable|date',
            'miles_in'        => 'nullable|numeric',
            'miles_out'       => 'nullable|numeric',
            'purchase_cost'   => 'nullable|numeric|min:0',
            'fixed_something' => 'required|boolean',              // NEW validation
            'fix_description' => 'nullable|string|max:1000',      // NEW validation
        ]);

        $clocking = Clocking::findOrFail($request->clocking_id);

        // Check if clock_out is after clock_in
        if (
            $request->clock_in && $request->clock_out &&
            Carbon::parse($request->clock_out)->lessThanOrEqualTo(Carbon::parse($request->clock_in))
        ) {
            return back()->withErrors([
                'clock_out' => 'Clock-out time cannot be before or equal to clock-in time.'
            ]);
        }

        // Check if miles_out is greater than miles_in when both are provided
        if (
            !is_null($request->miles_in) && !is_null($request->miles_out) &&
            $request->miles_out <= $request->miles_in
        ) {
            return back()->withErrors([
                'miles_out' => 'Miles out must be greater than miles in.'
            ]);
        }

        $updateData = [
            'clock_in'        => $request->clock_in ?: null,
            'clock_out'       => $request->clock_out ?: null,
            'miles_in'        => $request->miles_in ?: null,
            'miles_out'       => $request->miles_out ?: null,
            'purchase_cost'   => $request->purchase_cost ?: null,
            'fixed_something' => $request->fixed_something,        // NEW field
            'fix_description' => $request->fix_description ?: null, // NEW field
        ];

        $clocking->update($updateData);

        return redirect()->back()->with('success', 'Clocking record updated successfully.');
    }
}
