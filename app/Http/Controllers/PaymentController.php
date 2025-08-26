<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Company;
use App\Models\Store;
use Illuminate\Http\Request;
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

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Keep all other report methods exactly the same...
    public function costByCompanyReport()
    {
        return view('admin.payments.reports.cost-by-company');
    }

    public function monthlyReport(Request $request)
    {
        // Get available years from your payment data
        $availableYears = Payment::selectRaw('DISTINCT YEAR(date) as year')
            ->whereNotNull('date')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Use the most recent year with data, or current year
        $currentYear = now()->year;
        $targetYear = $request->get('year', $availableYears[0] ?? $currentYear);

        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthPayments = Payment::whereYear('date', $targetYear)
                ->whereMonth('date', $month)
                ->get();

            $paidAmount = $monthPayments->where('paid', true)->sum('cost');
            $totalAmount = $monthPayments->sum('cost');
            $unpaidAmount = $monthPayments->where('paid', false)->sum('cost');
            $paidPercentage = $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0;

            $monthlyData[] = [
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'paid_amount' => $paidAmount,
                'total_amount' => $totalAmount,
                'unpaid_amount' => $unpaidAmount,
                'percentage' => $paidPercentage,
                'payment_count' => $monthPayments->count()
            ];
        }

        $grandTotal = collect($monthlyData)->sum('paid_amount');
        $grandTotalAll = collect($monthlyData)->sum('total_amount');
        $avgPercentage = $grandTotalAll > 0 ? ($grandTotal / $grandTotalAll) * 100 : 0;

        return view('admin.payments.reports.monthly-report', compact(
            'monthlyData',
            'targetYear',
            'currentYear',
            'grandTotal',
            'grandTotalAll',
            'avgPercentage',
            'availableYears'
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

        // Base query
        $query = Payment::with(['company', 'store']);

        // Apply date filters (most important)
        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        // Apply other filters
        if ($companyId && $companyId !== 'all') {
            $query->where('company_id', $companyId);
        }

        if ($paid && $paid !== 'all') {
            $query->where('paid', $paid === '1');
        }

        if ($maintenanceType && $maintenanceType !== 'all') {
            $query->where('maintenance_type', $maintenanceType);
        }

        // Get filtered payments
        $filteredPayments = $query->get();

        // Debug: Log the query results
        \Log::info('Filtered payments count: ' . $filteredPayments->count());
        \Log::info('Sample payment: ', $filteredPayments->first()?->toArray() ?? ['no data']);

        // FIXED: Handle stores properly - check if store_id exists but store is null
        $stores = collect();

        foreach ($filteredPayments as $payment) {
            if ($payment->store && $payment->store->store_number) {
                // Store relationship exists and has store_number
                $stores->push($payment->store->store_number);
            } elseif ($payment->store_id) {
                // Store ID exists but relationship might be null - use ID as fallback
                $stores->push("Store-" . $payment->store_id);
            } else {
                // No store relationship - use a generic identifier
                $stores->push("Unknown Store");
            }
        }

        $stores = $stores->unique()->filter()->sort();

        // Group by store for the report
        $storeData = [];
        $totalWeeklyCost = 0;
        $totalMonthlyCost = 0;
        $totalEquipmentCost = 0;
        $totalServiceCost = 0;
        $totalFourWeeksCost = 0;
        $totalNinetyDaysCost = 0;

        foreach ($stores as $store) {
            $storePayments = $filteredPayments->filter(function($payment) use ($store) {
                if ($payment->store && is_object($payment->store)) {
                    return $payment->store->store_number === $store;
                } elseif ($payment->store_id) {
                    return "Store-" . $payment->store_id === $store;
                } else {
                    return $store === "Unknown Store";
                }
            });

            // Main filtered period cost
            $totalCost = $storePayments->sum('cost');
            $equipmentCost = $storePayments->where('maintenance_type', 'Equipment/Parts')->sum('cost');
            $serviceCost = $storePayments->where('maintenance_type', 'Service')->sum('cost');

            // Additional time period calculations
            $thisMonthPayments = $storePayments->filter(function($payment) {
                return $payment->date->isCurrentMonth();
            });

            $fourWeeksPayments = $storePayments->filter(function($payment) {
                return $payment->date >= now()->subWeeks(4);
            });

            $ninetyDaysPayments = $storePayments->filter(function($payment) {
                return $payment->date >= now()->subDays(90);
            });

            $storeData[] = [
                'store' => $store,
                'total_cost' => $totalCost,
                'equipment_cost' => $equipmentCost,
                'service_cost' => $serviceCost,
                'this_month_cost' => $thisMonthPayments->sum('cost'),
                'four_weeks_cost' => $fourWeeksPayments->sum('cost'),
                'ninety_days_cost' => $ninetyDaysPayments->sum('cost'),
            ];

            $totalWeeklyCost += $totalCost;
            $totalMonthlyCost += $thisMonthPayments->sum('cost');
            $totalEquipmentCost += $equipmentCost;
            $totalServiceCost += $serviceCost;
            $totalFourWeeksCost += $fourWeeksPayments->sum('cost');
            $totalNinetyDaysCost += $ninetyDaysPayments->sum('cost');
        }

        // FIXED: Set week and year based on filter dates, not current
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
            'stores',
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





    public function costPerStoreYearlyReport()
    {
        return view('admin.payments.reports.cost-per-store-yearly');
    }

    public function pendingProjectsReport()
    {
        return view('admin.payments.reports.pending-projects');
    }
}
