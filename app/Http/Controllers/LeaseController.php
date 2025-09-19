<?php
// app/Http/Controllers/LeaseController.php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Imports\LeaseImport;
use Maatwebsite\Excel\Facades\Excel;

class LeaseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Lease::with('store');
        // Create a base query for stats calculation
        $baseQuery = clone $query;

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $searchFilter = function($q) use ($search) {
                $q->where('store_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('store_address', 'like', "%{$search}%")
                    ->orWhereHas('store', function($q) use ($search) {
                        $q->where('store_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            };

            $query->where($searchFilter);
            $baseQuery->where($searchFilter);
        }

        // Filter by HVAC
        if ($request->has('hvac') && $request->hvac !== 'all') {
            $hvacFilter = function($q) use ($request) {
                $q->where('hvac', $request->hvac === '1');
            };

            $query->where($hvacFilter);
            $baseQuery->where($hvacFilter);
        }

        // Filter by expiring soon
        if ($request->has('expiring') && $request->expiring !== 'all') {
            $expiringFilter = function($q) use ($request) {
                if ($request->expiring === 'franchise') {
                    $q->expiringFranchiseSoon();
                } elseif ($request->expiring === 'lease') {
                    $q->expiringLeaseSoon();
                }
            };

            $query->where($expiringFilter);
            $baseQuery->where($expiringFilter);
        }

        // Additional filters
        if ($request->has('lease_status') && $request->lease_status !== 'all') {
            $statusFilter = function($q) use ($request) {
                switch ($request->lease_status) {
                    case 'active':
                        $q->where('initial_lease_expiration_date', '>', now());
                        break;
                    case 'expiring_soon':
                        $q->whereBetween('initial_lease_expiration_date', [now(), now()->addMonths(6)]);
                        break;
                    case 'expired':
                        $q->where('initial_lease_expiration_date', '<', now());
                        break;
                }
            };

            $query->where($statusFilter);
            $baseQuery->where($statusFilter);
        }

        // Rent range filter
        if ($request->has('rent_range') && $request->rent_range !== 'all') {
            $rentFilter = function($q) use ($request) {
                switch ($request->rent_range) {
                    case 'low':
                        $q->where('base_rent', '<', 5000);
                        break;
                    case 'medium':
                        $q->whereBetween('base_rent', [5000, 15000]);
                        break;
                    case 'high':
                        $q->where('base_rent', '>', 15000);
                        break;
                }
            };

            $query->where($rentFilter);
            $baseQuery->where($rentFilter);
        }

        // Sort
        $sortField = $request->get('sort', 'store_number');
        $sortDirection = $request->get('direction', 'asc');

        $validSortFields = [
            'store_number', 'name', 'base_rent', 'franchise_agreement_expiration_date',
            'initial_lease_expiration_date', 'sqf', 'created_at'
        ];

        if (in_array($sortField, $validSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('store_number', 'asc');
        }

        $leases = $query->paginate(15)->withQueryString();

        // Get selected stores for portfolio statistics
        $selectedStores = [];
        if ($request->has('portfolio_stores') && is_array($request->portfolio_stores)) {
            $selectedStores = $request->portfolio_stores;
        }

        $overallStats = Lease::getScopedStatistics($selectedStores);
        $availableStores = Store::orderBy('store_number')->get();

        // Calculate filtered stats using baseQuery
        $stats = $this->calculateFilteredStats($baseQuery);

        return view('admin.leases.index', compact(
            'leases',
            'stats',
            'overallStats',
            'availableStores',
            'selectedStores'
        ));
    }

    /**
     * Calculate statistics based on filtered query
     */
    private function calculateFilteredStats($query)
    {
        $total = $query->count();
        $withHvac = $query->where('hvac', true)->count();
        $franchiseExpiringSoon = $query->expiringFranchiseSoon()->count();
        $leaseExpiringSoon = $query->expiringLeaseSoon()->count();
        $totalSqf = $query->sum('sqf');
        $totalBaseRent = $query->sum('base_rent');
        $averageRent = $total > 0 ? $totalBaseRent / $total : 0;
        $averageSqf = $total > 0 ? $totalSqf / $total : 0;

        return [
            'total' => $total,
            'with_hvac' => $withHvac,
            'franchise_expiring_soon' => $franchiseExpiringSoon,
            'lease_expiring_soon' => $leaseExpiringSoon,
            'total_sqf' => $totalSqf,
            'total_base_rent' => $totalBaseRent,
            'average_rent' => $averageRent,
            'average_sqf' => $averageSqf,
            // Additional stats
            'active_leases' => $query->where('initial_lease_expiration_date', '>', now())->count(),
            'expired_leases' => $query->where('initial_lease_expiration_date', '<', now())->count(),
            'high_rent_count' => $query->where('base_rent', '>', 15000)->count(),
            'low_rent_count' => $query->where('base_rent', '<', 5000)->count(),
        ];
    }


    public function create(): View
    {
        $stores = Store::orderBy('store_number')->get();
        return view('admin.leases.create', compact('stores'));
    }

    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'store_id' => 'nullable|exists:stores,id',
            'new_store_number' => 'nullable|string|max:255',
            'new_store_name' => 'nullable|string|max:255',
            'store_number' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'store_address' => 'nullable|string',
            'aws' => 'nullable|numeric|min:0',
            'base_rent' => 'nullable|numeric|min:0',
            'percent_increase_per_year' => 'nullable|numeric|min:0|max:100',
            'cam' => 'nullable|numeric|min:0',
            'insurance' => 'nullable|numeric|min:0',
            're_taxes' => 'nullable|numeric|min:0',
            'others' => 'nullable|numeric|min:0',
            'current_term' => 'nullable|integer|min:1|max:10', // NEW VALIDATION RULE
            'security_deposit' => 'nullable|numeric|min:0',
            'franchise_agreement_expiration_date' => 'nullable|date',
            'renewal_options' => 'nullable|string|max:255',
            'initial_lease_expiration_date' => 'nullable|date',
            'sqf' => 'nullable|integer|min:0',
            'hvac' => 'nullable|boolean',
            'landlord_responsibility' => 'nullable|string',
            'landlord_name' => 'nullable|string|max:255',
            'landlord_email' => 'nullable|email|max:255',
            'landlord_phone' => 'nullable|string|max:255',
            'landlord_address' => 'nullable|string',
            'comments' => 'nullable|string'
        ]);
        DB::beginTransaction();
        try {
            // Handle store creation if needed
            if (!$validated['store_id'] && $validated['new_store_number']) {
                $store = Store::create([
                    'store_number' => $validated['new_store_number'],
                    'name' => $validated['new_store_name'],
                    'is_active' => true,
                ]);
                $validated['store_id'] = $store->id;
                $validated['store_number'] = $store->store_number;
            } elseif ($validated['store_id']) {
                $store = Store::find($validated['store_id']);
                $validated['store_number'] = $store->store_number;
            }

            unset($validated['new_store_number'], $validated['new_store_name']);
            Lease::create($validated);

            DB::commit();

            return redirect()->route('leases.index')
                ->with('success', 'Lease created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create lease: ' . $e->getMessage()]);
        }
    }

    public function show(Lease $lease): View
    {
        $lease->load('store');
        return view('admin.leases.show', compact('lease'));
    }

    public function edit(Lease $lease): View
    {
        $stores = Store::orderBy('store_number')->get();
        return view('admin.leases.edit', compact('lease', 'stores'));
    }

    public function update(Request $request, Lease $lease): RedirectResponse
    {
        $validated = $request->validate([
            'store_id' => 'nullable|exists:stores,id',
            'new_store_number' => 'nullable|string|max:255',
            'new_store_name' => 'nullable|string|max:255',
            'store_number' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'store_address' => 'nullable|string',
            'aws' => 'nullable|numeric|min:0',
            'base_rent' => 'nullable|numeric|min:0',
            'percent_increase_per_year' => 'nullable|numeric|min:0|max:100',
            'cam' => 'nullable|numeric|min:0',
            'insurance' => 'nullable|numeric|min:0',
            're_taxes' => 'nullable|numeric|min:0',
            'current_term' => 'nullable|integer|min:1|max:10', // NEW VALIDATION RULE

            'others' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'franchise_agreement_expiration_date' => 'nullable|date',
            'renewal_options' => 'nullable|string|max:255',
            'initial_lease_expiration_date' => 'nullable|date',
            'sqf' => 'nullable|integer|min:0',
            'hvac' => 'nullable|boolean',
            'landlord_responsibility' => 'nullable|string',
            'landlord_name' => 'nullable|string|max:255',
            'landlord_email' => 'nullable|email|max:255',
            'landlord_phone' => 'nullable|string|max:255',
            'landlord_address' => 'nullable|string',
            'comments' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Handle store creation if needed
            if (!$validated['store_id'] && $validated['new_store_number']) {
                $store = Store::create([
                    'store_number' => $validated['new_store_number'],
                    'name' => $validated['new_store_name'],
                    'is_active' => true,
                ]);
                $validated['store_id'] = $store->id;
                $validated['store_number'] = $store->store_number;
            } elseif ($validated['store_id']) {
                $store = Store::find($validated['store_id']);
                $validated['store_number'] = $store->store_number;
            }

            unset($validated['new_store_number'], $validated['new_store_name']);

            $lease->update($validated);

            DB::commit();

            return redirect()->route('leases.show', $lease)
                ->with('success', 'Lease updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update lease: ' . $e->getMessage()]);
        }
    }

    // Keep all other methods the same as in your original file...
    public function destroy(Lease $lease): RedirectResponse
    {
        $lease->delete();

        return redirect()->route('leases.index')
            ->with('success', 'Lease deleted successfully.');
    }

    public function getPortfolioStats(Request $request)
    {
        $selectedStores = [];
        if ($request->has('stores') && is_array($request->stores)) {
            $selectedStores = $request->stores;
        }

        $stats = Lease::getScopedStatistics($selectedStores);

        return response()->json($stats);
    }

    // Keep all export, import, and other methods exactly the same...
    public function export(Request $request)
    {
        // Keep the same implementation as in your original file
        $query = Lease::with('store');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('store_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('store_address', 'like', "%{$search}%")
                    ->orWhereHas('store', function($q) use ($search) {
                        $q->where('store_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('hvac') && $request->hvac !== 'all') {
            $query->where('hvac', $request->hvac === '1');
        }

        if ($request->has('expiring') && $request->expiring !== 'all') {
            if ($request->expiring === 'franchise') {
                $query->expiringFranchiseSoon();
            } elseif ($request->expiring === 'lease') {
                $query->expiringLeaseSoon();
            }
        }

        $leases = $query->get();

        $csvData = [];
        $csvData[] = [
            'Store Number', 'Store Name (from Store)', 'Name', 'Store Address', 'AWS', 'Base Rent', '% Increase/Year',
            'CAM', 'Insurance', 'RE Taxes', 'Others', 'Security Deposit',
            'Franchise Expiration', 'Renewal Options', 'Current Term Override', // NEW HEADER
            'Lease Expiration', 'SQF', 'HVAC', 'Total Rent', 'Current Term', 'Time Left Current Term',
            'Time Left Last Term', 'Lease to Sales Ratio', 'Time Until Franchise Expires',
            'Created At'
        ];

        foreach ($leases as $lease) {
            $currentTerm = $lease->current_term_info;

            $csvData[] = [
                $lease->store_number,
                $lease->store ? $lease->store->name : 'N/A',
                $lease->name,
                $lease->store_address,
                $lease->aws,
                $lease->base_rent,
                $lease->percent_increase_per_year ? $lease->percent_increase_per_year . '%' : '',
                $lease->cam,
                $lease->insurance,
                $lease->re_taxes,
                $lease->others,
                $lease->security_deposit,
                $lease->franchise_agreement_expiration_date?->format('Y-m-d'),
                $lease->renewal_options,
                $lease->current_term ?? 'Auto', // NEW DATA FIELD
                $lease->initial_lease_expiration_date?->format('Y-m-d'),
                $lease->sqf,
                $lease->hvac ? 'Yes' : 'No',
                $lease->total_rent,
                $currentTerm ? $currentTerm['term_name'] : 'N/A',
                $currentTerm ? $currentTerm['time_left']['formatted'] : 'N/A',
                $lease->time_until_last_term_ends ? $lease->time_until_last_term_ends['formatted'] : 'N/A',
                $lease->lease_to_sales_ratio ? number_format($lease->lease_to_sales_ratio * 100, 2) . '%' : 'N/A',
                $lease->time_until_franchise_expires ? $lease->time_until_franchise_expires['formatted'] : 'N/A',
                $lease->created_at->format('Y-m-d H:i:s')
            ];
        }

        $filename = 'leases_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Keep all other methods exactly the same...
    public function showImport(): View
    {
        return view('admin.leases.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $import = new LeaseImport();
            Excel::import($import, $request->file('import_file'));

            $errors = $import->getErrors();

            if (!empty($errors)) {
                DB::rollback();
                return back()->withErrors([
                    'import' => 'Import completed with errors: ' . implode(', ', $errors)
                ])->withInput();
            }

            DB::commit();

            return redirect()->route('leases.index')
                ->with('success', 'Leases imported successfully!');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollback();

            $failures = $e->failures();
            $errorMessages = [];

            foreach ($failures as $failure) {
                $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return back()->withErrors([
                'import' => 'Validation failed: ' . implode(' | ', $errorMessages)
            ])->withInput();

        } catch (\Exception $e) {
            DB::rollback();

            return back()->withErrors([
                'import' => 'Import failed: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function downloadTemplate()
    {
        // Keep exactly the same as in your original file
        $headers = [
            'Store #',
            'Known as',
            'Store Address',
            'AWS',
            'Base Rent',
            '% Increase Per Year',
            'CAM',
            'Insurance',
            'RE Taxes',
            'Others',
            'Security Deposit',
            'Date Franchise agreament expiration date2',
            'Renewal options[Terms,years]',
            'Initial lease expiration date',
            'SQF',
            'HVAC',
            'Landlord responsibility',
            'Landlord Name',
            'Email & Phone',
            'Address',
            'Comments'
        ];

        $csvData = [
            $headers,
            [
                '1',
                '1 Chatterton - CMH',
                '5611 Chatterton Road, Columbus, Ohio 43232',
                '37201.71',
                '1575.84',
                '3.5',
                '280.00',
                '150.00',
                '191.16',
                '100.00',
                '2000.00',
                '12/8/2029',
                '3,5',
                '2/28/2015',
                '1176',
                'No',
                'Responsible for structural elements (roof, foundation, exterior walls) and systems that serve multiple tenants, as well as maintaining common areas and major repairs.',
                'Dembena, LLC',
                'contact@dembena.com | (555) 123-4567',
                '12591 Wheaton Avenue NW, Pickerington, Ohio 43147',
                'Sample lease data'
            ]
        ];

        $filename = 'lease_import_template.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function landlordContact()
    {
        $leases = Lease::with('store')->get();
        return view('admin.leases.landlord-contact', compact('leases'));
    }

    public function costBreakdown()
    {
        $leases = Lease::with('store')->get();
        return view('admin.leases.cost-breakdown', compact('leases'));
    }

    public function leaseTracker()
    {
        $leases = Lease::with('store')->get();
        return view('admin.leases.lease-tracker', compact('leases'));
    }
}
