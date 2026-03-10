<?php
namespace App\Services\Api;
use App\Models\Clocking;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Modules\Invoice\Models\InvoiceCard;
use Illuminate\Support\Facades\Log;
use Modules\Invoice\Services\MileageDistributionService;
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
}