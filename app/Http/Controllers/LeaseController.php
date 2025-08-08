<?php
// app/Http/Controllers/LeaseController.php

namespace App\Http\Controllers;

use App\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Imports\LeaseImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;

class LeaseController extends Controller
{
    public function index(Request $request): View
{
    $query = Lease::query();

    // Search functionality
    if ($request->has('search') && $request->search) {
        $query->search($request->search);
    }

    // Filter by HVAC
    if ($request->has('hvac') && $request->hvac !== 'all') {
        $query->where('hvac', $request->hvac === '1');
    }

    // Filter by expiring soon
    if ($request->has('expiring') && $request->expiring !== 'all') {
        if ($request->expiring === 'franchise') {
            $query->expiringFranchiseSoon();
        } elseif ($request->expiring === 'lease') {
            $query->expiringLeaseSoon();
        }
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

    // Get scoped statistics based on selected stores
    $overallStats = Lease::getScopedStatistics($selectedStores);

    // Get all available stores for the dropdown
    $availableStores = Lease::getAllStoreNumbers();

    // Basic statistics (these remain unscoped for general overview)
    $stats = [
        'total' => Lease::count(),
        'with_hvac' => Lease::where('hvac', true)->count(),
        'franchise_expiring_soon' => Lease::expiringFranchiseSoon()->count(),
        'lease_expiring_soon' => Lease::expiringLeaseSoon()->count(),
        'total_sqf' => Lease::sum('sqf'),
    ];

    return view('admin.leases.index', compact(
        'leases', 
        'stats', 
        'overallStats', 
        'availableStores', 
        'selectedStores'
    ));
}

// Add a dedicated method for AJAX portfolio updates
public function getPortfolioStats(Request $request)
{
    $selectedStores = [];
    if ($request->has('stores') && is_array($request->stores)) {
        $selectedStores = $request->stores;
    }

    $stats = Lease::getScopedStatistics($selectedStores);
    
    return response()->json($stats);
}
    public function create(): View
    {
        return view('admin.leases.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_number' => 'nullable|string|max:255|unique:leases,store_number',
            'name' => 'nullable|string|max:255',
            'store_address' => 'nullable|string',
            'aws' => 'nullable|numeric|min:0',
            'base_rent' => 'nullable|numeric|min:0',
            'percent_increase_per_year' => 'nullable|numeric|min:0|max:100',
            'cam' => 'nullable|numeric|min:0',
            'insurance' => 'nullable|numeric|min:0',
            're_taxes' => 'nullable|numeric|min:0',
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

        Lease::create($validated);

        return redirect()->route('leases.index')
                        ->with('success', 'Lease created successfully.');
    }

    public function show(Lease $lease): View
    {
        return view('admin.leases.show', compact('lease'));
    }

    public function edit(Lease $lease): View
    {
        return view('admin.leases.edit', compact('lease'));
    }

    public function update(Request $request, Lease $lease): RedirectResponse
    {
        $validated = $request->validate([
            'store_number' => 'nullable|string|max:255|unique:leases,store_number,' . $lease->id,
            'name' => 'nullable|string|max:255',
            'store_address' => 'nullable|string',
            'aws' => 'nullable|numeric|min:0',
            'base_rent' => 'nullable|numeric|min:0',
            'percent_increase_per_year' => 'nullable|numeric|min:0|max:100',
            'cam' => 'nullable|numeric|min:0',
            'insurance' => 'nullable|numeric|min:0',
            're_taxes' => 'nullable|numeric|min:0',
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

        $lease->update($validated);

        return redirect()->route('leases.show', $lease)
                        ->with('success', 'Lease updated successfully.');
    }

    public function destroy(Lease $lease): RedirectResponse
    {
        $lease->delete();

        return redirect()->route('leases.index')
                        ->with('success', 'Lease deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = Lease::query();

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
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
            'Store Number', 'Name', 'Store Address', 'AWS', 'Base Rent', '% Increase/Year',
            'CAM', 'Insurance', 'RE Taxes', 'Others', 'Security Deposit',
            'Franchise Expiration', 'Renewal Options', 'Lease Expiration', 'SQF',
            'HVAC', 'Total Rent', 'Current Term', 'Time Left Current Term',
            'Time Left Last Term', 'Lease to Sales Ratio', 'Time Until Franchise Expires',
            'Created At'
        ];

        foreach ($leases as $lease) {
            $currentTerm = $lease->current_term_info;
            
            $csvData[] = [
                $lease->store_number,
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
    public function showImport(): View
{
    return view('admin.leases.import');
}

public function import(Request $request): RedirectResponse
{
    $request->validate([
        'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
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
}
