<?php

namespace App\Services;

use App\Models\MaintenanceRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Calculates per-MR labor and purchase costs from InvoiceCard sessions.
 *
 * Labor formula:
 *  - All sessions on the same card are split equally by N (# of MRs on that card).
 *  - For multi-session MRs, the FINAL card carries labor_hours + accumulated_labor_hours,
 *    so only the final card contributes labor hours/cost.
 *  - Intermediate cards contribute only driving_time_payment + mileage_payment (÷ N).
 *  - Edge case: MR never marked completed → use the latest card's labor as the final.
 *
 * Purchase formula:
 *  - SUM(payments.cost WHERE maintenance_request_id = X)          [admin payments]
 *  - SUM(invoice_card_materials.cost WHERE maintenance_request_id = X) [technician materials]
 */
class EquipmentLaborService
{
    // ─── Public API ────────────────────────────────────────────────────────────

    /**
     * Calculate total repair time (hours), labor cost, and purchase cost for one MR.
     *
     * @return array{repair_hours: float, labor_cost: float, purchase_cost: float, total_cost: float}
     */
    public function calculateForMr(MaintenanceRequest $mr): array
    {
        $laborData    = $this->calculateLabor($mr->id);
        $purchaseCost = $this->calculatePurchaseCost($mr->id);

        return [
            'repair_hours'  => $laborData['repair_hours'],
            'labor_cost'    => $laborData['labor_cost'],
            'purchase_cost' => $purchaseCost,
            'total_cost'    => $laborData['labor_cost'] + $purchaseCost,
        ];
    }

    /**
     * Calculate repair hours and labor cost for a given maintenance_request_id.
     *
     * @return array{repair_hours: float, labor_cost: float}
     */
    public function calculateLabor(int $mrId): array
    {
        // Fetch all card sessions this MR appears in, ordered oldest → newest
        $sessions = DB::table('invoice_card_maintenance_requests as pivot')
            ->join('invoice_cards as card', 'card.id', '=', 'pivot.invoice_card_id')
            ->where('pivot.maintenance_request_id', $mrId)
            ->orderBy('card.start_time', 'asc')
            ->select([
                'card.id as card_id',
                'card.status as card_status',
                'card.labor_hours',
                'card.accumulated_labor_hours',
                'card.labor_cost',
                'card.driving_time_payment',
                'card.mileage_payment',
                'pivot.task_status',
                'pivot.completed_at',
                DB::raw('(SELECT COUNT(*) FROM invoice_card_maintenance_requests WHERE invoice_card_id = card.id) as n'),
            ])
            ->get();

        if ($sessions->isEmpty()) {
            return ['repair_hours' => 0.0, 'labor_cost' => 0.0];
        }

        // Determine which session is the "final" one (task_status = completed).
        // If none completed, use the latest session as the fallback final.
        $finalSession = $sessions->firstWhere('task_status', 'completed')
            ?? $sessions->last();

        $repairHours = 0.0;
        $laborCost   = 0.0;

        foreach ($sessions as $session) {
            $n = max(1, (int) $session->n); // guard against 0

            if ($session->card_id === $finalSession->card_id) {
                // Final card: accumulated hours included here already
                $totalHours = (float) $session->labor_hours
                    + (float) $session->accumulated_labor_hours;

                $repairHours += $totalHours / $n;
                $laborCost   += ((float) $session->labor_cost
                    + (float) $session->driving_time_payment
                    + (float) $session->mileage_payment) / $n;
            } else {
                // Intermediate card: skip labor_hours / labor_cost (already baked into final).
                // Only take driving + mileage for this session.
                $laborCost += ((float) $session->driving_time_payment
                    + (float) $session->mileage_payment) / $n;
            }
        }

        return [
            'repair_hours' => round($repairHours, 2),
            'labor_cost'   => round($laborCost, 2),
        ];
    }

    /**
     * Calculate total purchase cost for a given maintenance_request_id.
     * Combines admin payments + technician on-site materials.
     */
    public function calculatePurchaseCost(int $mrId): float
    {
        $adminPayments = (float) DB::table('payments')
            ->where('maintenance_request_id', $mrId)
            ->sum('cost');

        $techMaterials = (float) DB::table('invoice_card_materials')
            ->where('maintenance_request_id', $mrId)
            ->sum('cost');

        // Also include the simple cost entered when an admin marks an MR as done
        $mrCost = (float) (DB::table('maintenance_requests')
            ->where('id', $mrId)
            ->value('costs') ?? 0);

        return round($adminPayments + $techMaterials + $mrCost, 2);
    }

    /**
     * Build an aggregated summary for all MRs belonging to one equipment record.
     *
     * @return array{fix_count: int, total_repair_hours: float, total_labor_cost: float, total_purchase_cost: float, total_cost: float}
     */
    public function summariseForEquipment(int $equipmentId, ?string $fromDate = null, ?string $toDate = null): array
    {
        $mrIds = DB::table('maintenance_requests')
            ->where('equipment_id', $equipmentId)
            ->when($fromDate, fn ($q) => $q->where('request_date', '>=', $fromDate))
            ->when($toDate,   fn ($q) => $q->where('request_date', '<=', $toDate . ' 23:59:59'))
            ->pluck('id');

        $fixCount           = $mrIds->count();
        $totalRepairHours   = 0.0;
        $totalLaborCost     = 0.0;
        $totalPurchaseCost  = 0.0;

        foreach ($mrIds as $mrId) {
            $labor             = $this->calculateLabor($mrId);
            $totalRepairHours  += $labor['repair_hours'];
            $totalLaborCost    += $labor['labor_cost'];
            $totalPurchaseCost += $this->calculatePurchaseCost($mrId);
        }

        return [
            'fix_count'           => $fixCount,
            'total_repair_hours'  => round($totalRepairHours, 2),
            'total_labor_cost'    => round($totalLaborCost, 2),
            'total_purchase_cost' => round($totalPurchaseCost, 2),
            'total_cost'          => round($totalLaborCost + $totalPurchaseCost, 2),
        ];
    }

    /**
     * Build per-MR breakdowns for an equipment's detail/show page.
     * Returns a Collection of arrays, one per MR.
     */
    public function breakdownForEquipment(int $equipmentId): Collection
    {
        $mrs = MaintenanceRequest::with(['store', 'assignedToUser', 'createdByUser'])
            ->where('equipment_id', $equipmentId)
            ->orderBy('request_date', 'desc')
            ->get();

        return $mrs->map(function (MaintenanceRequest $mr) {
            $costs = $this->calculateForMr($mr);

            return [
                'mr'            => $mr,
                'repair_hours'  => $costs['repair_hours'],
                'labor_cost'    => $costs['labor_cost'],
                'purchase_cost' => $costs['purchase_cost'],
                'total_cost'    => $costs['total_cost'],
            ];
        });
    }
}
