<?php

namespace App\Services\Api\Admin;
use App\Models\Company;
use App\Models\Payment;
 

class CompanyService
{
    public function index($request)
    {
        $query = Company::query();

        // Create a base query for stats calculation
        $baseQuery = clone $query;

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;

            $searchFilter = function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            };

            $query->where($searchFilter);
            $baseQuery->where($searchFilter);
        }

        // Apply status filter
        if ($request->filled('is_active') && $request->is_active !== 'all') {

            $statusFilter = function ($q) use ($request) {
                $q->where('is_active', $request->is_active);
            };

            $query->where($statusFilter);
            $baseQuery->where($statusFilter);
        }

        // Additional filters
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $paymentFilter = function ($q) use ($request) {
                switch ($request->payment_status) {
                    case 'with_payments':
                        $q->has('payments');
                        break;
                    case 'without_payments':
                        $q->doesntHave('payments');
                        break;
                    case 'recent_payments':
                        $q->whereHas('payments', function ($subQ) {
                            $subQ->where('created_at', '>=', now()->subDays(30));
                        });
                        break;
                }

            };

            $query->where($paymentFilter);
            $baseQuery->where($paymentFilter);
        }

        // Payment amount range filter
        if ($request->filled('payment_range') && $request->payment_range !== 'all') {

            $paymentRangeFilter = function ($q) use ($request) {

                switch ($request->payment_range) {

                    case 'low':
                        $q->whereHas('payments', function ($subQ) {
                            $subQ->havingRaw('SUM(cost) < 1000');
                        });
                        break;

                    case 'medium':
                        $q->whereHas('payments', function ($subQ) {
                            $subQ->havingRaw('SUM(cost) BETWEEN 1000 AND 10000');
                        });
                        break;

                    case 'high':
                        $q->whereHas('payments', function ($subQ) {
                            $subQ->havingRaw('SUM(cost) > 10000');
                        });
                        break;
                }

            };

            $query->where($paymentRangeFilter);
            $baseQuery->where($paymentRangeFilter);
        }

        // Load companies with payment statistics
        $companies = $query->withCount('payments')
            ->withSum('payments', 'cost')
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->query());

        // Calculate filtered stats using baseQuery
        $stats = $this->calculateFilteredStats($baseQuery);

        return [
            'companies' => $companies,
            'stats' => $stats
        ];
    }


    

    public function calculateFilteredStats($query)
    {
        $total = $query->count();
        $active = $query->where('is_active', true)->count();
        $inactive = $query->where('is_active', false)->count();

        $companyIds = $query->pluck('id');

        $totalPayments = Payment::whereIn('company_id',$companyIds)->count();
        $totalAmount = Payment::whereIn('company_id',$companyIds)->sum('cost') ?? 0;
        $paidAmount = Payment::whereIn('company_id',$companyIds)->where('paid',true)->sum('cost') ?? 0;
        $unpaidAmount = Payment::whereIn('company_id',$companyIds)->where('paid',false)->sum('cost') ?? 0;

        $averagePaymentPerCompany = $total > 0 ? $totalAmount / $total : 0;

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'total_payments' => $totalPayments,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'unpaid_amount' => $unpaidAmount,
            'average_payment_per_company' => $averagePaymentPerCompany,
            'companies_with_payments' => $query->has('payments')->count(),
            'companies_without_payments' => $query->doesntHave('payments')->count(),
            'recent_active_companies' => $query->whereHas('payments',function($q){
                $q->where('created_at','>=',now()->subDays(30));
            })->count(),
        ];
    }


    public function store(array $data)
    {
        return Company::create($data);
    }


    public function show(Company $company)
    {
        $company->load(['payments'=>function($q){
            $q->with('company')->orderBy('date','desc');
        }]);

        $stats = [
            'total_payments'=>$company->payments->count(),
            'total_amount'=>$company->payments->sum('cost'),
            'paid_amount'=>$company->payments->where('paid',true)->sum('cost'),
            'unpaid_amount'=>$company->payments->where('paid',false)->sum('cost'),
            'avg_payment'=>$company->payments->count()>0 ? $company->payments->avg('cost') : 0,
            'recent_payment'=>$company->payments->first(),
            'oldest_payment'=>$company->payments->sortBy('date')->first()
        ];

        return [
            'company'=>$company,
            'stats'=>$stats
        ];
    }


    public function update(Company $company,array $data)
    {
        $company->update($data);
        return $company;
    }


    public function destroy(Company $company)
    {
        if ($company->payments()->count()>0) {
            throw new \Exception('Cannot delete company with existing payment records.');
        }

        $company->delete();
    }
    public function export($request)
    {
        $query = Company::withCount('payments')
            ->withSum('payments', 'cost');

        // Apply same filters as index
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");

            });
        }

        $companies = $query->orderBy('name')->get();

        $filename = 'companies-export-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $file = fopen('php://temp', 'w');

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

        rewind($file);

        $csvContent = stream_get_contents($file);

        fclose($file);

        return [
            'filename' => $filename,
            'content' => base64_encode($csvContent)
        ];
    }

}