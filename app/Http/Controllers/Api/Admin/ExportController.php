<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\ClockingExport;
use App\Http\Controllers\Controller;
use App\Services\Api\Admin\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function __construct(
        private ExportService $service
    ) {}

  public function exportToExcel(Request $request, $startDateParam = null, $endDateParam = null)
    {
        try {

            $startDate = $startDateParam ?? $request->query('start_date');
            $endDate = $endDateParam ?? $request->query('end_date');

            $result = $this->service->exportData($startDate, $endDate);

            $data = $result['data'] ?? [];

            return Excel::download(
                new ClockingExport($data),
                'clocking.xlsx'
            );

        } catch (Throwable $e) {

            Log::error('Clocking export Excel error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to export clocking data.',
            ], 500);
        }
    }
}