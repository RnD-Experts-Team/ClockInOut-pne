<?php

namespace App\Http\Controllers\Api\Admin;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\CompanyStoreRequest;
use App\Http\Requests\Api\Admin\CompanyUpdateRequest;
use App\Services\Api\Admin\CompanyService;

class CompanyController extends Controller
{

    public function __construct(private CompanyService $companyService) {}


    public function index(Request $request)
    {
        try {

            $data = $this->companyService->index($request);

            return response()->json([
                'success'=>true,
                'data'=>$data
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success'=>false,
                'message'=>'Failed to fetch companies',
                'error'=>$e->getMessage()
            ],500);
        }
    }


    public function store(CompanyStoreRequest $request)
    {
        try {

            $company = $this->companyService->store(
                $request->validated()
            );

            return response()->json([
                'success'=>true,
                'message'=>'Company created successfully',
                'data'=>$company
            ],201);

        } catch (\Throwable $e) {

            return response()->json([
                'success'=>false,
                'message'=>'Failed to create company',
                'error'=>$e->getMessage()
            ],500);
        }
    }


    public function show(Company $company)
    {
        try {

            $data = $this->companyService->show($company);

            return response()->json([
                'success'=>true,
                'data'=>$data
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success'=>false,
                'message'=>'Failed to fetch company',
                'error'=>$e->getMessage()
            ],500);
        }
    }


    public function update(CompanyUpdateRequest $request, Company $company)
    {
        try {

            $company = $this->companyService->update(
                $company,
                $request->validated()
            );

            return response()->json([
                'success'=>true,
                'message'=>'Company updated successfully',
                'data'=>$company
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success'=>false,
                'message'=>'Failed to update company',
                'error'=>$e->getMessage()
            ],500);
        }
    }


    public function destroy(Company $company)
    {
        try {

            $this->companyService->destroy($company);

            return response()->json([
                'success'=>true,
                'message'=>'Company deleted successfully'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage()
            ],400);
        }
    }
    public function export(Request $request)
    {
        try {

            $data = $this->companyService->export($request);

            return response()->json([
                'success' => true,
                'message' => 'Companies exported successfully',
                'data' => $data
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Export failed',
                'error' => $e->getMessage()
            ], 500);

        }
    }

}