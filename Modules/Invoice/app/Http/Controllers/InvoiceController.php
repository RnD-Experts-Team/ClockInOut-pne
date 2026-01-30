<?php

namespace Modules\Invoice\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Invoice\Models\Invoice;
use Modules\Invoice\Models\InvoiceCard;
use Modules\Invoice\Models\InvoiceEmailTemplate;
use Modules\Invoice\Models\InvoiceRecipient;
use Modules\Invoice\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    protected $invoiceService;
    
    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }
    
    /**
     * Display all invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['store', 'user', 'invoiceCard']);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('store', function($storeQuery) use ($search) {
                      $storeQuery->where('store_number', 'like', "%{$search}%")
                                 ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by store
        if ($request->filled('store_filter') && $request->store_filter !== '') {
            $query->where('store_id', $request->store_filter);
        }
        
        // Filter by month
        if ($request->filled('month_filter')) {
            $date = \Carbon\Carbon::parse($request->month_filter);
            $query->whereYear('period_start', $date->year)
                  ->whereMonth('period_start', $date->month);
        }
        
        // Filter by status
        if ($request->filled('status_filter') && $request->status_filter !== 'all') {
            $query->where('status', $request->status_filter);
        }
        
        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('period_start', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('period_end', '<=', $request->date_to);
        }
        
        $invoices = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        // Calculate stats
        $stats = [
            'total' => Invoice::count(),
            'this_month' => Invoice::whereMonth('created_at', now()->month)
                                   ->whereYear('created_at', now()->year)
                                   ->count(),
            'total_amount' => Invoice::sum('grand_total'),
            'avg_invoice' => Invoice::avg('grand_total'),
        ];
        
        $stores = \App\Models\Store::orderBy('store_number')->get();
        
        return view('invoice::invoices.index', compact('invoices', 'stats', 'stores'));
    }
    
    /**
     * Show invoice details
     */
    public function show($id)
    {
        $invoice = Invoice::with(['store', 'user', 'invoiceCard'])->findOrFail($id);
        $invoiceData = $this->invoiceService->getInvoiceData($invoice);
        
        // Get email templates and recipients
        $templates = InvoiceEmailTemplate::all();
        $recipients = InvoiceRecipient::where('store_id', $invoice->store_id)->get();
        return view('invoice::invoices.show', array_merge($invoiceData, [
            'templates' => $templates,
            'recipients' => $recipients,
        ]));
    }
    
    /**
     * Generate invoice from card
     */
    public function generateFromCard(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:invoice_cards,id',
        ]);
        
        $invoice = $this->invoiceService->createInvoiceFromCard($request->card_id);
        
        return redirect()->route('invoice.invoices.show', $invoice->id)
            ->with('success', 'Invoice generated successfully!');
    }
    
    /**
     * Send invoice via email
     */
    public function sendEmail(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'template_id' => 'nullable|exists:invoice_email_templates,id',
        ]);
        
        try {
            $invoice = Invoice::findOrFail($id);
            $invoiceData = $this->invoiceService->getInvoiceData($invoice);
            
            $template = $request->template_id 
                ? InvoiceEmailTemplate::find($request->template_id)
                : InvoiceEmailTemplate::where('is_default', true)->first();
            
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'No email template found. Please create a default template first.',
                ], 400);
            }
            
            // Send email
            Mail::to($request->email)->send(new \Modules\Invoice\Mail\InvoiceMail($invoice, $template, $invoiceData));
            
            // Mark as sent
            $this->invoiceService->markAsSent($invoice, $request->email);
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice sent successfully to ' . $request->email,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending invoice email: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error sending email: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Download invoice image
     */
    public function download($id)
    {
        $invoice = Invoice::findOrFail($id);
        
        if (!$invoice->image_path || !Storage::exists($invoice->image_path)) {
            return back()->with('error', 'Invoice image not found. Please generate it first.');
        }
        
        return Storage::download($invoice->image_path, "Invoice-{$invoice->invoice_number}.png");
    }
    
    /**
     * Save generated image path
     */
    public function saveImage(Request $request, $id)
    {
        $request->validate([
            'image_path' => 'required|string',
        ]);
        
        $invoice = Invoice::findOrFail($id);
        $invoice->update(['image_path' => $request->image_path]);
        
        return response()->json([
            'success' => true,
            'message' => 'Image saved successfully!',
        ]);
    }
}
