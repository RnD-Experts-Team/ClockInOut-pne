<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\Store;
use App\Services\EquipmentLaborService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EquipmentTrackerController extends Controller
{
    public function __construct(protected EquipmentLaborService $laborService)
    {
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    /**
     * Display the equipment tracker index with filters and summary cards.
     */
    public function index(Request $request): View
    {
        $storeId  = $request->input('store');
        $type     = $request->input('type');
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');
        $showAll  = $request->boolean('show_inactive');

        $query = Equipment::with('store')
            ->when(!$showAll, fn ($q) => $q->where('is_active', true))
            ->when($storeId === 'global', fn ($q) => $q->whereNull('store_id'))
            ->when($storeId && $storeId !== 'global', fn ($q) => $q->whereHas('maintenanceRequests', fn ($mq) => $mq->where('store_id', $storeId)))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByRaw("ISNULL(store_id), name");

        $equipment = $query->get();

        // Build MR count + date-filtered counts per equipment
        $mrQuery = DB::table('maintenance_requests')
            ->selectRaw('equipment_id, COUNT(*) as fix_count')
            ->when($fromDate, fn ($q) => $q->where('request_date', '>=', $fromDate))
            ->when($toDate,   fn ($q) => $q->where('request_date', '<=', $toDate . ' 23:59:59'))
            ->whereIn('equipment_id', $equipment->pluck('id'))
            ->groupBy('equipment_id');

        $mrCounts = $mrQuery->pluck('fix_count', 'equipment_id');

        // For global equipment (store_id = null), find which stores have MRs for it
        $globalEquipmentIds = $equipment->whereNull('store_id')->pluck('id');
        $mrStoreNames = DB::table('maintenance_requests')
            ->join('stores', 'stores.id', '=', 'maintenance_requests.store_id')
            ->selectRaw('maintenance_requests.equipment_id, GROUP_CONCAT(DISTINCT stores.name ORDER BY stores.name SEPARATOR ", ") as store_names')
            ->whereIn('maintenance_requests.equipment_id', $globalEquipmentIds)
            ->groupBy('maintenance_requests.equipment_id')
            ->pluck('store_names', 'equipment_id');

        // Attach fix counts and pre-computed totals to each equipment record
        foreach ($equipment as $item) {
            $item->fix_count = $mrCounts[$item->id] ?? 0;
            $item->mr_store_names = $mrStoreNames[$item->id] ?? null;
            $summary = $this->laborService->summariseForEquipment($item->id, $fromDate, $toDate);
            $item->total_repair_hours  = $summary['total_repair_hours'];
            $item->total_labor_cost    = $summary['total_labor_cost'];
            $item->total_purchase_cost = $summary['total_purchase_cost'];
            $item->total_cost          = $summary['total_cost'];
        }

        // Summary totals
        $totals = [
            'equipment_count'  => $equipment->count(),
            'fix_count'        => $equipment->sum('fix_count'),
            'labor_cost'       => $equipment->sum('total_labor_cost'),
            'purchase_cost'    => $equipment->sum('total_purchase_cost'),
            'total_cost'       => $equipment->sum('total_cost'),
        ];

        $stores = Store::active()->orderBy('store_number')->get();
        $types  = Equipment::whereNotNull('type')->distinct()->pluck('type')->sort()->values();

        return view('admin.equipment.index', compact(
            'equipment', 'totals', 'stores', 'types',
            'storeId', 'type', 'fromDate', 'toDate', 'showAll'
        ));
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    /**
     * Equipment detail page — MR timeline with per-ticket costs.
     */
    public function show(int $id): View
    {
        $item = Equipment::with('store')->findOrFail($id);

        $breakdown = $this->laborService->breakdownForEquipment($id);

        $summary = [
            'fix_count'           => $breakdown->count(),
            'total_repair_hours'  => $breakdown->sum('repair_hours'),
            'total_labor_cost'    => $breakdown->sum('labor_cost'),
            'total_purchase_cost' => $breakdown->sum('purchase_cost'),
            'total_cost'          => $breakdown->sum('total_cost'),
        ];

        return view('admin.equipment.show', compact('item', 'breakdown', 'summary'));
    }

    // ─── Create / Store ───────────────────────────────────────────────────────

    public function create(): View
    {
        $stores = Store::active()->orderBy('store_number')->get();
        return view('admin.equipment.create', compact('stores'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'store_id'      => 'nullable|exists:stores,id',
            'type'          => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'model'         => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:2000',
            'is_active'     => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Equipment::create($data);

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Equipment record created successfully.');
    }

    // ─── Edit / Update ────────────────────────────────────────────────────────

    public function edit(int $id): View
    {
        $item   = Equipment::findOrFail($id);
        $stores = Store::active()->orderBy('store_number')->get();
        return view('admin.equipment.edit', compact('item', 'stores'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = Equipment::findOrFail($id);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'store_id'      => 'nullable|exists:stores,id',
            'type'          => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'model'         => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:2000',
            'is_active'     => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $item->update($data);

        return redirect()->route('admin.equipment.show', $item->id)
            ->with('success', 'Equipment record updated.');
    }

    // ─── Deactivate ───────────────────────────────────────────────────────────

    public function destroy(int $id): RedirectResponse
    {
        $item = Equipment::findOrFail($id);
        $item->update(['is_active' => false]);

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Equipment record deactivated.');
    }

    // ─── Re-assign MR ─────────────────────────────────────────────────────────

    /**
     * Re-assign a maintenance request to a different equipment record.
     * Called from the equipment detail page.
     */
    public function reassignMr(Request $request, int $mrId): RedirectResponse
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
        ]);

        $mr = MaintenanceRequest::findOrFail($mrId);
        $mr->update(['equipment_id' => $request->equipment_id]);

        return back()->with('success', 'Maintenance request re-assigned.');
    }

    // ─── Export ───────────────────────────────────────────────────────────────

    /**
     * CSV export — tracker index (respects active filters).
     */
    public function export(Request $request): StreamedResponse
    {
        $storeId  = $request->input('store');
        $type     = $request->input('type');
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');
        $showAll  = $request->boolean('show_inactive');

        $query = Equipment::with('store')
            ->when(!$showAll, fn ($q) => $q->where('is_active', true))
            ->when($storeId === 'global', fn ($q) => $q->whereNull('store_id'))
            ->when($storeId && $storeId !== 'global', fn ($q) => $q->whereHas('maintenanceRequests', fn ($mq) => $mq->where('store_id', $storeId)))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByRaw("ISNULL(store_id), name");

        $equipment = $query->get();

        // For global equipment (store_id = null), find which stores have MRs for it
        $globalEquipmentIds = $equipment->whereNull('store_id')->pluck('id');
        $mrStoreNames = DB::table('maintenance_requests')
            ->join('stores', 'stores.id', '=', 'maintenance_requests.store_id')
            ->selectRaw('maintenance_requests.equipment_id, GROUP_CONCAT(DISTINCT stores.name ORDER BY stores.name SEPARATOR ", ") as store_names')
            ->whereIn('maintenance_requests.equipment_id', $globalEquipmentIds)
            ->groupBy('maintenance_requests.equipment_id')
            ->pluck('store_names', 'equipment_id');

        foreach ($equipment as $item) {
            $summary = $this->laborService->summariseForEquipment($item->id, $fromDate, $toDate);
            $item->fix_count           = $summary['fix_count'];
            $item->total_repair_hours  = $summary['total_repair_hours'];
            $item->total_labor_cost    = $summary['total_labor_cost'];
            $item->total_purchase_cost = $summary['total_purchase_cost'];
            $item->total_cost          = $summary['total_cost'];
            $item->mr_store_names      = $mrStoreNames[$item->id] ?? null;
        }

        $filename = 'equipment-tracker-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($equipment) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Equipment Name', 'Type', 'Store', '# of Fixes',
                'Total Repair Time (hrs)', 'Labor Cost', 'Purchase Cost', 'Total Cost', 'Active',
            ]);
            foreach ($equipment as $item) {
                fputcsv($handle, [
                    $item->name,
                    $item->type,
                    $item->store?->name ?? $item->mr_store_names ?? 'Global',
                    $item->fix_count,
                    $item->total_repair_hours,
                    number_format($item->total_labor_cost, 2),
                    number_format($item->total_purchase_cost, 2),
                    number_format($item->total_cost, 2),
                    $item->is_active ? 'Yes' : 'No',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * CSV export — per-equipment MR detail.
     */
    public function exportDetail(int $id): StreamedResponse
    {
        $item      = Equipment::with('store')->findOrFail($id);
        $breakdown = $this->laborService->breakdownForEquipment($id);
        $filename  = 'equipment-detail-' . $item->id . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($breakdown) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Ticket #', 'Date', 'Store', 'Description', 'How Fixed',
                'Status', 'Repair Time (hrs)', 'Labor Cost', 'Purchase Cost', 'Total Cost',
            ]);
            foreach ($breakdown as $row) {
                $mr = $row['mr'];
                fputcsv($handle, [
                    $mr->entry_number ?? $mr->id,
                    $mr->request_date?->format('Y-m-d'),
                    $mr->store?->name,
                    $mr->description_of_issue,
                    $mr->how_we_fixed_it,
                    $mr->status,
                    $row['repair_hours'],
                    number_format($row['labor_cost'], 2),
                    number_format($row['purchase_cost'], 2),
                    number_format($row['total_cost'], 2),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
