<?php

namespace App\Http\Controllers;

use App\Models\Clocking;
use App\Models\User;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

            // Calculate hours and earnings
            if ($clocking->clock_in && $clocking->clock_out) {
                $start = Carbon::parse($clocking->clock_in);
                $end = Carbon::parse($clocking->clock_out);
                $diffInSeconds = $end->timestamp - $start->timestamp;
                $totalSeconds += $diffInSeconds;

                if (isset($clocking->user->hourly_pay)) {
                    $hoursDecimal = $diffInSeconds / 3600;
                    $earnings = $hoursDecimal * $clocking->user->hourly_pay;
                    $totalEarnings += $earnings;
                }
            }

            // Add purchase cost to totals
            $totalPurchaseCost += $clocking->purchase_cost ?? 0;
        }

        // Format total hours
        $totalHoursFormatted = sprintf('%02d:%02d:%02d',
            floor($totalSeconds / 3600),
            floor(($totalSeconds % 3600) / 60),
            $totalSeconds % 60
        );

        // Calculate total salary for all filtered records
        $totalSalary = $totalEarnings + $totalGasPayment + $totalPurchaseCost;

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

                $clocking->total_hours = gmdate('H:i:s', $diffInSeconds);

                if (isset($clocking->user->hourly_pay)) {
                    $hoursDecimal = $diffInSeconds / 3600;
                    $clocking->earnings = $hoursDecimal * $clocking->user->hourly_pay;
                }
            }

            // Calculate total salary for individual record
            $clocking->total_salary = ($clocking->earnings ?? 0) +
                                     ($clocking->gas_payment ?? 0) +
                                     ($clocking->purchase_cost ?? 0);
            $totalSalary += $clocking->total_salary;

            // Format dates for display
            $clocking->formatted_date = $clocking->clock_in ? Carbon::parse($clocking->clock_in)->format('M d, Y') : '';
            $clocking->formatted_clock_in = $clocking->clock_in ? Carbon::parse($clocking->clock_in)->format('g:i A') : '';
            $clocking->formatted_clock_out = $clocking->clock_out ? Carbon::parse($clocking->clock_out)->format('g:i A') : '';
        }

        // Format total hours
        $totalHoursFormatted = sprintf('%02d:%02d:%02d',
            floor($totalSeconds / 3600),
            floor(($totalSeconds % 3600) / 60),
            $totalSeconds % 60
        );

        // Get all users for filter
        $users = User::all();

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

    $clocking->delete();

    return back()->with('success', 'Record deleted successfully');
}






    public function clockIn(Request $request)
    {
        $request->validate([
            'using_car' => 'required|boolean',
            'miles_in' => 'required_if:using_car,1|nullable|integer',
            'image_in' => 'required_if:using_car,1|nullable|image|mimes:jpg,png,jpeg|max:2048',
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
    // Validate new fields as well
    $request->validate([
        'miles_out'         => 'nullable|integer',
        'image_out'         => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        'bought_something'  => 'required|boolean',
        'purchase_cost'     => 'required_if:bought_something,1|nullable|numeric',
        'purchase_receipt'  => 'required_if:bought_something,1|nullable|image|mimes:jpg,png,jpeg|max:2048',
    ]);

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

    // Retrieve the existing clocking record for clock out
    $clocking = Clocking::where('user_id', Auth::id())
        ->whereNull('clock_out')
        ->where('is_clocked_in', true)
        ->firstOrFail();

    // Update the record with clock-out details
    $clocking->update([
        'clock_out'        => now(),
        'miles_out'        => $request->miles_out,
        'image_out'        => $imagePath,
        'is_clocked_in'    => false,

        // New fields
        'bought_something' => $request->bought_something,
        'purchase_cost'    => $request->purchase_cost,
        'purchase_receipt' => $receiptPath,
    ]);

    return back()->with('success', 'تم تسجيل الإنصراف بنجاح');
}


    public function updateClocking(Request $request)
    {
        $request->validate([
            'clocking_id' => 'required|exists:clockings,id',
            'clock_in'    => 'nullable|date',
            'clock_out'   => 'nullable|date',
            'miles_in'    => 'nullable|numeric',
            'miles_out'   => 'nullable|numeric',
            'purchase_cost' => 'nullable|numeric|min:0',
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
            'clock_in'      => $request->clock_in ?: null,
            'clock_out'     => $request->clock_out ?: null,
            'miles_in'      => $request->miles_in ?: null,
            'miles_out'     => $request->miles_out ?: null,
            'purchase_cost' => $request->purchase_cost ?: null,
        ];

        $clocking->update($updateData);

        return redirect()->back()->with('success', 'Clocking record updated successfully.');
    }
}
