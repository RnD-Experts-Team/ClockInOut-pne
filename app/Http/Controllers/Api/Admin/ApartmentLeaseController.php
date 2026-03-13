<?php

namespace App\Http\Controllers\Api\Admin;
use App\Services\Api\Admin\ApartmentLeaseService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\ApartmentLeaseStoreRequest;
use App\Models\ApartmentLease;
use Illuminate\Http\Request;

class ApartmentLeaseController extends Controller
{

    public function __construct(private ApartmentLeaseService $service)
    {
    }

     public function export(Request $request)
    {
        $result = $this->service->exportLeases($request);

        if (!$result['success']) {

            return response()->json([
                'success' => false,
                'message' => $result['message']
            ],500);
        }

        return response()->json([
            'success' => true,
            'filename' => $result['filename'],
            'csv' => $result['data']
        ]);
    }



    public function list()
    {
        
        $leases = $this->service->listLeases();
        return response()->json([
            'success' => true,
            'data' => $leases
        ]);
    }
     public function store(ApartmentLeaseStoreRequest $request)
    {
        try {

            $this->service->createLease(
                $request->validated()
            );

            return redirect()
                ->route('admin.apartment-leases.index')
                ->with('success', 'Apartment lease created successfully.');

        } catch (\Exception $e) {

            return back()
                ->withErrors([
                    'error' => 'Failed to create apartment lease: ' . $e->getMessage()
                ]);
        }
    }


    public function show(ApartmentLease $apartmentLease)
    {
        $apartmentLease->load(['store','renewalCreatedBy']);

        return view(
            'admin.apartment-leases.show',
            compact('apartmentLease')
        );
    }
    public function index(Request $request)
    {
        $data = $this->service->getLeases($request);

        return view('admin.apartment-leases.index', [
            'leases' => $data['leases'],
            'stats' => $data['stats'],
            'stores' => $data['stores']
        ]);
    }

}