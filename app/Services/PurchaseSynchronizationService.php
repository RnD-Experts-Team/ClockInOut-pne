<?php

namespace App\Services;

use App\Models\Clocking;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PurchaseSynchronizationService
{
    /**
     * Create a Payment from a Clocking session
     */
    public function syncFromClockingToPayment(Clocking $clocking): ?Payment
    {
        if (($clocking->purchase_cost ?? 0) <= 0) {
            return null;
        }

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'store_id' => $clocking->store_id ?? null,
                'date' => now(),
                'company_id' => null,
                'cost' => $clocking->purchase_cost,
                'notes' => 'Imported from clocking session ' . $clocking->id,
                'paid' => false,
                'is_admin_equipment' => false,
                'payment_method' => 'clocking',
                'maintenance_type' => 'purchase',
                'clocking_id' => $clocking->id,
                'source_system' => 'clocking_system',
                'sync_status' => 'synced'
            ]);

            // Mark clocking as having purchases
            $clocking->bought_something = true;
            $clocking->save();

            DB::commit();

            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync clocking to payment: ' . $e->getMessage(), ['clocking_id' => $clocking->id]);
            return null;
        }
    }

    /**
     * Sync from Payment back to a Clocking record
     */
    public function syncFromPaymentToClocking(Payment $payment): bool
    {
        if (!$payment->clocking_id) {
            return false;
        }

        $clocking = Clocking::find($payment->clocking_id);
        if (!$clocking) {
            return false;
        }

        DB::beginTransaction();
        try {
            // Update purchase cost as sum of payments linked to the clocking
            $this->updateClockingPurchaseCost($clocking->id);

            // Mark as synced
            $payment->sync_status = 'synced';
            $payment->save();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync payment to clocking: ' . $e->getMessage(), ['payment_id' => $payment->id]);
            $payment->sync_status = 'failed';
            $payment->save();
            return false;
        }
    }

    /**
     * Sum payments for a clocking and update the clocking.purchase_cost
     */
    public function updateClockingPurchaseCost(int $clockingId): bool
    {
        $total = Payment::where('clocking_id', $clockingId)->sum('cost');

        $clocking = Clocking::find($clockingId);
        if (!$clocking) return false;

        $clocking->purchase_cost = $total;
        $clocking->bought_something = $total > 0;
        $clocking->save();

        return true;
    }

    /**
     * Get unified purchase view across both systems for a user
     */
    public function getUnifiedPurchaseView(int $userId, int $clockingId = null)
    {
        // Payments created directly in invoice system
        $invoicePayments = Payment::where('source_system', 'invoice_system')
            ->where('user_id', $userId)
            ->get();

        // Payments created from clocking system
        $clockingPayments = Payment::where('source_system', 'clocking_system')
            ->where('user_id', $userId)
            ->when($clockingId, fn($q) => $q->where('clocking_id', $clockingId))
            ->get();

        $merged = $invoicePayments->concat($clockingPayments)
            ->unique(function ($item) {
                return ($item->source_system ?? '') . ':' . ($item->id ?? uniqid());
            })
            ->sortByDesc('date')
            ->values();

        return $merged;
    }

    /**
     * Simple conflict handler - prefer newest payment, mark sync_status appropriately
     */
    public function handleConflict(Payment $payment, Clocking $clocking): bool
    {
        // If differences exist between payment cost and clocking purchase_cost, choose the most recent
        if (abs(($payment->cost ?? 0) - ($clocking->purchase_cost ?? 0)) > 0.009) {
            // Choose payment as source of truth and update clocking
            $clocking->purchase_cost = $payment->cost;
            $clocking->bought_something = ($payment->cost ?? 0) > 0;
            $clocking->save();

            $payment->sync_status = 'synced';
            $payment->save();

            return true;
        }

        // No conflict
        $payment->sync_status = 'synced';
        $payment->save();
        return true;
    }
}
