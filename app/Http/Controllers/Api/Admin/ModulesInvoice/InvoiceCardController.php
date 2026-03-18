<?php
namespace App\Http\Controllers\Api\Admin\ModulesInvoice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\ModulesInvoice\CompleteInvoiceCardRequest;
use App\Http\Requests\Api\Admin\ModulesInvoice\StoreInvoiceCardRequest;
use App\Services\Api\Admin\ModulesInvoice\InvoiceCardService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
}
