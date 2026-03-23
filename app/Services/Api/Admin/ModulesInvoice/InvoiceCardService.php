<?php

namespace App\Services\Api\Admin\ModulesInvoice;

use App\Models\Clocking;
use App\Models\Configuration;
use App\Models\ModulesInvoice\InvoiceCard;
use App\Models\ModulesInvoice\InvoiceCardMaterial;
use App\Models\ModulesInvoice\InvoiceCardTask;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceCardService
{
    public function store($request)
    {
        $clocking = Clocking::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->where('is_clocked_in', true)
            ->first();

        if (!$clocking) {
            throw new \Exception('No active clocking session found.');
        }

        if ($clocking->using_car && $request->arrival_odometer) {
            $odometerService = new OdometerCalculationService();
            // Create temporary card to get previous odometer
            $tempCard = new InvoiceCard([
                'clocking_id' => $clocking->id,
                'start_time' => now(),
            ]);

            $previousOdometer = $odometerService->getPreviousOdometer($tempCard);
            $validation = $odometerService->validateOdometer($request->arrival_odometer, $previousOdometer);

            if (!$validation['valid']) {
                throw new \Exception($validation['error']);
            }
        }

        DB::beginTransaction();

        try {
            // Check for existing incomplete card for this store and user
            $existingCard = InvoiceCard::where('store_id', $request->store_id)
                ->where('user_id', Auth::id())
                ->where('status', 'not_done')
                ->whereNotNull('end_time')// Card was previously marked as not_done
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

                if ($existingCard->labor_hours > 0) {
                    $existingCard->accumulated_labor_hours =
                        ($existingCard->accumulated_labor_hours ?? 0) + $existingCard->labor_hours;
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
                
                if ($request->arrival_odometer) {
                    $existingCard->arrival_odometer = $request->arrival_odometer;
                }

                if ($request->hasFile('arrival_odometer_image')) {
                    $path = $request->file('arrival_odometer_image')->store('odometer_images', 'public');
                    $existingCard->arrival_odometer_image = $path;
                }

                $existingCard->save();

                // Calculate distance, driving time, and driving payment if using car
                if ($clocking->using_car && $existingCard->arrival_odometer) {
                    $odometerService = new OdometerCalculationService();
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

                if ($request->has('maintenance_request_ids')) {
                    $existingIds = $existingCard->maintenanceRequests->pluck('id')->toArray();
                    $newIds = array_diff($request->maintenance_request_ids, $existingIds);

                    if (!empty($newIds)) {
                        $existingCard->maintenanceRequests()->attach($newIds);
                    }
                }

                DB::commit();

                return [
                    'type' => 'existing',
                    'card' => $existingCard
                ];
            }

            $path = null;
            if ($request->hasFile('arrival_odometer_image')) {
                $path = $request->file('arrival_odometer_image')->store('odometer_images', 'public');
            }

            $card = InvoiceCard::create([
                'clocking_id' => $clocking->id,
                'store_id' => $request->store_id,
                'user_id' => Auth::id(),
                'start_time' => now(),
                'arrival_odometer' => $request->arrival_odometer,
                'arrival_odometer_image' => $path,
                'status' => 'in_progress',
            ]);
            // Calculate distance, driving time, and driving payment if using car

            if ($clocking->using_car && $request->arrival_odometer) {
                $odometerService = new OdometerCalculationService();
                $odometerService->calculateAll($card);
                $card->refresh();
            }
            // Associate maintenance requests

            if ($request->has('maintenance_request_ids')) {
                $card->maintenanceRequests()->attach($request->maintenance_request_ids);
            }

            DB::commit();

            return [
                'type' => 'new',
                'card' => $card
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
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

            return [
                'type' => 'admin',
                'invoiceCards' => $invoiceCards,
                'stores' => $stores,
            ];
        }

        // User view: Show cards for current clocking session
        $clocking = Clocking::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->where('is_clocked_in', true)
            ->first();

        if (!$clocking) {
            throw new \Exception('Please clock in first to create invoice cards.');
        }

        $invoiceCards = InvoiceCard::with(['store', 'materials', 'maintenanceRequests'])
            ->where('clocking_id', $clocking->id)
            ->orderBy('start_time', 'desc')
            ->get();

        $stores = Store::active()->orderBy('store_number')->get();

        return [
            'type' => 'user',
            'invoiceCards' => $invoiceCards,
            'stores' => $stores,
            'clocking' => $clocking,
        ];
    }
    public function show($id)
    {
        $card = InvoiceCard::with(['store', 'materials', 'maintenanceRequests', 'user', 'tasks.maintenanceRequest'])
            ->findOrFail($id);

        // Check authorization
        if ($card->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            throw new \Exception('Unauthorized');
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
        // $nativeRequests = \App\Models\Native\NativeRequest::where('store_id', $card->store_id)
        //     ->where('assigned_to', $targetUserId)
        //     ->whereIn('status', ['pending', 'in_progress', 'received'])
        //     ->with(['urgencyLevel', 'requester', 'assignedTo'])
        //     ->orderBy('urgency_level_id', 'asc')
        //     ->orderBy('request_date', 'asc')
        //     ->get();

        // Combine both types into one collection
        $allRequests = $maintenanceRequests;
        
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

        return [
            'card' => $card,
            'allRequests' => $allRequests,
            'adminEquipmentByRequest' => $adminEquipmentByRequest,
            'linkedRequestIds' => $linkedRequestIds,
        ];
    }
    public function complete($request, $cardId)
    {
        $card = InvoiceCard::findOrFail($cardId);

        // Check authorization
        if ($card->user_id !== Auth::id()) {
            throw new \Exception('Unauthorized');
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
                
                throw new \Exception($errorMessage);
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

            return $card;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete invoice card', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    public function addMaterial($request, $cardId)
    {
        Log::info('Material add request received', [
            'card_id' => $cardId,
            'request_data' => $request->all(),
            'user_id' => Auth::id()
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

            return [
                'success' => true,
                'message' => "Material added successfully{$taskInfo}!",
                'data' => $material
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add material', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Failed to add material: ' . $e->getMessage()
            ];
        }
    }
    public function deleteMaterial($materialId, $userId)
    {
        DB::beginTransaction();
        try {
            $material = InvoiceCardMaterial::findOrFail($materialId);
            $card = $material->invoiceCard;

            // Check authorization
            if ($card->user_id !== $userId) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized',
                    'status_code' => 403
                ];
            }

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

            return [
                'success' => true,
                'message' => 'Material deleted successfully!',
                'status_code' => 200
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete material', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Failed to delete material.',
                'error' => $e->getMessage(),
                'status_code' => 500
            ];
        }
    }
 
  
    public function addTaskToCard(int $cardId, int $taskId)
    {
        $card = InvoiceCard::find($cardId);
        if (!$card) {
            return ['success' => false, 'message' => 'Card not found'];
        }

        $existing = InvoiceCardTask::where('invoice_card_id', $cardId)
            ->where('maintenance_request_id', $taskId)
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'Task already attached'];
        }

        $task = InvoiceCardTask::create([
            'invoice_card_id' => $cardId,
            'maintenance_request_id' => $taskId,
            'task_status' => 'pending'
        ]);

        return ['success' => true, 'task' => $task];
    }

    public function syncClockingWithMaterials($clockingId)
    {
        $clocking = Clocking::find($clockingId);
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

        return $incompleteCards;
    }
 
    
    
}