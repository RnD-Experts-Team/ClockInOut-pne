<?php

namespace App\Http\Controllers\Api\Admin\ModulesInvoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\ModulesInvoice\GenerateInvoiceRequest;
use App\Http\Requests\Api\Admin\ModulesInvoice\SaveInvoiceImageRequest;
use App\Http\Requests\Api\Admin\ModulesInvoice\SendInvoiceEmailRequest;
use App\Services\Api\Admin\ModulesInvoice\InvoiceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    protected $service;

    public function __construct(InvoiceService $service)
    {
        $this->service = $service;
    }
    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->service->index($request->all());

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch invoices',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show($id): JsonResponse
    {
        try {
            $data = $this->service->show($id);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function generateFromCard(GenerateInvoiceRequest $request)
    {
        try {
            $invoice = $this->service->createInvoiceFromCard($request->card_id);

            return response()->json([
                'success' => true,
                'message' => 'Invoice generated successfully!',
                'data' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ]
            ], 201);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice card not found.'
            ], 404);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),  
            ], 500);
        }
    }
    public function download($id)
    {
        try {
            $file = $this->service->downloadInvoiceImage($id);

            return Storage::download($file['path'], $file['name']);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.'
            ], 404);

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
    public function saveImage(SaveInvoiceImageRequest $request, $id)
    {
        try {
            $this->service->saveInvoiceImage($id, $request->image_path);

            return response()->json([
                'success' => true,
                'message' => 'Image saved successfully!',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.'
            ], 404);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }
    public function sendEmail(SendInvoiceEmailRequest $request, $id)
    {
        try {
            $this->service->sendInvoiceEmail(
                $id,
                $request->email,
                $request->template_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Invoice sent successfully to ' . $request->email,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error sending invoice email: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Throwable $e) {
            Log::error('Unexpected error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }
    

  
}