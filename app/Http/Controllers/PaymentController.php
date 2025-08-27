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
    public function costByCompanyReport(Request $request)
    {
        // Get filters from index page
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $companyId = $request->get('company_id');
        $search = $request->get('search');
        $maintenanceType = $request->get('maintenance_type');
        $paid = $request->get('paid');

        // FIXED: Build base query with proper filters
        $paymentsQuery = Payment::with('company');

        // Apply date filters
        if ($dateFrom && $dateTo) {
            $paymentsQuery->whereBetween('date', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ]);
        } elseif ($dateFrom) {
            $paymentsQuery->whereDate('date', '>=', $dateFrom);
        } elseif ($dateTo) {
            $paymentsQuery->whereDate('date', '<=', $dateTo);
        } else {
            // Default to current month if no date filters
            $paymentsQuery->whereMonth('date', now()->month)
                ->whereYear('date', now()->year);
        }

        // Apply company filter
        if ($companyId && $companyId !== 'all') {
            $paymentsQuery->where('company_id', $companyId);
        }

        // Apply search filter
        if ($search) {
            $paymentsQuery->where(function($q) use ($search) {
                $q->where('store', 'like', "%{$search}%")
                    ->orWhere('what_got_fixed', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('company', function($companyQuery) use ($search) {
                        $companyQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply maintenance type filter
        if ($maintenanceType && $maintenanceType !== 'all') {
            $paymentsQuery->where('maintenance_type', $maintenanceType);
        }

        // Apply payment status filter
        if ($paid && $paid !== 'all') {
            $paymentsQuery->where('paid', $paid === '1');
        }

        // FIXED: Get companies with aggregated data using query builder
        $companies = DB::table('companies as c')
            ->leftJoin('payments as p', 'c.id', '=', 'p.company_id')
            ->when($dateFrom && $dateTo, function($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('p.date', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay()
                ]);
            })
            ->when($dateFrom && !$dateTo, function($query) use ($dateFrom) {
                $query->whereDate('p.date', '>=', $dateFrom);
            })
            ->when($dateTo && !$dateFrom, function($query) use ($dateTo) {
                $query->whereDate('p.date', '<=', $dateTo);
            })
            ->when(!$dateFrom && !$dateTo, function($query) {
                $query->whereMonth('p.date', now()->month)
                    ->whereYear('p.date', now()->year);
            })
            ->when($companyId && $companyId !== 'all', function($query) use ($companyId) {
                $query->where('c.id', $companyId);
            })
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('p.store', 'like', "%{$search}%")
                        ->orWhere('p.what_got_fixed', 'like', "%{$search}%")
                        ->orWhere('p.notes', 'like', "%{$search}%")
                        ->orWhere('c.name', 'like', "%{$search}%");
                });
            })
            ->when($maintenanceType && $maintenanceType !== 'all', function($query) use ($maintenanceType) {
                $query->where('p.maintenance_type', $maintenanceType);
            })
            ->when($paid && $paid !== 'all', function($query) use ($paid) {
                $query->where('p.paid', $paid === '1');
            })
            ->select([
                'c.id',
                'c.name',
                DB::raw('COALESCE(SUM(p.cost), 0) as total_cost'),
                DB::raw('COALESCE(SUM(CASE WHEN p.paid = 1 THEN p.cost ELSE 0 END), 0) as paid_cost'),
                DB::raw('COALESCE(SUM(CASE WHEN p.paid = 0 THEN p.cost ELSE 0 END), 0) as unpaid_cost'),
                DB::raw('COUNT(p.id) as payment_count')
            ])
            ->groupBy('c.id', 'c.name')
            ->orderBy('total_cost', 'desc')
            ->get();

        // FIXED: Calculate 90-day costs separately for each company
        $companiesWithNinetyDays = collect($companies)->map(function($company) use ($dateFrom, $dateTo) {
            $ninetyDayQuery = Payment::where('company_id', $company->id);

            if ($dateFrom && $dateTo) {
                // Use the same date range as the main filter
                $ninetyDayQuery->whereBetween('date', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay()
                ]);
            } else {
                // Default 90 days
                $ninetyDayQuery->where('date', '>=', now()->subDays(90));
            }

            $ninetyDayCost = $ninetyDayQuery->sum('cost');

            return (object) [
                'id' => $company->id,
                'name' => $company->name,
                'ninety_day_cost' => $ninetyDayCost,
                'payments' => collect([]) // Add empty collection for compatibility
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
            ->selectRaw('
            COALESCE(store, CONCAT("Store ", store_id)) as store_display,
            SUM(cost) as total_cost,
            COUNT(*) as payment_count,
            AVG(cost) as avg_cost
        ')
            ->whereNotNull('cost')
            ->where('cost', '>', 0)
            ->groupByRaw('COALESCE(store, CONCAT("Store ", store_id))')
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
