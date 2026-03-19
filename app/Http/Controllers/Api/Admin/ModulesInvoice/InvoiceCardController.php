<?php
namespace App\Http\Controllers\Api\Admin\ModulesInvoice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\ModulesInvoice\AddMaterialRequest;
use App\Http\Requests\Api\Admin\ModulesInvoice\AddRemoveTaskRequest;
use App\Http\Requests\Api\Admin\ModulesInvoice\CompleteInvoiceCardRequest;
use App\Http\Requests\Api\Admin\ModulesInvoice\CompleteTaskRequest;
use App\Http\Requests\Api\Admin\ModulesInvoice\StoreInvoiceCardRequest;
use App\Models\Configuration;
use App\Models\ModulesInvoice\InvoiceCard;
use App\Services\Api\Admin\CognitoFormsService;
use App\Services\Api\Admin\ModulesInvoice\InvoiceCardService;
use App\Services\Api\Admin\ModulesInvoice\MultiTaskCardService;
 use App\Services\Api\TicketCompletionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceCardController extends Controller
{
    protected $service;

    public function __construct(InvoiceCardService $service)
    {
        $this->service = $service;
    }
    public function store(StoreInvoiceCardRequest $request)
    {
        try {
            $result = $this->service->store($request);

            if ($result['type'] === 'existing') {
                return response()->json([
                    'success' => true,
                    'message' => 'Continuing work on previous incomplete card.',
                    'data' => [
                        'card_id' => $result['card']->id
                    ]
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice card created successfully!',
                'data' => [
                    'card_id' => $result['card']->id
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create invoice card', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }
    public function index()
    {
        try {
            $result = $this->service->index();

            // Admin response
            if ($result['type'] === 'admin') {
                return response()->json([
                    'success' => true,
                    'type' => 'admin',
                    'data' => [
                        'invoice_cards' => $result['invoiceCards'],
                        'stores' => $result['stores'],
                    ]
                ], 200);
            }

            // User response
            return response()->json([
                'success' => true,
                'type' => 'user',
                'data' => [
                    'invoice_cards' => $result['invoiceCards'],
                    'stores' => $result['stores'],
                    'clocking' => $result['clocking'],
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }
    public function show($id)
    {
        try {
            $result = $this->service->show($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'card' => $result['card'],
                    'requests' => $result['allRequests'],
                    'admin_equipment' => $result['adminEquipmentByRequest'],
                    'linked_request_ids' => $result['linkedRequestIds'],
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found.'
            ], 404);

        } catch (\Exception $e) {
            if ($e->getMessage() === 'Unauthorized') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }
    public function complete(CompleteInvoiceCardRequest $request, $cardId)
    {
        try {
            $card = $this->service->complete($request, $cardId);

            return response()->json([
                'success' => true,
                'message' => 'Invoice card completed successfully!',
                'data' => [
                    'card_id' => $card->id,
                    'status' => $card->status,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found.'
            ], 404);

        } catch (\Exception $e) {
            if ($e->getMessage() === 'Unauthorized') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }
    public function addMaterial(AddMaterialRequest $request, $cardId): JsonResponse
    {
        try {
            $response = $this->service->addMaterial($request, $cardId);

            if ($response['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $response['message'],
                    'data' => $response['data']
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => $response['message']
            ], 500);
        } catch (\Exception $e) {
             Log::error('Failed to add material', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding material: ' . $e->getMessage()
            ], 500);
        }
    }
    public function deleteMaterial($materialId): JsonResponse
    {
        $response = $this->service->deleteMaterial($materialId, auth()->id());

        return response()->json([
            'success' => $response['success'],
            'message' => $response['message'],
            'error' => $response['error'] ?? null
        ], $response['status_code']);
    }
    public function addTask(AddRemoveTaskRequest $request, $cardId): JsonResponse
    {
        try {
            $card = InvoiceCard::findOrFail($cardId);

            // Check if the card belongs to the authenticated user
            if ($card->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $service = new MultiTaskCardService();
            $result = $service->addTaskToCard($cardId, $request->maintenance_request_id);

            // If the result is unsuccessful, return the error message
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 422);
            }

            // If successful, return the task details
            return response()->json([
                'success' => true,
                'task' => $result['task']
            ], 200);

        } catch (\Exception $e) {
            // Handle any unexpected exceptions and return a JSON error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    public function removeTask(AddRemoveTaskRequest $request, $cardId): JsonResponse
    {
        try {
            // Find the card by its ID
            $card = InvoiceCard::findOrFail($cardId);

            // Check if the authenticated user is the owner of the card
            if ($card->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Call the service to remove the task from the card
            $service = new MultiTaskCardService();
            $result = $service->removeTaskFromCard($cardId, $request->maintenance_request_id);

            // If the result is unsuccessful, return the error message
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 422);
            }

            // If successful, return a success response
            return response()->json([
                'success' => true
            ], 200);

        } catch (\Exception $e) {
            // Handle any unexpected exceptions
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    public function completeTask(CompleteTaskRequest $request, $cardId): JsonResponse
    {
        try {
            // Find the card by its ID
            $card = InvoiceCard::findOrFail($cardId);

            // Check if the authenticated user is the owner of the card
            if ($card->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Call the service to mark the task as complete
            $service = new MultiTaskCardService();
            $result = $service->markTaskComplete($cardId, $request->maintenance_request_id);

            // If the result is unsuccessful, return the error message
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 422);
            }

            // Only auto-finalize if this is NOT a single task completion request
            $shouldAutoFinalize = !$request->boolean('complete_single_task', false);

            // If all tasks complete and auto-finalize is enabled, finalize the card
            if ($result['all_tasks_complete'] && $shouldAutoFinalize) {
                DB::beginTransaction();
                try {
                    // Update card to completed status
                    $card->end_time = now();
                    $card->status = 'completed';
                    $card->save();

                    // Recalculate costs
                    $card->calculateLaborCost();
                    $card->calculateMaterialsCost();
                    $card->calculateMileagePayment(Configuration::getGasPaymentRate());
                    $card->calculateTotalCost();

                    // Process ticket completion actions
                    $ticketService = new TicketCompletionService(new CognitoFormsService());
                    $ticketService->processCardCompletion($card);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to finalize card after completing tasks', ['error' => $e->getMessage()]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to finalize card'
                    ], 500);
                }
            }

            // Return a success response with relevant data
            return response()->json([
                'success' => true,
                'all_tasks_complete' => $result['all_tasks_complete'],
                'task' => $result['task'],
                'card_finalized' => $result['all_tasks_complete'] && $shouldAutoFinalize
            ], 200);

        } catch (\Exception $e) {
            // Handle any unexpected exceptions and return a JSON error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    public function syncAllClockingRecords(): JsonResponse
    {
        try {
            // Only allow admin access
            if (Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $clockingIds = InvoiceCard::distinct()->pluck('clocking_id')->filter();
            $syncedCount = 0;

            foreach ($clockingIds as $clockingId) {
                $this->service->syncClockingWithMaterials($clockingId);  // assuming this method is defined in the controller
                $syncedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Synced {$syncedCount} clocking records with their materials",
                'synced_count' => $syncedCount
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions and return the error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getIncompleteCards(InvoiceCardService $invoiceCardService): JsonResponse
    {
        try {
             $incompleteCards = $invoiceCardService->getIncompleteCards();

            return response()->json([
                'success' => true,
                'incomplete_cards' => $incompleteCards
            ]);
        } catch (\Exception $e) {
             return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    
   
}
