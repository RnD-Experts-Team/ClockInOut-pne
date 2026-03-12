<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreNativeRequestRequest;
use App\Services\Api\Admin\NativeRequestService;
use Illuminate\Http\Request;
use App\Models\Native\NativeRequest;

class NativeRequestController extends Controller
{
    protected $nativeRequestService;

    public function __construct(NativeRequestService $nativeRequestService)
    {
        $this->nativeRequestService = $nativeRequestService;
    }

    
    public function store(StoreNativeRequestRequest $request)
    {
        try {

            $nativeRequest = $this->nativeRequestService->store($request);

            return response()->json([
                'status' => true,
                'message' => 'Ticket Submitted Successfully!',
                'ticket_id' => $nativeRequest->id
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }

    /**
     * List tickets
     */
    public function index(Request $request)
    {
        try {

            $data = $this->nativeRequestService->index($request);

            return response()->json([
                'status' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }

    /**
     * Show ticket
     */
    public function show(NativeRequest $request)
    {
        try {

            $data = $this->nativeRequestService->show($request);

            return response()->json([
                'status' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }
}