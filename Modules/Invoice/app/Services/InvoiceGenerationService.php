<?php

namespace Modules\Invoice\Services;

use App\Models\Payment;
use App\Models\Store;
use Modules\Invoice\Models\InvoiceCard;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InvoiceGenerationService
{
    /**
     * Aggregate invoice data for a store within a date range
     *
     * @param int $storeId
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public function aggregateInvoiceData(int $storeId, string $dateFrom, string $dateTo): array
    {
        $store = Store::findOrFail($storeId);
        
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();
        
        // Get all completed invoice cards for this store within date range
        $invoiceCards = InvoiceCard::with(['user', 'materials', 'maintenanceRequests'])
            ->where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereBetween('start_time', [$from, $to])
            ->get();
        
        // Get all payments with equipment items for this store within date range
        $paymentsWithEquipment = Payment::with(['company', 'equipmentItems'])
            ->where('store_id', $storeId)
            ->whereHas('equipmentItems')
            ->whereBetween('date', [$from, $to])
            ->get();
        
        // Calculate labor costs by technician
        $laborByTechnician = [];
        foreach ($invoiceCards as $card) {
            $technicianName = $card->user->name;
            
            if (!isset($laborByTechnician[$technicianName])) {
                $laborByTechnician[$technicianName] = [
                    'name' => $technicianName,
                    'hourly_rate' => $card->user->hourly_pay,
                    'total_hours' => 0,
                    'total_cost' => 0,
                ];
            }
            
            $laborByTechnician[$technicianName]['total_hours'] += $card->labor_hours ?? 0;
            $laborByTechnician[$technicianName]['total_cost'] += $card->labor_cost ?? 0;
        }
        
        // Aggregate materials
        $materials = [];
        foreach ($invoiceCards as $card) {
            foreach ($card->materials as $material) {
                $materials[] = [
                    'item_name' => $material->item_name,
                    'cost' => $material->cost,
                    'receipt_photos' => $material->receipt_photos,
                ];
            }
        }
        
        // Aggregate equipment items from payments
        $equipment = [];
        $equipmentTotal = 0;
        
        foreach ($paymentsWithEquipment as $payment) {
            foreach ($payment->equipmentItems as $item) {
                $equipment[] = [
                    'item_name' => $item->item_name,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'total_cost' => $item->total_cost,
                    'company' => $payment->company->name ?? 'N/A',
                    'payment_date' => $payment->date->format('M d, Y'),
                    'payment_id' => $payment->id,
                ];
                $equipmentTotal += $item->total_cost;
            }
        }
        
        // Calculate mileage
        $totalMiles = $invoiceCards->sum('total_miles');
        $totalMileagePayment = $invoiceCards->sum('mileage_payment');
        
        // Calculate totals
        $laborTotal = $invoiceCards->sum('labor_cost');
        $materialsTotal = $invoiceCards->sum('materials_cost');
        // $equipmentTotal already calculated above
        $mileageTotal = $totalMileagePayment;
        
        $subtotal = $laborTotal + $materialsTotal + $equipmentTotal + $mileageTotal;
        
        // Calculate tax (5% - you can make this configurable)
        $taxRate = 0.05;
        $tax = $subtotal * $taxRate;
        
        $grandTotal = $subtotal + $tax;
        
        return [
            'store' => [
                'id' => $store->id,
                'number' => $store->store_number,
                'name' => $store->name,
                'address' => $store->address,
            ],
            'period' => [
                'from' => $from->format('M d, Y'),
                'to' => $to->format('M d, Y'),
                'days' => $from->diffInDays($to) + 1,
            ],
            'labor' => [
                'by_technician' => array_values($laborByTechnician),
                'total' => $laborTotal,
            ],
            'materials' => [
                'items' => $materials,
                'total' => $materialsTotal,
            ],
            'equipment' => [
                'items' => $equipment,
                'total' => $equipmentTotal,
            ],
            'mileage' => [
                'total_miles' => $totalMiles,
                'payment' => $mileageTotal,
            ],
            'totals' => [
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate * 100,
                'tax' => $tax,
                'grand_total' => $grandTotal,
            ],
            'invoice_cards_count' => $invoiceCards->count(),
            'generated_at' => now()->format('M d, Y H:i:s'),
        ];
    }
    
    /**
     * Generate invoice number
     *
     * @param int $storeId
     * @param string $date
     * @return string
     */
    public function generateInvoiceNumber(int $storeId, string $date): string
    {
        $store = Store::findOrFail($storeId);
        $dateObj = Carbon::parse($date);
        
        // Format: INT-[StoreNumber]-[YYYYMM]
        return sprintf(
            'INT-%s-%s',
            str_pad($store->store_number, 3, '0', STR_PAD_LEFT),
            $dateObj->format('Ym')
        );
    }
    
    /**
     * Generate invoice image (placeholder - will be implemented with Browsershot)
     *
     * @param array $invoiceData
     * @return array
     */
    public function generateInvoiceImage(array $invoiceData): array
    {
        // TODO: Implement with Browsershot/Intervention Image
        // For now, return placeholder paths
        
        $invoiceNumber = $this->generateInvoiceNumber(
            $invoiceData['store']['id'],
            now()->toDateString()
        );
        
        $filename = "Invoice_{$invoiceData['store']['number']}_{$invoiceData['period']['from']}.png";
        
        Log::info("Invoice image generation requested", [
            'invoice_number' => $invoiceNumber,
            'filename' => $filename,
        ]);
        
        return [
            'invoice_number' => $invoiceNumber,
            'png_path' => "invoices/{$filename}",
            'pdf_path' => str_replace('.png', '.pdf', "invoices/{$filename}"),
        ];
    }
}
