<?php
namespace App\Http\Controllers\Api\Admin\ModulesInvoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\ModulesInvoice\StoreUpdateEmailRecipientRequest;
use App\Services\Api\Admin\ModulesInvoice\EmailRecipientService;
use Illuminate\Http\JsonResponse;

class EmailRecipientController extends Controller
{
    protected $service;

    public function __construct(EmailRecipientService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        try {
            $recipients = $this->service->list();

            return response()->json([
                'success' => true,
                'data' => $recipients
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recipients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreUpdateEmailRecipientRequest $request): JsonResponse
    {
        try {
            $recipient = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Email recipient added successfully!',
                'data' => $recipient
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add recipient',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(StoreUpdateEmailRecipientRequest $request, $id): JsonResponse
    {
        try {
            $recipient = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Email recipient updated successfully!',
                'data' => $recipient
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update recipient',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Email recipient deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete recipient',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}