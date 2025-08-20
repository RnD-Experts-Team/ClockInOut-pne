<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Load companies with payment statistics
        $companies = $query->withCount('payments')
            ->withSum('payments', 'cost')
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->query());

        // Calculate statistics
        $stats = [
            'total' => Company::count(),
            'active' => Company::where('is_active', true)->count(),
            'inactive' => Company::where('is_active', false)->count(),
            'total_payments' => \App\Models\Payment::count(),
            'total_amount' => \App\Models\Payment::sum('cost') ?? 0,
        ];

        return view('admin.companies.index', compact('companies', 'stats'));
    }


    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:companies,email',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        Company::create($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Company created successfully!');
    }


    public function show(Company $company)
    {
        $company->load(['payments' => function($q) {
            $q->with('company')->orderBy('date', 'desc');
        }]);

        $stats = [
            'total_payments' => $company->payments->count(),
            'total_amount' => $company->payments->sum('cost'),
            'paid_amount' => $company->payments->where('paid', true)->sum('cost'),
            'unpaid_amount' => $company->payments->where('paid', false)->sum('cost'),
            'avg_payment' => $company->payments->count() > 0 ? $company->payments->avg('cost') : 0,
            'recent_payment' => $company->payments->first(),
            'oldest_payment' => $company->payments->sortBy('date')->first()
        ];

        return view('admin.companies.show', compact('company', 'stats'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:companies,email,' . $company->id,
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        $company->update($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Company updated successfully!');
    }

    public function destroy(Company $company)
    {
        if ($company->payments()->count() > 0) {
            return redirect()->route('companies.index')
                ->with('error', 'Cannot delete company with existing payment records.');
        }

        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = Company::withCount('payments')
            ->withSum('payments', 'cost');

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $companies = $query->orderBy('name')->get();

        $filename = 'companies-export-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($companies) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'Company Name',
                'Contact Person',
                'Phone',
                'Email',
                'Address',
                'Total Payments',
                'Total Amount',
                'Created Date'
            ]);

            // CSV Data
            foreach ($companies as $company) {
                fputcsv($file, [
                    $company->name,
                    $company->contact_person,
                    $company->phone,
                    $company->email,
                    $company->address,
                    $company->payments_count,
                    $company->payments_sum_cost ?? 0,
                    $company->created_at ? $company->created_at->format('Y-m-d') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function calculateStats($request = null)
    {
        $baseQuery = Company::query();

        // Apply same filters as main query if request provided
        if ($request && $request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return [
            'total' => $baseQuery->count(),
            'active' => $baseQuery->has('payments')->count(),
            'inactive' => $baseQuery->doesntHave('payments')->count(),
            'total_payments' => DB::table('payments')->count(),
            'total_amount' => DB::table('payments')->sum('cost')
        ];
    }
}
