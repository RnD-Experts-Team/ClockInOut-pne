<?php
namespace App\Services\Api;
use App\Models\Clocking;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Modules\Invoice\Models\InvoiceCard;
use Illuminate\Support\Facades\Log;
use Modules\Invoice\Services\MileageDistributionService;
use App\Models\Configuration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class ClockingService
{
    //for user:
    public function getClockingData()
    {
        date_default_timezone_set(config('app.timezone'));

        // latest clocking
        $clocking = Clocking::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->where('is_clocked_in', true)
            ->first();

        $using_car = $clocking ? $clocking->using_car : false;
        //get invice cards for current session
        $invoiceCards = collect();
        $stores = collect();
        $assignedStores = collect();

        if ($clocking) {

            $invoiceCards = InvoiceCard::with(['store', 'materials', 'maintenanceRequests'])
                ->where('clocking_id', $clocking->id)
                ->orderBy('start_time', 'desc')
                ->get();

            $assignedStores = Store::active()
                ->whereHas('maintenanceRequests', function ($query) {
                    $query->where('assigned_to', Auth::id())
                        ->whereIn('status', ['on_hold', 'in_progress', 'pending']);
                })
                ->withCount([
                    'maintenanceRequests as pending_tasks_count' => function ($query) {
                        $query->where('assigned_to', Auth::id())
                            ->whereIn('status', ['on_hold', 'in_progress', 'pending']);
                    }
                ])
                ->orderBy('store_number')
                ->get();

            $stores = Store::active()
                ->orderBy('store_number')
                ->get();
        }

        return [
            'clocking' => $clocking,
            'using_car' => $using_car,
            'invoiceCards' => $invoiceCards,
            'stores' => $stores,
            'assignedStores' => $assignedStores,
        ];
    }
     public function clockIn($request)
    {
        date_default_timezone_set(config('app.timezone'));

        $imagePath = null;

        if ($request->hasFile('image_in')) {
            $imagePath = $request->file('image_in')->store('clocking_images', 'public');
        }

        $clocking = Clocking::create([
            'user_id'       => Auth::id(),
            'clock_in'      => now(),
            'miles_in'      => $request->miles_in,
            'image_in'      => $imagePath,
            'is_clocked_in' => true,
            'using_car'     => $request->using_car,
        ]);

        return $clocking;
    }
    public function clockOut($request)
    {
        date_default_timezone_set(config('app.timezone'));

        $imagePath = null;

        if ($request->hasFile('image_out')) {
            $imagePath = $request->file('image_out')->store('clocking_images', 'public');
        }

        $clocking = Clocking::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->where('is_clocked_in', true)
            ->first();

        if (!$clocking) {
            Log::error('No active clocking record found for user', [
                'user_id' => Auth::id()
            ]);

            return [
                'error' => true,
                'message' => 'No active clock-in record found. Please clock in first.'
            ];
        }
        // Validate miles_out >= miles_in if using car

        if ($clocking->using_car && $request->miles_out && $clocking->miles_in) {
            if ($request->miles_out < $clocking->miles_in) {
                return [
                    'error' => true,
                    'message' => 'Clock-out odometer must be greater than or equal to clock-in odometer.'
                ];
            }
        }

        $clocking->update([
            'clock_out'     => now(),
            'miles_out'     => $request->miles_out,
            'image_out'     => $imagePath,
            'is_clocked_in' => false,
        ]);
        // Distribute final segment miles across invoice cards

        if ($clocking->using_car && $request->miles_out) {
            try {
                $mileageService = new \Modules\Invoice\Services\MileageDistributionService();
                $mileageService->distributeReturnMiles($clocking->id);

                Log::info('Final segment miles distributed successfully', [
                    'clocking_id' => $clocking->id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to distribute final segment miles', [
                    'clocking_id' => $clocking->id,
                    'error' => $e->getMessage()
                ]);
            // Don't fail the clock-out if mileage distribution fails

            }
        }

        Log::info('Clock-out completed successfully', [
            'clocking_id' => $clocking->id
        ]);

        return $clocking;
    }
    //for admin
     public function clockingTable($request)
    {

        if (Auth::user()->role !== 'admin') {
            abort(403);
        }
        // Get gas payments rate from configuration

        $gasPaymentRate = Configuration::getGasPaymentRate();
        // Get all users for the dropdown

        $users = User::orderBy('name')->get();
        // Get filter parameters

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $selectedUser = $request->input('user_id');

        $query = Clocking::with('user');

        if ($startDate) {
            $query->whereDate('clock_in', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('clock_in', '<=', $endDate);
        }

        if ($selectedUser) {
            $query->where('user_id', $selectedUser);
        }

        $totalQuery = clone $query;

        $clockings = $query->latest()->paginate(25);

        $totalMilesIn = $totalMilesOut = $totalMiles = $totalSeconds = 0;
        $totalGasPayment = $totalPurchaseCost = $totalEarnings = $totalSalary = 0;
        $totalFixCount = 0;

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

            $totalPurchaseCost += $clocking->purchase_cost ?? 0;

            if ($clocking->fixed_something) {
                $totalFixCount++;
            }
        }

        $totalSalary = $totalEarnings + $totalGasPayment + $totalPurchaseCost;

        $totalHoursFormatted = sprintf(
            '%02d:%02d:%02d',
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

            $clocking->total_salary =
                ($clocking->earnings ?? 0) +
                ($clocking->gas_payment ?? 0) +
                ($clocking->purchase_cost ?? 0);
            // ✅ FIXED: Process fix description for display

            if ($clocking->fix_description) {
                // Create shortened version (first 50 characters)

                $clocking->fix_description_short =
                    strlen($clocking->fix_description) > 50
                    ? substr($clocking->fix_description, 0, 50) . '...'
                    : $clocking->fix_description;

            } else {

                $clocking->fix_description_short = null;
            }

            $clocking->formatted_date =
                $clocking->clock_in
                ? Carbon::parse($clocking->clock_in)->format('M d, Y')
                : '';

            $clocking->formatted_clock_in =
                $clocking->clock_in
                ? Carbon::parse($clocking->clock_in)->format('g:i A')
                : '';

            $clocking->formatted_clock_out =
                $clocking->clock_out
                ? Carbon::parse($clocking->clock_out)->format('g:i A')
                : '';
        }

        return [
            'clockings' => $clockings,
            'users' => $users,
            'gas_payment_rate' => $gasPaymentRate,
            'totals' => [
                'totalMilesIn' => $totalMilesIn,
                'totalMilesOut' => $totalMilesOut,
                'totalMiles' => $totalMiles,
                'totalHours' => $totalHoursFormatted,
                'totalGasPayment' => $totalGasPayment,
                'totalPurchaseCost' => $totalPurchaseCost,
                'totalEarnings' => $totalEarnings,
                'totalFixCount' => $totalFixCount,
                'totalSalary' => $totalSalary
            ]
        ];
    }


    public function updateGasRate($rate)
    {
        Configuration::where('key', 'gas_payment_rate')
            ->update(['value' => $rate]);
    }


    public function deleteClocking($id)
    {
        $clocking = Clocking::findOrFail($id);

        if ($clocking->image_in) {
            Storage::disk('public')->delete($clocking->image_in);
        }

        if ($clocking->image_out) {
            Storage::disk('public')->delete($clocking->image_out);
        }

        if ($clocking->purchase_receipt) {
            Storage::disk('public')->delete($clocking->purchase_receipt);
        }

        if ($clocking->fix_image) {
            Storage::disk('public')->delete($clocking->fix_image);
        }

        $clocking->delete();
    }
    public function updateClocking($request)
    {
        $clocking = Clocking::findOrFail($request->clocking_id);
        // Check if clock_out is after clock_in

        if (
            $request->clock_in &&
            $request->clock_out &&
            Carbon::parse($request->clock_out)
                ->lessThanOrEqualTo(Carbon::parse($request->clock_in))
        ) {
            throw new \Exception(
                'Clock-out time cannot be before or equal to clock-in time.'
            );
        }

        if (
            !is_null($request->miles_in) &&
            !is_null($request->miles_out) &&
            $request->miles_out <= $request->miles_in
        ) {
            throw new \Exception(
                'Miles out must be greater than miles in.'
            );
        }

        $updateData = [
            'clock_in'        => $request->clock_in ?: null,
            'clock_out'       => $request->clock_out ?: null,
            'miles_in'        => $request->miles_in ?: null,
            'miles_out'       => $request->miles_out ?: null,
            'purchase_cost'   => $request->purchase_cost ?: null,
            'fixed_something' => $request->fixed_something,
            'fix_description' => $request->fix_description ?: null,
        ];

        $clocking->update($updateData);

        return $clocking;
    }
}