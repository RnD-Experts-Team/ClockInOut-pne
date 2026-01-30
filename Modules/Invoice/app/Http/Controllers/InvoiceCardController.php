<?php

namespace Modules\Invoice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Clocking;
use App\Models\Store;
use App\Models\MaintenanceRequest;
use App\Models\Configuration;
use Modules\Invoice\Models\InvoiceCard;
use Modules\Invoice\Models\InvoiceCardMaterial;
use Modules\Invoice\Services\MileageDistributionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceCardController extends Controller
{
    protected $mileageService;

    public function __construct(MileageDistributionService $mileageService)
    {
        $this->mileageService = $mileageService;
    }

    /**
     * Display all invoice cards for current clocking session (User)
     * OR all completed cards (Admin)
     */
    public function index()
    {
        // Admin view: Show all completed cards
        if (Auth::user()->role === 'admin') {
            $invoiceCards = InvoiceCard::with(['store', 'user', 'materials', 'maintenanceRequests'])
                ->where('status', 'completed')
                ->whereDoesntHave('invoice') // Only cards without invoices
                ->orderBy('end_time', 'desc')
                ->paginate(20);

            $stores = Store::orderBy('store_number')->get();

            return view('invoice::cards.admin-index', compact('invoiceCards', 'stores'));
        }

        // User view: Show cards for current clocking session
        $clocking = Clocking::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->where('is_clocked_in', true)
            ->first();

        if (!$clocking) {
            return redirect()->route('clocking.index')
                ->with('error', 'Please clock in first to create invoice cards.');
        }

        $invoiceCards = InvoiceCard::with(['store', 'materials', 'maintenanceRequests'])
            ->where('clocking_id', $clocking->id)
            ->orderBy('start_time', 'desc')
            ->get();

        $stores = Store::active()->orderBy('store_number')->get();

        return view('invoice::cards.index', compact('invoiceCards', 'stores', 'clocking'));
    }

    /**
     * Store a new invoice card
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'arrival_odometer' => 'nullable|numeric|min:0',
            'arrival_odometer_image' => 'nullable|image', // No size limit
            'maintenance_request_ids' => 'nullable|array',
            'maintenance_request_ids.*' => 'exists:maintenance_requests,id',
        ]);

        $clocking = Clocking::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->where('is_clocked_in', true)
            ->first();

        if (!$clocking) {
            return back()->withErrors(['error' => 'No active clocking session found.']);
        }

        // Validate odometer if using car
        if ($clocking->using_car && $request->arrival_odometer) {
            $odometerService = new \Modules\Invoice\Services\OdometerCalculationService();
            
            // Create temporary card to get previous odometer
            $tempCard = new InvoiceCard([
                'clocking_id' => $clocking->id,
                'start_time' => now(),
            ]);
            
            $previousOdometer = $odometerService->getPreviousOdometer($tempCard);
            $validation = $odometerService->validateOdometer($request->arrival_odometer, $previousOdometer);
            
            if (!$validation['valid']) {
                return back()->withErrors(['arrival_odometer' => $validation['error']]);
            }
        }

        DB::beginTransaction();
        try {
            // Check for existing incomplete card for this store and user
            $existingCard = InvoiceCard::where('store_id', $request->store_id)
                ->where('user_id', Auth::id())
                ->where('status', 'not_done')
                ->whereNotNull('end_time') // Card was previously marked as not_done
                ->orderBy('created_at', 'desc')
                ->first();

            if ($existingCard) {
                // Reopen existing card instead of creating new one
                Log::info('Reopening existing incomplete card', [
                    'existing_card_id' => $existingCard->id,
                    'store_id' => $request->store_id,
                    'user_id' => Auth::id(),
                    'previous_labor_hours' => $existingCard->labor_hours,
                    'previous_accumulated_labor_hours' => $existingCard->accumulated_labor_hours,
                    'previous_calculated_miles' => $existingCard->calculated_miles,
                    'previous_allocated_return_miles' => $existingCard->allocated_return_miles,
                    'previous_total_miles' => $existingCard->total_miles,
                    'previous_driving_time_hours' => $existingCard->driving_time_hours,
                    'previous_allocated_return_driving_time' => $existingCard->allocated_return_driving_time,
                ]);

                // Preserve distribution values from previous session
                $previousAllocatedReturnMiles = $existingCard->allocated_return_miles ?? 0;
                $previousAllocatedReturnDrivingTime = $existingCard->allocated_return_driving_time ?? 0;
                $previousTotalMiles = $existingCard->total_miles ?? 0;
                $previousTotalDrivingTime = $existingCard->total_driving_time_hours ?? 0;

                // Accumulate previous labor hours before updating start_time
                if ($existingCard->labor_hours > 0) {
                    $existingCard->accumulated_labor_hours = ($existingCard->accumulated_labor_hours ?? 0) + $existingCard->labor_hours;
                    Log::info('Accumulating labor hours for reopened card', [
                        'card_id' => $existingCard->id,
                        'previous_session_hours' => $existingCard->labor_hours,
                        'total_accumulated_hours' => $existingCard->accumulated_labor_hours,
                    ]);
                }

                // Update existing card to continue work
                $existingCard->clocking_id = $clocking->id; // Link to current clocking session
                $existingCard->status = 'in_progress';
                $existingCard->start_time = now(); // Update start time for new session
                $existingCard->end_time = null; // Clear end time to reopen
                $existingCard->not_done_reason = null; // Clear previous not_done reason
                $existingCard->labor_hours = 0; // Reset current session labor hours
                
                // Update odometer information if provided
                if ($request->arrival_odometer) {
                    $existingCard->arrival_odometer = $request->arrival_odometer;
                }
                
                // Handle new odometer image upload
                if ($request->hasFile('arrival_odometer_image')) {
                    $odometerImagePath = $request->file('arrival_odometer_image')->store('odometer_images', 'public');
                    $existingCard->arrival_odometer_image = $odometerImagePath;
                }
                
                $existingCard->save();

                // Calculate distance, driving time, and driving payment if using car
                if ($clocking->using_car && $existingCard->arrival_odometer) {
                    $odometerService = new \Modules\Invoice\Services\OdometerCalculationService();
                    // Use accumulate=true to add new driving data to existing data
                    $odometerService->calculateAll($existingCard, true);
                    
                    // Refresh to get updated values
                    $existingCard->refresh();
                }

                // CRITICAL: Restore distribution values from previous session
                // The calculateAll above only handles the new session's driving data
                // We need to preserve the distribution from the previous session
                if ($previousAllocatedReturnMiles > 0 || $previousAllocatedReturnDrivingTime > 0) {
                    Log::info('Restoring distribution values from previous session', [
                        'card_id' => $existingCard->id,
                        'previous_allocated_return_miles' => $previousAllocatedReturnMiles,
                        'previous_allocated_return_driving_time' => $previousAllocatedReturnDrivingTime,
                        'current_calculated_miles' => $existingCard->calculated_miles,
                        'current_driving_time_hours' => $existingCard->driving_time_hours,
                    ]);

                    // Add previous distribution to current values
                    $existingCard->allocated_return_miles = $previousAllocatedReturnMiles;
                    $existingCard->allocated_return_driving_time = $previousAllocatedReturnDrivingTime;
                    
                    // Recalculate totals including previous distribution
                    $existingCard->total_miles = ($existingCard->calculated_miles ?? 0) + $existingCard->allocated_return_miles;
                    $existingCard->total_driving_time_hours = ($existingCard->driving_time_hours ?? 0) + $existingCard->allocated_return_driving_time;
                    
                    // Recalculate payments with preserved distribution
                    $mileRate = \App\Models\Configuration::getGasPaymentRate();
                    $existingCard->mileage_payment = $existingCard->total_miles * $mileRate;
                    
                    $hourlyRate = $existingCard->user->hourly_pay ?? 20;
                    $existingCard->driving_time_payment = $existingCard->total_driving_time_hours * $hourlyRate;
                    
                    $existingCard->save();

                    Log::info('Distribution values restored for reopened card', [
                        'card_id' => $existingCard->id,
                        'final_calculated_miles' => $existingCard->calculated_miles,
                        'final_allocated_return_miles' => $existingCard->allocated_return_miles,
                        'final_total_miles' => $existingCard->total_miles,
                        'final_driving_time_hours' => $existingCard->driving_time_hours,
                        'final_allocated_return_driving_time' => $existingCard->allocated_return_driving_time,
                        'final_total_driving_time_hours' => $existingCard->total_driving_time_hours,
                        'final_mileage_payment' => $existingCard->mileage_payment,
                        'final_driving_time_payment' => $existingCard->driving_time_payment,
                    ]);
                }

                // Add any new maintenance requests (don't replace existing ones)
                if ($request->has('maintenance_request_ids')) {
                    $existingRequestIds = $existingCard->maintenanceRequests->pluck('id')->toArray();
                    $newRequestIds = array_diff($request->maintenance_request_ids, $existingRequestIds);
                    
                    if (!empty($newRequestIds)) {
                        $existingCard->maintenanceRequests()->attach($newRequestIds);
                    }
                }

                DB::commit();

                return redirect()->route('invoice.cards.show', $existingCard->id)
                    ->with('success', 'Continuing work on previous incomplete card. All your previous work and distribution has been preserved!');
            }

            // No existing incomplete card found, create new one
            // Handle odometer image upload
            $odometerImagePath = null;
            if ($request->hasFile('arrival_odometer_image')) {
                $odometerImagePath = $request->file('arrival_odometer_image')->store('odometer_images', 'public');
            }
            
            // Create invoice card
            $card = InvoiceCard::create([
                'clocking_id' => $clocking->id,
                'store_id' => $request->store_id,
                'user_id' => Auth::id(),
                'start_time' => now(),
                'arrival_odometer' => $request->arrival_odometer,
                'arrival_odometer_image' => $odometerImagePath,
                'status' => 'in_progress',
            ]);

            // Calculate distance, driving time, and driving payment if using car
            if ($clocking->using_car && $request->arrival_odometer) {
                $odometerService = new \Modules\Invoice\Services\OdometerCalculationService();
                $odometerService->calculateAll($card);
                
                // Refresh to get updated values
                $card->refresh();
            }

            // Associate maintenance requests
            if ($request->has('maintenance_request_ids')) {
                $card->maintenanceRequests()->attach($request->maintenance_request_ids);
            }

            DB::commit();

            return redirect()->route('invoice.cards.show', $card->id)
                ->with('success', 'Invoice card created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create invoice card', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to create invoice card.']);
        }
    }

    /**
     * Show single invoice card
     */
    public function show($id)
    {
        $card = InvoiceCard::with(['store', 'materials', 'maintenanceRequests', 'user', 'tasks.maintenanceRequest'])
            ->findOrFail($id);

        // Check authorization
        if ($card->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
        
        // Get maintenance request IDs linked to this card
        $linkedRequestIds = $card->maintenanceRequests->pluck('id')->toArray();
        
        // Determine which user's tasks to show
        // If admin is viewing, show tasks assigned to the card's original user
        // If user is viewing their own card, show their tasks
        $targetUserId = (Auth::user()->role === 'admin') ? $card->user_id : Auth::id();
        
        // Get maintenance requests ASSIGNED TO TARGET USER for this store
        // 1. Regular MaintenanceRequests assigned to target user (exclude 'done' status)
        $maintenanceRequests = \App\Models\MaintenanceRequest::where('store_id', $card->store_id)
            ->where('assigned_to', $targetUserId)
            ->whereNotIn('status', ['done'])
            ->with(['urgencyLevel', 'requester', 'assignedTo'])
            ->orderBy('urgency_level_id', 'asc')
            ->orderBy('due_date', 'asc')
            ->get();

        // 2. Native Requests assigned to target user
        $nativeRequests = \App\Models\Native\NativeRequest::where('store_id', $card->store_id)
            ->where('assigned_to', $targetUserId)
            ->whereIn('status', ['pending', 'in_progress', 'received'])
            ->with(['urgencyLevel', 'requester', 'assignedTo'])
            ->orderBy('urgency_level_id', 'asc')
            ->orderBy('request_date', 'asc')
            ->get();

        // Combine both types into one collection
        $allRequests = $maintenanceRequests->concat($nativeRequests);
        
        // Get all available request IDs (for equipment query)
        $availableRequestIds = $allRequests->pluck('id')->toArray();
        
        // Get admin equipment grouped by maintenance request ID for JavaScript
        // Fetch equipment for ALL requests the user can see, not just linked ones
        $adminEquipmentByRequest = \App\Models\Payment::where('store_id', $card->store_id)
            ->whereNotNull('maintenance_request_id')
            ->whereIn('maintenance_request_id', $availableRequestIds)
            ->with('equipmentItems')
            ->get()
            ->groupBy('maintenance_request_id')
            ->map(function($payments) {
                return $payments->flatMap(function($payment) {
                    return $payment->equipmentItems->map(function($item) use ($payment) {
                        return [
                            'item_name' => $item->item_name,
                            'quantity' => $item->quantity,
                            'unit_cost' => $item->unit_cost,
                            'total_cost' => $item->total_cost,
                        ];
                    });
                });
            });
        return view('invoice::cards.show', compact('card', 'allRequests', 'adminEquipmentByRequest', 'linkedRequestIds'));
    }

    /**
     * Add material to invoice card
     */
    public function addMaterial(Request $request, $cardId)
    {
        Log::info('Material add request received', [
            'card_id' => $cardId,
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        $request->validate([
            'item_name' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'receipt_photos' => 'nullable|array|max:5',
            'receipt_photos.*' => 'image|mimes:jpg,png,jpeg|max:5120',
            'maintenance_request_id' => 'nullable|exists:maintenance_requests,id',
        ]);

        $card = InvoiceCard::findOrFail($cardId);

        // Check authorization
        if ($card->user_id !== Auth::id()) {
            Log::warning('Unauthorized material add attempt', [
                'card_id' => $cardId,
                'card_user_id' => $card->user_id,
                'requesting_user_id' => Auth::id()
            ]);
            abort(403, 'Unauthorized');
        }

        Log::info('Starting material add transaction', [
            'card_id' => $cardId,
            'clocking_id' => $card->clocking_id
        ]);

        DB::beginTransaction();
        try {
            // Handle receipt photos
            $photoPaths = [];
            if ($request->hasFile('receipt_photos')) {
                Log::info('Processing receipt photos', [
                    'photo_count' => count($request->file('receipt_photos'))
                ]);
                foreach ($request->file('receipt_photos') as $photo) {
                    $path = $photo->store('invoice_receipts', 'public');
                    $photoPaths[] = $path;
                }
            }

            // Create material
            $material = InvoiceCardMaterial::create([
                'invoice_card_id' => $card->id,
                'maintenance_request_id' => $request->maintenance_request_id, // Associate with specific task
                'item_name' => $request->item_name,
                'cost' => $request->cost,
                'receipt_photos' => $photoPaths,
            ]);

            Log::info('Material created successfully', [
                'material_id' => $material->id,
                'cost' => $material->cost
            ]);

            // Recalculate materials cost
            $card->calculateMaterialsCost();
            $card->calculateTotalCost();

            // Sync the associated Clocking record for backward compatibility
            if ($card->clocking_id) {
                Log::info('Syncing with clocking record', [
                    'clocking_id' => $card->clocking_id
                ]);
                $this->syncClockingWithMaterials($card->clocking_id);
            } else {
                Log::warning('No clocking_id found for card', [
                    'card_id' => $card->id
                ]);
            }

            DB::commit();

            $taskInfo = $request->maintenance_request_id ? " for Task #{$request->maintenance_request_id}" : "";
            Log::info('Material add completed successfully', [
                'material_id' => $material->id,
                'task_info' => $taskInfo
            ]);
            
            return back()->with('success', "Material added successfully{$taskInfo}!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add material', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to add material: ' . $e->getMessage()]);
        }
    }

    /**
     * Complete invoice card
     */
    public function complete(Request $request, $cardId)
    {
        $request->validate([
            'status' => 'required|in:completed,not_done',
            'notes' => 'nullable|string|max:1000',
            'not_done_reason' => 'required_if:status,not_done|nullable|string|max:1000',
            'selected_maintenance_request_id' => 'nullable|exists:maintenance_requests,id',
        ]);

        $card = InvoiceCard::findOrFail($cardId);

        // Check authorization
        if ($card->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Validate: Prevent completing card if there are incomplete tasks
        if ($request->status === 'completed') {
            $incompleteTasks = $card->tasks()
                ->where('task_status', '!=', 'completed')
                ->with('maintenanceRequest')
                ->get();

            if ($incompleteTasks->count() > 0) {
                $taskNumbers = $incompleteTasks->pluck('maintenanceRequest.id')->filter()->implode(', #');
                $errorMessage = 'Cannot complete this card! You have ' . $incompleteTasks->count() . ' incomplete task(s).';
                
                if ($taskNumbers) {
                    $errorMessage .= ' Incomplete tasks: #' . $taskNumbers;
                }
                
                $errorMessage .= ' Please complete all tasks before marking the card as done, or mark the card as "Not Done" instead.';
                
                return back()->withErrors(['error' => $errorMessage]);
            }
        }

        DB::beginTransaction();
        try {
            // Update card
            $card->end_time = now();
            $card->status = $request->status;
            $card->notes = $request->notes;
            $card->not_done_reason = $request->not_done_reason;
            $card->save();

            // Link selected maintenance request if provided
            if ($request->selected_maintenance_request_id) {
                // Sync will replace existing relationships
                $card->maintenanceRequests()->sync([$request->selected_maintenance_request_id]);
            }

            // Calculate costs
            $card->calculateLaborCost();
            $card->calculateMaterialsCost();
            
            // Calculate mileage payment - preserve existing distribution
            $mileRate = Configuration::getGasPaymentRate();
            $card->calculateMileagePayment($mileRate);
            
            $card->calculateTotalCost();

            // Process ticket completion if card is completed
            if ($request->status === 'completed') {
                try {
                    $ticketService = new \App\Services\TicketCompletionService(
                        new \App\Services\CognitoFormsService()
                    );
                    $ticketService->processCardCompletion($card);
                    
                    Log::info('Ticket completion processed for card', ['card_id' => $card->id]);
                } catch (\Exception $e) {
                    Log::error('Failed to process ticket completion', [
                        'card_id' => $card->id,
                        'error' => $e->getMessage()
                    ]);
                    // Don't fail the card completion if ticket processing fails
                }
            }

            DB::commit();

            return redirect()->route('invoice.cards.index')
                ->with('success', 'Invoice card completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete invoice card', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to complete invoice card.']);
        }
    }

    /**
     * Delete material
     */
    public function deleteMaterial($materialId)
    {
        $material = InvoiceCardMaterial::findOrFail($materialId);
        $card = $material->invoiceCard;

        // Check authorization
        if ($card->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        DB::beginTransaction();
        try {
            // Store material cost for clocking update
            $materialCost = $material->cost;
            
            // Delete photos
            if ($material->receipt_photos) {
                foreach ($material->receipt_photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }

            $material->delete();

            // Recalculate costs
            $card->calculateMaterialsCost();
            $card->calculateTotalCost();

            // Sync the associated Clocking record for backward compatibility
            if ($card->clocking_id) {
                $this->syncClockingWithMaterials($card->clocking_id);
            }

            DB::commit();

            return back()->with('success', 'Material deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete material', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to delete material.']);
        }
    }

    /**
     * Add a task to an existing card
     */
    public function addTask(Request $request, $cardId)
    {
        $request->validate([
            'maintenance_request_id' => 'required|exists:maintenance_requests,id'
        ]);

        $card = InvoiceCard::findOrFail($cardId);

        if ($card->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $service = new \Modules\Invoice\Services\MultiTaskCardService();
        $result = $service->addTaskToCard($cardId, $request->maintenance_request_id);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        return response()->json(['success' => true, 'task' => $result['task']]);
    }

    /**
     * Remove a task from a card
     */
    public function removeTask(Request $request, $cardId)
    {
        $request->validate([
            'maintenance_request_id' => 'required|exists:maintenance_requests,id'
        ]);

        $card = InvoiceCard::findOrFail($cardId);

        if ($card->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $service = new \Modules\Invoice\Services\MultiTaskCardService();
        $result = $service->removeTaskFromCard($cardId, $request->maintenance_request_id);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark a single task on a card as complete
     */
    public function completeTask(Request $request, $cardId)
    {
        $request->validate([
            'maintenance_request_id' => 'required|exists:maintenance_requests,id',
            'complete_single_task' => 'nullable|boolean'
        ]);

        $card = InvoiceCard::findOrFail($cardId);

        if ($card->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $service = new \Modules\Invoice\Services\MultiTaskCardService();
        $result = $service->markTaskComplete($cardId, $request->maintenance_request_id);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        // Only auto-finalize if this is NOT a single task completion request
        $shouldAutoFinalize = !$request->boolean('complete_single_task', false);

        // If all tasks complete and auto-finalize is enabled, finalize the card
        if ($result['all_tasks_complete'] && $shouldAutoFinalize) {
            DB::beginTransaction();
            try {
                $card->end_time = now();
                $card->status = 'completed';
                $card->save();

                // Recalculate costs
                $card->calculateLaborCost();
                $card->calculateMaterialsCost();
                $card->calculateMileagePayment(\App\Models\Configuration::getGasPaymentRate());
                $card->calculateTotalCost();

                // Process ticket completion actions
                $ticketService = new \App\Services\TicketCompletionService(new \App\Services\CognitoFormsService());
                $ticketService->processCardCompletion($card);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to finalize card after completing tasks', ['error' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Failed to finalize card'], 500);
            }
        }

        return response()->json([
            'success' => true, 
            'all_tasks_complete' => $result['all_tasks_complete'], 
            'task' => $result['task'],
            'card_finalized' => $result['all_tasks_complete'] && $shouldAutoFinalize
        ]);
    }

    /**
     * Sync Clocking record with all materials from all cards in the session
     */
    private function syncClockingWithMaterials($clockingId)
    {
        $clocking = \App\Models\Clocking::find($clockingId);
        if (!$clocking) {
            return;
        }

        // Get all materials from all cards in this clocking session
        $totalMaterialsCost = InvoiceCardMaterial::whereHas('invoiceCard', function($query) use ($clockingId) {
            $query->where('clocking_id', $clockingId);
        })->sum('cost');

        // Get the first receipt photo from any material in this session
        $firstReceiptPhoto = InvoiceCardMaterial::whereHas('invoiceCard', function($query) use ($clockingId) {
            $query->where('clocking_id', $clockingId);
        })->whereNotNull('receipt_photos')->first();

        // Update clocking record
        $clocking->bought_something = $totalMaterialsCost > 0;
        $clocking->purchase_cost = $totalMaterialsCost;
        
        if ($firstReceiptPhoto && !empty($firstReceiptPhoto->receipt_photos)) {
            $clocking->purchase_receipt = $firstReceiptPhoto->receipt_photos[0];
        } elseif ($totalMaterialsCost == 0) {
            $clocking->purchase_receipt = null;
        }
        
        $clocking->save();

        Log::info('Synced clocking record with all materials', [
            'clocking_id' => $clockingId,
            'total_materials_cost' => $totalMaterialsCost,
            'bought_something' => $clocking->bought_something
        ]);
    }

    /**
     * Sync all existing clocking records with their materials (for data migration)
     */
    public function syncAllClockingRecords()
    {
        // Only allow admin access
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $clockingIds = InvoiceCard::distinct()->pluck('clocking_id')->filter();
        $syncedCount = 0;

        foreach ($clockingIds as $clockingId) {
            $this->syncClockingWithMaterials($clockingId);
            $syncedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Synced {$syncedCount} clocking records with their materials",
            'synced_count' => $syncedCount
        ]);
    }

    /**
     * Get incomplete cards for current user (AJAX endpoint)
     */
    public function getIncompleteCards()
    {
        $incompleteCards = InvoiceCard::where('user_id', Auth::id())
            ->where('status', 'not_done')
            ->whereNotNull('end_time')
            ->with('store')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($card) {
                return [
                    'id' => $card->id,
                    'store_id' => $card->store_id,
                    'store_name' => $card->store->store_number . ' - ' . $card->store->name,
                    'created_at' => $card->created_at->format('M d, Y g:i A'),
                    'not_done_reason' => $card->not_done_reason,
                    'materials_count' => $card->materials->count(),
                    'tasks_count' => $card->maintenanceRequests->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'incomplete_cards' => $incompleteCards
        ]);
    }
}
