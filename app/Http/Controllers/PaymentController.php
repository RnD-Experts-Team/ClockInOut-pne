<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Company;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
            ->pluck('store_number')
            ->toArray();
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
            'maintenance_type' => 'nullable|string|max:255'
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

            Payment::create($data);

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
        $payment->load(['company', 'store']);
        return view('admin.payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
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
            'maintenance_type' => 'nullable|string|max:255'
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

            DB::commit();

            return redirect()->route('payments.index')
                ->with('success', 'Payment record updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update payment: ' . $e->getMessage()]);
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
            }
            return $query;
        };

        return [
            'total'          => $applyFilters(Payment::query())->count(),
            'total_cost'     => $applyFilters(Payment::query())->sum('cost'),
            'paid'           => $applyFilters(Payment::query())->where('paid', true)->count(),
            'unpaid'         => $applyFilters(Payment::query())->where('paid', false)->count(),
            'this_month'     => $applyFilters(Payment::query())->thisMonth()->count(),
            'this_month_cost'=> $applyFilters(Payment::query())->thisMonth()->sum('cost'),
            'within_90_days' => $applyFilters(Payment::query())->within90Days()->count(),
            'unpaid_amount'  => $applyFilters(Payment::query())->where('paid', false)->sum('cost'),
            'paid_amount'    => $applyFilters(Payment::query())->where('paid', true)->sum('cost')
        ];
    }

    public function storeImageView($store)
    {
        try {
            $payments = Payment::where('store', $store)
                ->orWhereHas('store', function($q) use ($store) {
                    $q->where('store_number', $store);
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
            \Log::error('Store Image Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Keep all other report methods exactly the same...
    public function costByCompanyReport()
    {
        return view('admin.payments.reports.cost-by-company');
    }

    public function monthlyReport()
    {
        return view('admin.payments.reports.monthly-report');
    }

    public function weeklyMaintenanceReport()
    {
        return view('admin.payments.reports.weekly-maintenance');
    }

    public function costPerStoreYearlyReport()
    {
        return view('admin.payments.reports.cost-per-store-yearly');
    }

    public function pendingProjectsReport()
    {
        return view('admin.payments.reports.pending-projects');
    }
}
