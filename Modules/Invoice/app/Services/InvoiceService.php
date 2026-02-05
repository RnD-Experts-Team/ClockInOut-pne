<?php

namespace Modules\Invoice\Services;

use Modules\Invoice\Models\Invoice;
use Modules\Invoice\Models\InvoiceCard;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Create invoice from invoice card
     */
    public function createInvoiceFromCard(int $cardId): Invoice
    {
        $card = InvoiceCard::with(['store', 'user', 'materials', 'clocking', 'maintenanceRequests'])->findOrFail($cardId);
        
        // Calculate equipment cost for this store in the same period
        $equipmentCost = $this->calculateEquipmentCost(
            $card,
            $card->start_time,
            $card->end_time ?? now()
        );
        
        // Get mileage data for this specific card
        // For multi-store visits, use the card's allocated miles, not the entire session
        $cardTotalMiles = $card->total_miles ?? 0; // This includes allocated return miles
        $cardDistanceMiles = $card->calculated_miles ?? 0; // Distance driven to this store
        
        // Calculate totals
        $subtotal = $card->labor_cost + $card->materials_cost + $equipmentCost + $card->mileage_payment + ($card->driving_time_payment ?? 0);
        $taxRate = 0; // 5%
        $taxAmount = $subtotal * ($taxRate / 100);
        $grandTotal = $subtotal + $taxAmount;
        
        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber($card->store_id),
            'invoice_card_id' => $card->id,
            'store_id' => $card->store_id,
            'user_id' => $card->user_id,
            'period_start' => Carbon::parse($card->start_time)->startOfDay(),
            'period_end' => Carbon::parse($card->end_time ?? now())->endOfDay(),
            'labor_hours' => ($card->labor_hours ?? 0) + ($card->accumulated_labor_hours ?? 0),
            'labor_cost' => $card->labor_cost ?? 0,
            'materials_cost' => $card->materials_cost ?? 0,
            'equipment_cost' => $equipmentCost,
            'total_miles' => $cardTotalMiles, // Total miles allocated to this card (includes return miles)
            'total_distance_miles' => $cardDistanceMiles, // Distance driven to this specific store
            'driving_time_hours' => $card->total_driving_time_hours ?? $card->driving_time_hours ?? 0,
            'driving_time_payment' => $card->driving_time_payment ?? 0,
            'mileage_cost' => $card->mileage_payment ?? 0,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'status' => 'draft',
        ]);
        
        return $invoice;
    }
    
    /**
     * Calculate equipment cost for store in period (filtered by linked maintenance requests)
     */
    private function calculateEquipmentCost(InvoiceCard $card, $startDate, $endDate): float
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // Get maintenance request IDs linked to this card
        $maintenanceRequestIds = $card->maintenanceRequests->pluck('id')->toArray();
        
        $equipmentQuery = DB::table('payment_equipment_items')
            ->join('payments', 'payment_equipment_items.payment_id', '=', 'payments.id')
            ->where('payments.store_id', $card->store_id)
            ->where('payments.is_admin_equipment', true); // Only admin equipment
        
        // If there are linked maintenance requests, filter by those OR date range
        // This ensures equipment purchased for specific tasks shows up, plus general equipment in the period
        if (!empty($maintenanceRequestIds)) {
            $equipmentQuery->where(function($query) use ($maintenanceRequestIds, $start, $end) {
                $query->whereIn('payments.maintenance_request_id', $maintenanceRequestIds)
                      ->orWhereBetween('payments.date', [$start, $end]);
            });
        } else {
            // If no linked requests, use date range filter
            $equipmentQuery->whereBetween('payments.date', [$start, $end]);
        }
        
        $equipmentTotal = $equipmentQuery->sum('payment_equipment_items.total_cost');
        
        return (float) $equipmentTotal;
    }
    
    /**
     * Generate unique invoice number
     */
    public function generateInvoiceNumber(int $storeId): string
    {
        $store = \App\Models\Store::findOrFail($storeId);
        $storeNumber = str_pad($store->store_number ?? $storeId, 4, '0', STR_PAD_LEFT);
        
        // Get last invoice number for this month
        $lastInvoice = Invoice::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;
        
        // Format: INT-XXXX (simple sequential)
        return 'INT-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Recalculate invoice totals
     */
    public function recalculateTotals(Invoice $invoice): Invoice
    {
        $subtotal = $invoice->labor_cost + $invoice->materials_cost + $invoice->equipment_cost + $invoice->mileage_cost + $invoice->driving_time_payment;
        $taxAmount = $subtotal * ($invoice->tax_rate / 100);
        $grandTotal = $subtotal + $taxAmount;
        
        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
        ]);
        
        return $invoice->fresh();
    }
    
    /**
     * Mark invoice as sent
     */
    public function markAsSent(Invoice $invoice, string $email): Invoice
    {
        $invoice->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_to_email' => $email,
        ]);
        
        return $invoice->fresh();
    }
    
    /**
     * Get invoice data for display
     */
    public function getInvoiceData(Invoice $invoice): array
    {
        $card = $invoice->invoiceCard()->with(['materials', 'user', 'maintenanceRequests'])->first();
        
        // Get maintenance request IDs linked to this invoice card
        $maintenanceRequestIds = $card && $card->maintenanceRequests 
            ? $card->maintenanceRequests->pluck('id')->toArray() 
            : [];
        
        // Get equipment items - filter by linked maintenance requests if any
        $equipmentQuery = DB::table('payment_equipment_items')
            ->join('payments', 'payment_equipment_items.payment_id', '=', 'payments.id')
            ->leftJoin('companies', 'payments.company_id', '=', 'companies.id')
            ->where('payments.store_id', $invoice->store_id)
            ->where('payments.is_admin_equipment', true); // Only admin equipment purchases
        
        // If there are linked maintenance requests, filter by those (regardless of date)
        // This ensures equipment purchased for specific tasks shows up in the invoice
        if (!empty($maintenanceRequestIds)) {
            $equipmentQuery->where(function($query) use ($maintenanceRequestIds, $invoice) {
                $query->whereIn('payments.maintenance_request_id', $maintenanceRequestIds)
                      ->orWhereBetween('payments.date', [$invoice->period_start, $invoice->period_end]);
            });
        } else {
            // If no linked requests, use date range filter
            $equipmentQuery->whereBetween('payments.date', [$invoice->period_start, $invoice->period_end]);
        }
        
        $equipmentItems = $equipmentQuery->select(
                'payment_equipment_items.*',
                'companies.name as company_name',
                'payments.date as payment_date',
                'payments.maintenance_request_id',
                'payments.id as payment_id'
            )
            ->get();
        
        // Get materials from card if exists, otherwise empty collection
        $materials = $card && $card->materials ? $card->materials : collect();
        
        return [
            'invoice' => $invoice,
            'card' => $card,
            'store' => $invoice->store,
            'technician' => $invoice->user,
            'materials' => $materials,
            'equipment_items' => $equipmentItems,
        ];
    }
}
