<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Company;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['company', 'store']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('store', 'like', "%{$search}%")
                    ->orWhere('what_got_fixed', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('company', function($companyQuery) use ($search) {
                        $companyQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('store', function($storeQuery) use ($search) {
                        $storeQuery->where('store_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by company
        if ($request->filled('company_id') && $request->company_id !== 'all') {
            $query->where('company_id', $request->company_id);
        }

        // Filter by store
        if ($request->filled('store_id') && $request->store_id !== 'all') {
            $query->where('store_id', $request->store_id);
        }

        // Filter by payments status
        if ($request->filled('paid') && $request->paid !== 'all') {
            $query->where('paid', $request->paid === '1');
        }

        // Filter by maintenance type
        if ($request->filled('maintenance_type') && $request->maintenance_type !== 'all') {
            $query->where('maintenance_type', $request->maintenance_type);
        }

        // Filter by equipment presence
        if ($request->filled('has_equipment') && $request->has_equipment !== 'all') {
            if ($request->has_equipment === '1') {
                $query->whereHas('equipmentItems');
            } else {
                $query->whereDoesntHave('equipmentItems');
            }
        }

        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Time-based filters
        if ($request->filled('time_filter')) {
            switch ($request->time_filter) {
                case 'this_month':
                    $query->thisMonth();
                    break;
                case 'within_90_days':
                    $query->within90Days();
                    break;
                case 'within_4_weeks':
                    $query->within4Weeks();
                    break;
                case 'this_week':
                    $query->thisWeek();
                    break;
                case 'within_1_year':
                    $query->within1Year();
                    break;
            }
        }

        // Cost range filters
        if ($request->filled('min_cost')) {
            $query->where('cost', '>=', $request->min_cost);
        }
        if ($request->filled('max_cost')) {
            $query->where('cost', '<=', $request->max_cost);
        }

        $payments = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();
        foreach ($payments as $payment) {
            if (!$payment->store && $payment->store_id) {
                // Try to find the store using the store_id
                $payment->store = Store::find($payment->store_id);

                // If still no store found, create a temporary object for display
                if (!$payment->store) {
                    $payment->store = (object)[
                        'id' => $payment->store_id,
                        'store_number' => 'Store #' . $payment->store_id,
                        'name' => 'Store ID: ' . $payment->store_id
                    ];
                }
            }
        }
        // Calculate statistics
        $stats = $this->calculateStats($request);
        $companies = Company::orderBy('name')->get();
        $stores = Store::orderBy('store_number')->get(); // Make sure this exists
        $maintenanceTypes = Payment::distinct()->pluck('maintenance_type')->filter()->sort()->values();

        // Ensure availableStores is always an array, never null
        $availableStores = Payment::distinct()
            ->whereNotNull('store') // Only get non-null stores
            ->pluck('store')
            ->filter() // Remove any empty values
            ->sort()
            ->values()
            ->toArray(); // Convert to array
        // Check for store_id field
        $availableStores = Store::whereHas('payments')
            ->orderBy('store_number') // Remove the backslash
            ->pluck('name')
            ->toArray();
//        dd($payments);

        return view('admin.payments.index', compact(
            'payments',
            'stats',
            'companies',
            'stores', // If you need Store objects
            'availableStores', // This should be an array of store numbers/names
            'maintenanceTypes'
        ));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $stores = Store::orderBy('store_number')->get();
        $fixedOptions = Payment::select('what_got_fixed')
            ->whereNotNull('what_got_fixed')
            ->distinct()
            ->orderBy('what_got_fixed')
            ->pluck('what_got_fixed');
        return view('admin.payments.create', compact('companies', 'stores', 'fixedOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'nullable|exists:stores,id',
            'new_store_number' => 'nullable|string|max:255',
            'new_store_name' => 'nullable|string|max:255',
            'date' => 'required|date',
            'what_got_fixed' => 'nullable|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'paid' => 'boolean',
            'payment_method' => 'nullable|string|max:255',
            'maintenance_type' => 'nullable|string|max:255',
            'maintenance_request_id' => 'nullable|exists:maintenance_requests,id',
            'equipment_items' => 'nullable|array',
            'equipment_items.*.name' => 'nullable|string|max:255',
            'equipment_items.*.quantity' => 'nullable|integer|min:1',
            'equipment_items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->all();

            // Handle store creation if needed
            if (!$data['store_id'] && $data['new_store_number']) {
                $store = Store::create([
                    'store_number' => $data['new_store_number'],
                    'name' => $data['new_store_name'],
                    'is_active' => true,
                ]);
                $data['store_id'] = $store->id;
                $data['store'] = $store->store_number; // For backward compatibility
            } elseif ($data['store_id']) {
                $store = Store::find($data['store_id']);
                $data['store'] = $store->store_number; // For backward compatibility
            }

            unset($data['new_store_number'], $data['new_store_name']);

            $payment = Payment::create($data);

            // If payment created from clocking session
            if ($request->filled('clocking_id')) {
                $payment->clocking_id = $request->clocking_id;
                $payment->source_system = 'clocking_system';
                $payment->sync_status = 'synced';
                $payment->save();

                // Update clocking total purchase cost
                app(\App\Services\PurchaseSynchronizationService::class)->updateClockingPurchaseCost($request->clocking_id);
            }

            // If payment created from invoice card
            if ($request->filled('invoice_card_id')) {
                $payment->invoice_card_id = $request->invoice_card_id;
                $payment->source_system = 'invoice_system';
                $payment->sync_status = 'synced';
                $payment->save();
            }

            // Handle equipment items if provided
            if ($request->has('equipment_items') && is_array($request->equipment_items)) {
                $hasEquipment = false;
                foreach ($request->equipment_items as $item) {
                    // Only create if item has a name
                    if (!empty($item['name'])) {
                        $quantity = (int) ($item['quantity'] ?? 1);
                        $unitCost = (float) ($item['unit_cost'] ?? 0);
                        
                        $payment->equipmentItems()->create([
                            'item_name' => $item['name'],
                            'quantity' => $quantity,
                            'unit_cost' => $unitCost,
                            'total_cost' => $quantity * $unitCost,
                        ]);
                        $hasEquipment = true;
                    }
                }
                
                // If equipment items were added, mark as admin equipment
                if ($hasEquipment) {
                    $payment->is_admin_equipment = true;
                    $payment->save();
                }
            }

            DB::commit();

            return redirect()->route('payments.index')
                ->with('success', 'Payment record created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create payment: ' . $e->getMessage()]);
        }
    }

    public function show(Payment $payment)
    {
        $payment->load(['company', 'store', 'equipmentItems']);
        return view('admin.payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $payment->load('equipmentItems');
        $companies = Company::orderBy('name')->get();
        $stores = Store::orderBy('store_number')->get();
        $fixedOptions = Payment::select('what_got_fixed')
            ->whereNotNull('what_got_fixed')
            ->distinct()
            ->orderBy('what_got_fixed')
            ->pluck('what_got_fixed');
        return view('admin.payments.edit', compact('payment', 'companies', 'stores', 'fixedOptions'));
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'store_id' => 'nullable|exists:stores,id',
            'new_store_number' => 'nullable|string|max:255',
            'new_store_name' => 'nullable|string|max:255',
            'date' => 'required|date',
            'what_got_fixed' => 'nullable|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'paid' => 'boolean',
            'payment_method' => 'nullable|string|max:255',
            'maintenance_type' => 'nullable|string|max:255',
            'maintenance_request_id' => 'nullable|exists:maintenance_requests,id',
            'equipment_items' => 'nullable|array',
            'equipment_items.*.name' => 'nullable|string|max:255',
            'equipment_items.*.quantity' => 'nullable|integer|min:1',
            'equipment_items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->all();

            // Handle store creation if needed
            if (!$data['store_id'] && $data['new_store_number']) {
                $store = Store::create([
                    'store_number' => $data['new_store_number'],
                    'name' => $data['new_store_name'],
                    'is_active' => true,
                ]);
                $data['store_id'] = $store->id;
                $data['store'] = $store->store_number; // For backward compatibility
            } elseif ($data['store_id']) {
                $store = Store::find($data['store_id']);
                $data['store'] = $store->store_number; // For backward compatibility
            }

            unset($data['new_store_number'], $data['new_store_name']);

            $payment->update($data);

            // If updated with clocking or invoice_card references, set source and sync accordingly
            if ($request->filled('clocking_id')) {
                $payment->clocking_id = $request->clocking_id;
                $payment->source_system = 'clocking_system';
                $payment->sync_status = 'synced';
                $payment->save();
                app(\App\Services\PurchaseSynchronizationService::class)->updateClockingPurchaseCost($request->clocking_id);
            }

            if ($request->filled('invoice_card_id')) {
                $payment->invoice_card_id = $request->invoice_card_id;
                $payment->source_system = 'invoice_system';
                $payment->sync_status = 'synced';
                $payment->save();
            }

            // Handle equipment items update
            // Delete existing equipment items
            $payment->equipmentItems()->delete();
            
            // Re-create equipment items from request
            $hasEquipment = false;
            if ($request->has('equipment_items') && is_array($request->equipment_items)) {
                foreach ($request->equipment_items as $item) {
                    // Only create if item has a name
                    if (!empty($item['name'])) {
                        $quantity = (int) ($item['quantity'] ?? 1);
                        $unitCost = (float) ($item['unit_cost'] ?? 0);
                        
                        $payment->equipmentItems()->create([
                            'item_name' => $item['name'],
                            'quantity' => $quantity,
                            'unit_cost' => $unitCost,
                            'total_cost' => $quantity * $unitCost,
                        ]);
                        $hasEquipment = true;
                    }
                }
            }
            
            // Update is_admin_equipment flag based on whether equipment items exist
            $payment->is_admin_equipment = $hasEquipment;
            $payment->save();

            DB::commit();

            return redirect()->route('payments.index')
                ->with('success', 'Payment record updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update payment: ' . $e->getMessage()]);
        }
    }

    public function dashboard()
    {
        $user = Auth::user();

        // Ensure the role column exists and redirect accordingly
        if ($user->role == 'admin') {
            return redirect()->route('admin.clocking');
        } elseif ($user->role == 'user') {
            return redirect()->route('clocking.index');
        }
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('payments.index')
            ->with('success', 'Payment record deleted successfully.');
    }

    public function export(Request $request)
    {
        {
            $query = Payment::with(['company', 'store']);

            // Apply same filters as index
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('store', 'like', "%{$search}%")
                        ->orWhere('what_got_fixed', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('company', function($companyQuery) use ($search) {
                            $companyQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('store', function($storeQuery) use ($search) {
                            $storeQuery->where('store_number', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            }

            $payments = $query->orderBy('date', 'desc')->get();

            $filename = 'payments-export-' . now()->format('Y-m-d-H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($payments) {
                $file = fopen('php://output', 'w');

                fputcsv($file, [
                    'Store Number',
                    'Store Name',
                    'Date',
                    'What Got Fixed',
                    'Company Name',
                    'Cost',
                    'Notes',
                    'Paid',
                    'Payment Method',
                    'Maintenance Type'
                ]);

                foreach ($payments as $payment) {
                    // Debug: Check what we have
                    $storeNumber = 'N/A';
                    $storeName = 'N/A';

                    // Method 1: Try eager loaded relationship first
                    if ($payment->relationLoaded('store') && $payment->store) {
                        $storeNumber = $payment->store->store_number ?? 'N/A';
                        $storeName = $payment->store->name ?? 'N/A';
                    }
                    // Method 2: If relationship failed, manually fetch store
                    elseif ($payment->store_id) {
                        $store = \App\Models\Store::find($payment->store_id);
                        if ($store) {
                            $storeNumber = $store->store_number ?? 'N/A';
                            $storeName = $store->name ?? 'N/A';
                        }
                    }

                    // Handle company
                    $companyName = 'N/A';
                    if ($payment->relationLoaded('company') && $payment->company) {
                        $companyName = $payment->company->name;
                    } elseif ($payment->company_id) {
                        $company = \App\Models\Company::find($payment->company_id);
                        $companyName = $company ? $company->name : 'N/A';
                    }

                    fputcsv($file, [
                        $storeNumber,
                        $storeName,
                        $payment->date ? $payment->date->format('Y-m-d') : 'N/A',
                        $payment->what_got_fixed ?? '',
                        $companyName,
                        $payment->cost ?? 0,
                        $payment->notes ?? '',
                        $payment->paid ? 'Yes' : 'No',
                        $payment->payment_method ?? '',
                        $payment->maintenance_type ?? ''
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

    }

    public function portfolioStats(Request $request)
    {
        $stores = $request->input('stores', []);
        $query = Payment::query();

        if (!empty($stores)) {
            $query->whereHas('store', function($q) use ($stores) {
                $q->whereIn('store_number', $stores);
            });
        }

        $payments = $query->get();

        $totals = [
            'total_cost' => $payments->sum('cost'),
            'paid_amount' => $payments->where('paid', true)->sum('cost'),
            'unpaid_amount' => $payments->where('paid', false)->sum('cost'),
            'count' => $payments->count(),
            'stores_count' => $payments->pluck('store_id')->unique()->count()
        ];

        $averages = [
            'avg_cost' => $payments->count() > 0 ? $payments->avg('cost') : 0,
            'avg_per_store' => $totals['stores_count'] > 0 ? $totals['total_cost'] / $totals['stores_count'] : 0
        ];

        return response()->json([
            'selected_stores' => $stores,
            'totals' => $totals,
            'averages' => $averages
        ]);
    }

    private function calculateStats($request = null)
    {
        // Keep the same implementation but add store relationship filtering
        $applyFilters = function($query) use ($request) {
            if ($request) {
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('store', 'like', "%{$search}%")
                            ->orWhere('what_got_fixed', 'like', "%{$search}%")
                            ->orWhere('notes', 'like', "%{$search}%")
                            ->orWhereHas('company', function($companyQuery) use ($search) {
                                $companyQuery->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('store', function($storeQuery) use ($search) {
                                $storeQuery->where('store_number', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
                            });
                    });
                }

                if ($request->filled('company_id') && $request->company_id !== 'all') {
                    $query->where('company_id', $request->company_id);
                }

                if ($request->filled('store_id') && $request->store_id !== 'all') {
                    $query->where('store_id', $request->store_id);
                }

                if ($request->filled('paid') && $request->paid !== 'all') {
                    $query->where('paid', $request->paid === '1');
                }

                if ($request->filled('maintenance_type') && $request->maintenance_type !== 'all') {
                    $query->where('maintenance_type', $request->maintenance_type);
                }

                if ($request->filled('date_from')) {
                    $query->whereDate('date', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('date', '<=', $request->date_to);
                }

                if ($request->filled('time_filter')) {
                    switch ($request->time_filter) {
                        case 'this_month':
                            $query->thisMonth();
                            break;
                        case 'within_90_days':
                            $query->within90Days();
                            break;
                        case 'within_4_weeks':
                            $query->within4Weeks();
                            break;
                        case 'this_week':
                            $query->thisWeek();
                            break;
                        case 'within_1_year':
                            $query->within1Year();
                            break;
                    }
                }

                if ($request->filled('min_cost')) {
                    $query->where('cost', '>=', $request->min_cost);
                }
                if ($request->filled('max_cost')) {
                    $query->where('cost', '<=', $request->max_cost);
                }

                if ($request->filled('has_equipment') && $request->has_equipment !== 'all') {
                    if ($request->has_equipment === '1') {
                        $query->whereHas('equipmentItems');
                    } else {
                        $query->whereDoesntHave('equipmentItems');
                    }
                }
            }
            return $query;
        };

        // Calculate equipment statistics
        $equipmentPaymentsQuery = $applyFilters(Payment::query())->whereHas('equipmentItems');
        $equipmentTotal = \DB::table('payment_equipment_items')
            ->whereIn('payment_id', $equipmentPaymentsQuery->pluck('id'))
            ->sum('total_cost');

        return [
            'total'          => $applyFilters(Payment::query())->count(),
            'total_cost'     => $applyFilters(Payment::query())->sum('cost'),
            'paid'           => $applyFilters(Payment::query())->where('paid', true)->count(),
            'unpaid'         => $applyFilters(Payment::query())->where('paid', false)->count(),
            'this_month'     => $applyFilters(Payment::query())->thisMonth()->count(),
            'this_month_cost'=> $applyFilters(Payment::query())->thisMonth()->sum('cost'),
            'within_90_days' => $applyFilters(Payment::query())->within90Days()->count(),
            'unpaid_amount'  => $applyFilters(Payment::query())->where('paid', false)->sum('cost'),
            'paid_amount'    => $applyFilters(Payment::query())->where('paid', true)->sum('cost'),
            'equipment_purchases' => $equipmentPaymentsQuery->count(),
            'equipment_total' => $equipmentTotal
        ];
    }

    public function storeImageView($store)
    {
        try {
            $payments = Payment::whereHas('store', function($q) use ($store) {
                $q->where('name', $store)
                    ->orWhere('store_number', $store);
            })
                ->with(['company', 'store'])
                ->orderByDesc('date')
                ->get();

            if ($payments->isEmpty()) {
                $grouped = collect();
            } else {
                $grouped = $payments->groupBy(function($payment) {
                    return $payment->what_got_fixed ?: '(blank)';
                });
            }

            $grandTotal = $payments->sum('cost');

            return view('admin.payments.reports.store-image', compact('store', 'grouped', 'grandTotal'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Keep all other report methods exactly the same...
    public function costByCompanyReport(Request $request)
    {
        $allCompanies = Company::all();
        // Get filters from index page
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Apply date filters
        $paymentsQuery = Payment::with('company');

        // Apply date filters
        if ($request->get('date_from') && $request->get('date_to')) {
            $paymentsQuery->whereBetween('date', [
                Carbon::parse($request->get('date_from'))->startOfDay(),
                Carbon::parse($request->get('date_to'))->endOfDay()
            ]);
        } elseif ($request->get('date_from')) {
            $paymentsQuery->whereDate('date', '>=', $request->get('date_from'));
        } elseif ($request->get('date_to')) {
            $paymentsQuery->whereDate('date', '<=', $request->get('date_to'));
        }
        // Don't apply default date filter - let it show all data if no dates specified

        // Apply other filters
        if ($request->get('company_id') && $request->get('company_id') !== 'all') {
            $paymentsQuery->where('company_id', $request->get('company_id'));
        }

        if ($request->get('search')) {
            $search = $request->get('search');
            $paymentsQuery->where(function($q) use ($search) {
                $q->where('store', 'like', "%{$search}%")
                    ->orWhere('what_got_fixed', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('company', function($companyQuery) use ($search) {
                        $companyQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->get('maintenance_type') && $request->get('maintenance_type') !== 'all') {
            $paymentsQuery->where('maintenance_type', $request->get('maintenance_type'));
        }

        if ($request->get('paid') && $request->get('paid') !== 'all') {
            $paymentsQuery->where('paid', $request->get('paid') === '1');
        }

        // Get the filtered payments
        $filteredPayments = $paymentsQuery->get();

        // Group by company and calculate totals
        $companies = $allCompanies->map(function($company) use ($filteredPayments) {
            $companyPayments = $filteredPayments->where('company_id', $company->id);

            return (object)[
                'id' => $company->id,
                'name' => $company->name,
                'total_cost' => $companyPayments->sum('cost'),
                'paid_cost' => $companyPayments->where('paid', true)->sum('cost'),
                'unpaid_cost' => $companyPayments->where('paid', false)->sum('cost'),
                'payment_count' => $companyPayments->count()
            ];
        })->sortByDesc('total_cost');

        // Calculate 90-day costs
        $companiesWithNinetyDays = $allCompanies->map(function($company) {
            $ninetyDayQuery = Payment::where('company_id', $company->id)
                ->where('date', '>=', now()->subDays(90));

            return (object)[
                'id' => $company->id,
                'name' => $company->name,
                'ninety_day_cost' => $ninetyDayQuery->sum('cost'),
                'payments' => collect([])
            ];
        });


        return view('admin.payments.reports.cost-by-company', compact(
            'companies',
            'companiesWithNinetyDays',
            'dateFrom',
            'dateTo'
        ));
    }

    public function monthlyReport(Request $request)
    {
        // Get filters from index page
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $companyId = $request->get('company_id');
        $search = $request->get('search');
        $maintenanceType = $request->get('maintenance_type');

        // FIXED: Determine target year from filters, not defaulting to current year
        if ($dateFrom) {
            $targetYear = Carbon::parse($dateFrom)->year;
        } elseif ($dateTo) {
            $targetYear = Carbon::parse($dateTo)->year;
        } else {
            $targetYear = $request->get('year', now()->year);
        }

        $currentYear = now()->year;

        // Base query
        $query = Payment::query();

        // FIXED: Apply date filters properly
        if ($dateFrom && $dateTo) {
            $query->whereBetween('date', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ]);
        } elseif ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        } else {
            // Only apply year filter if no date range specified
            $query->whereYear('date', $targetYear);
        }

        // Apply additional filters
        if ($companyId && $companyId !== 'all') {
            $query->where('company_id', $companyId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('store', 'like', "%{$search}%")
                    ->orWhere('what_got_fixed', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('company', function($companyQuery) use ($search) {
                        $companyQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($maintenanceType && $maintenanceType !== 'all') {
            $query->where('maintenance_type', $maintenanceType);
        }

        // Get available years
        $availableYears = Payment::selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // FIXED: Process monthly data for the correct year range
        $monthlyData = [];

        // Determine which months to process based on date filters
        if ($dateFrom && $dateTo) {
            $startMonth = Carbon::parse($dateFrom)->month;
            $endMonth = Carbon::parse($dateTo)->month;
            $startYear = Carbon::parse($dateFrom)->year;
            $endYear = Carbon::parse($dateTo)->year;

            // Handle cross-year date ranges
            if ($startYear == $endYear) {
                $monthRange = range($startMonth, $endMonth);
                $yearToProcess = $startYear;
            } else {
                // For simplicity, process all months of the target year
                $monthRange = range(1, 12);
                $yearToProcess = $targetYear;
            }
        } else {
            $monthRange = range(1, 12);
            $yearToProcess = $targetYear;
        }

        foreach ($monthRange as $month) {
            $monthQuery = clone $query;

            // Add month filter
            $monthQuery->whereMonth('date', $month)
                ->whereYear('date', $yearToProcess);

            // Calculate data for this month
            $paidAmount = (clone $monthQuery)->where('paid', true)->sum('cost');
            $totalAmount = $monthQuery->sum('cost');
            $unpaidAmount = $totalAmount - $paidAmount;
            $percentage = $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0;
            $paymentCount = $monthQuery->count();

            $monthlyData[] = [
                'month' => $month,
                'month_name' => Carbon::create($yearToProcess, $month, 1)->format('F'),
                'paid_amount' => $paidAmount,
                'unpaid_amount' => $unpaidAmount,
                'total_amount' => $totalAmount,
                'percentage' => $percentage,
                'payment_count' => $paymentCount
            ];
        }

        // Calculate totals
        $grandTotal = collect($monthlyData)->sum('paid_amount');
        $grandTotalAll = collect($monthlyData)->sum('total_amount');
        $avgPercentage = $grandTotalAll > 0 ? ($grandTotal / $grandTotalAll) * 100 : 0;

        return view('admin.payments.reports.monthly-report', compact(
            'monthlyData',
            'targetYear',
            'currentYear',
            'availableYears',
            'grandTotal',
            'grandTotalAll',
            'avgPercentage',
            'dateFrom',
            'dateTo'
        ));
    }
    public function weeklyMaintenanceReport(Request $request)
    {
        // Get filter parameters
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $companyId = $request->query('company_id');
        $paid = $request->query('paid');
        $maintenanceType = $request->query('maintenance_type');

        // Base query for ALL payments (no date filters for time period calculations)
        $baseQuery = Payment::with(['company', 'store']);

        // Apply non-date filters
        if ($companyId && $companyId !== 'all') {
            $baseQuery->where('company_id', $companyId);
        }

        if ($paid && $paid !== 'all') {
            $baseQuery->where('paid', $paid === '1');
        }

        if ($maintenanceType && $maintenanceType !== 'all') {
            $baseQuery->where('maintenance_type', $maintenanceType);
        }

        // Get ALL payments for time period calculations
        $allPayments = $baseQuery->get();

        // Debug: Log the query results
        Log::info('All payments count: ' . $allPayments->count());

        // FIXED: Handle stores properly - check if store_id exists but store is null
        $storeIds = collect();
        $storeMap = collect();

        foreach ($allPayments as $payment) {
            if ($payment->store) {
                $storeMap->put($payment->store_id, $payment->store);
            } elseif ($payment->store_id) {
                $storeIds->push($payment->store_id);
            }
        }

        // Batch load missing stores
        if ($storeIds->isNotEmpty()) {
            $missingStores = Store::whereIn('id', $storeIds->unique())->get();
            foreach ($missingStores as $store) {
                $storeMap->put($store->id, $store);
            }
        }

        // Build stores collection with actual names
        $stores = collect();
        foreach ($allPayments as $payment) {
            if ($payment->store_id && $storeMap->has($payment->store_id)) {
                $store = $storeMap->get($payment->store_id);
                $stores->push([
                    'id' => $store->id,
                    'name' => $store->name,
                    'store_number' => $store->store_number ?? null
                ]);
            } elseif ($payment->store_id) {
                $stores->push([
                    'id' => $payment->store_id,
                    'name' => "Store ID: " . $payment->store_id,
                    'store_number' => null
                ]);
            } else {
                $stores->push([
                    'id' => null,
                    'name' => "Unknown Store",
                    'store_number' => null
                ]);
            }
        }

        $stores = $stores->unique('id')->sortBy('name');
        $storeNames = $stores->pluck('name');

        // Group by store for the report
        $storeData = [];
        $totalWeeklyCost = 0;
        $totalMonthlyCost = 0;
        $totalEquipmentCost = 0;
        $totalServiceCost = 0;
        $totalFourWeeksCost = 0;
        $totalNinetyDaysCost = 0;

        foreach ($stores as $storeInfo) {
            $storeId = $storeInfo['id'];

            // Create base query for this store with all filters applied
            $storeQuery = Payment::query();

            // Apply all the same filters
            if ($companyId && $companyId !== 'all') {
                $storeQuery->where('company_id', $companyId);
            }
            if ($paid && $paid !== 'all') {
                $storeQuery->where('paid', $paid === '1');
            }
            if ($maintenanceType && $maintenanceType !== 'all') {
                $storeQuery->where('maintenance_type', $maintenanceType);
            }

            // Filter by store
            if ($storeId === null) {
                $storeQuery->whereNull('store_id');
            } else {
                $storeQuery->where('store_id', $storeId);
            }

            // Calculate "This Week" data (filtered period) using date filters
            $filteredPeriodQuery = clone $storeQuery;
            if ($dateFrom) {
                $filteredPeriodQuery->whereDate('date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $filteredPeriodQuery->whereDate('date', '<=', $dateTo);
            }
            if (!$dateFrom && !$dateTo) {
                // If no date filters provided, use current week
                $filteredPeriodQuery->thisWeek();
            }

            $filteredPeriodPayments = $filteredPeriodQuery->get();
            $totalCost = $filteredPeriodPayments->sum('cost');
            $equipmentCost = $filteredPeriodPayments->filter(function($payment) {
                return strtolower($payment->maintenance_type) === 'equipment/parts';
            })->sum('cost');
            $serviceCost = $filteredPeriodPayments->where('maintenance_type', 'Service')->sum('cost');
//dd($equipmentCost);
            // Use model scopes for time period calculations
            $thisMonthCost = (clone $storeQuery)->thisMonth()->sum('cost');
            $fourWeeksCost = (clone $storeQuery)->within4Weeks()->sum('cost');
            $ninetyDaysCost = (clone $storeQuery)->within90Days()->sum('cost');

            $storeData[] = [
                'store' => $storeInfo['name'],
                'store_id' => $storeInfo['id'],
                'store_number' => $storeInfo['store_number'],
                'total_cost' => $totalCost,
                'equipment_cost' => $equipmentCost,
                'service_cost' => $serviceCost,
                'this_month_cost' => $thisMonthCost,
                'four_weeks_cost' => $fourWeeksCost,
                'ninety_days_cost' => $ninetyDaysCost,
            ];

            // Calculate totals
            $totalWeeklyCost += $totalCost;
            $totalMonthlyCost += $thisMonthCost;
            $totalEquipmentCost += $equipmentCost;
            $totalServiceCost += $serviceCost;
            $totalFourWeeksCost += $fourWeeksCost;
            $totalNinetyDaysCost += $ninetyDaysCost;
        }

        // Set week and year based on filter dates
        if ($dateFrom) {
            $currentWeek = \Carbon\Carbon::parse($dateFrom)->weekOfYear;
            $targetYear = \Carbon\Carbon::parse($dateFrom)->year;
        } else {
            $currentWeek = now()->weekOfYear;
            $targetYear = now()->year;
        }

        // Get available years
        $availableYears = Payment::selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('admin.payments.reports.weekly-maintenance', compact(
            'storeNames',
            'storeData',
            'currentWeek',
            'targetYear',
            'totalWeeklyCost',
            'totalMonthlyCost',
            'totalEquipmentCost',
            'totalServiceCost',
            'totalFourWeeksCost',
            'totalNinetyDaysCost',
            'availableYears'
        ));
    }





    public function costPerStoreYearlyReport(Request $request)
    {
        // Get filters from index page
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $companyId = $request->get('company_id');
        $search = $request->get('search');
        $maintenanceType = $request->get('maintenance_type');
        $paid = $request->get('paid');

        // FIXED: Determine target year from filters
        if ($dateFrom) {
            $targetYear = Carbon::parse($dateFrom)->year;
        } elseif ($dateTo) {
            $targetYear = Carbon::parse($dateTo)->year;
        } else {
            $targetYear = $request->get('year', now()->year);
        }

        // Base query with filters
        $query = Payment::query();

        // Apply date filters
        if ($dateFrom && $dateTo) {
            $query->whereBetween('date', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ]);
        } elseif ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        } else {
            // Default to within 1 year from target year
            $query->where('date', '>=', Carbon::create($targetYear, 1, 1)->startOfYear())
                ->where('date', '<=', Carbon::create($targetYear, 12, 31)->endOfYear());
        }

        // Apply additional filters
        if ($companyId && $companyId !== 'all') {
            $query->where('company_id', $companyId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('store', 'like', "%{$search}%")
                    ->orWhere('what_got_fixed', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('company', function($companyQuery) use ($search) {
                        $companyQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($maintenanceType && $maintenanceType !== 'all') {
            $query->where('maintenance_type', $maintenanceType);
        }

        if ($paid && $paid !== 'all') {
            $query->where('paid', $paid === '1');
        }

        // FIXED: Get store yearly costs with proper grouping
        $storeYearlyCosts = $query
            ->leftJoin('stores', 'payments.store_id', '=', 'stores.id')
            ->selectRaw('
        COALESCE(stores.name, CONCAT("Store ", payments.store_id)) as store_display,
        payments.store_id,
        SUM(cost) as total_cost,
        COUNT(*) as payment_count,
        AVG(cost) as avg_cost
    ')
            ->whereNotNull('cost')
            ->where('cost', '>', 0)
            ->groupBy('payments.store_id', 'stores.name')
            ->orderBy('total_cost', 'desc')
            ->get();

        // Calculate summary data
        $grandTotal = $storeYearlyCosts->sum('total_cost');
        $avgCostPerStore = $storeYearlyCosts->count() > 0 ? $grandTotal / $storeYearlyCosts->count() : 0;

        // Get available years for dropdown
        $availableYears = Payment::selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        return view('admin.payments.reports.cost-per-store-yearly', compact(
            'storeYearlyCosts',
            'grandTotal',
            'avgCostPerStore',
            'targetYear',
            'availableYears',
            'dateFrom',
            'dateTo'
        ));
    }

    public function pendingProjectsReport()
    {
        return view('admin.payments.reports.pending-projects');
    }
}
