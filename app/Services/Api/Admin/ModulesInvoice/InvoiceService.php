<?php

namespace App\Services\Api\Admin\ModulesInvoice;

use App\Mail\InvoiceMail;
use App\Models\ModulesInvoice\Invoice;
use App\Models\ModulesInvoice\InvoiceCard;
use App\Models\ModulesInvoice\InvoiceEmailTemplate;
use App\Models\ModulesInvoice\InvoiceRecipient;
use App\Models\Store;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class InvoiceService
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }
    public function index(array $filters)
    {
        $query = Invoice::with(['store', 'user', 'invoiceCard']);
        
        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('store', function($storeQuery) use ($search) {
                    $storeQuery->where('store_number', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                });
            });
        }
        
        // Filter by store
        if (!empty($filters['store_filter']) && $filters['store_filter'] !== '') {
            $query->where('store_id', $filters['store_filter']);
        }
        
        // Filter by month
        if (!empty($filters['month_filter'])) {
            $date = \Carbon\Carbon::parse($filters['month_filter']);
            $query->whereYear('period_start', $date->year)
                ->whereMonth('period_start', $date->month);
        }
        
        // Filter by status
        if (!empty($filters['status_filter']) && $filters['status_filter'] !== 'all') {
            $query->where('status', $filters['status_filter']);
        }
        
        // Date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('period_start', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('period_end', '<=', $filters['date_to']);
        }
        
        $invoices = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        $stats = [
            'total' => Invoice::count(),
            'this_month' => Invoice::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->count(),
            'total_amount' => Invoice::sum('grand_total'),
            'avg_invoice' => Invoice::avg('grand_total'),
        ];
        
        $stores = Store::orderBy('store_number')->get();
        
        return [
            'invoices' => $invoices,
            'stats' => $stats,
            'stores' => $stores,
        ];
    }
    public function show($id): array
    {
        $invoice = Invoice::with(['store', 'user', 'invoiceCard'])->findOrFail($id);

        $invoiceData = $this->getInvoiceData($invoice);

        $templates = InvoiceEmailTemplate::all();
        $recipients = InvoiceRecipient::where('store_id', $invoice->store_id)->get();

        return array_merge($invoiceData, [
            'templates' => $templates,
            'recipients' => $recipients,
        ]);
    }
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
            ->where('payments.is_admin_equipment', true);
         // If there are linked maintenance requests, filter by those (regardless of date)
        // This ensures equipment purchased for specific tasks shows up in the invoice
       
        if (!empty($maintenanceRequestIds)) {
            $equipmentQuery->where(function($query) use ($maintenanceRequestIds, $invoice) {
                $query->whereIn('payments.maintenance_request_id', $maintenanceRequestIds)
                    ->orWhereBetween('payments.date', [$invoice->period_start, $invoice->period_end]);
            });
        } else {
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
    public function downloadInvoiceImage(int $id)
    {
        $invoice = Invoice::findOrFail($id);

        if (!$invoice->image_path || !Storage::exists($invoice->image_path)) {
            throw new \Exception('Invoice image not found. Please generate it first.');
        }

        return [
            'path' => $invoice->image_path,
            'name' => "Invoice-{$invoice->invoice_number}.png"
        ];
    }

    public function saveInvoiceImage(int $id, string $path): void
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update(['image_path' => $path]);
    }
    public function sendInvoiceEmail(int $id, string $email, ?int $templateId = null): void
    {
        $invoice = Invoice::findOrFail($id);

        $invoiceData = $this->getInvoiceData($invoice);

        $template = $templateId
            ? InvoiceEmailTemplate::find($templateId)
            : InvoiceEmailTemplate::where('is_default', true)->first();

        if (!$template) {
            throw new \Exception('No email template found. Please create a default template first.');
        }

        // Send email
        Mail::to($email)->send(
            new InvoiceMail($invoice, $template, $invoiceData)
        );

        // Mark as sent
        $this->markAsSent($invoice, $email);
    }
    public function markAsSent(Invoice $invoice, string $email): Invoice
    {
        $invoice->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_to_email' => $email,
        ]);
        
        return $invoice->fresh();
    }
    
}