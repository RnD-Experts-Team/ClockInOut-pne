<?php

namespace App\Services\Api\Admin;

use App\Imports\LeaseImport;
use App\Models\CalendarEvent;
use App\Models\CalendarReminder;
use Illuminate\Http\Request;
use App\Models\Lease;
use App\Models\Store;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class LeaseService
{
    public function getPortfolioStats(Request $request): array
    {
        $selectedStores = [];

        if ($request->has('stores') && is_array($request->stores)) {
            $selectedStores = $request->stores;
        }

        $stats = Lease::getScopedStatistics($selectedStores);

        return $stats;
    }

    public function import($file): array
    {
        try {

            DB::beginTransaction();
            $import = new LeaseImport();
            Excel::import($import, $file);
            $errors = $import->getErrors();
            if (!empty($errors)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Import completed with errors',
                    'errors' => $errors
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Leases imported successfully!'
            ];

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            DB::rollBack();

            $failures = $e->failures();
            $errorMessages = [];

            foreach ($failures as $failure) {
                $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errorMessages
            ];

        } catch (\Exception $e) {

            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Import failed',
                'errors' => [$e->getMessage()]
            ];
        }
    }
    public function downloadTemplate(): array
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
            'Comments',
            'Renewal Date',
            'Renewal Status',
            'Renewal Notes'
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
                'Sample lease data',
                '2025-12-31',
                'pending',
                'Need to review terms before renewal'
            ]
        ];

        return [
            'filename' => 'lease_import_template_with_renewals.csv',
            'data' => $csvData
        ];
    }
    public function landlordContact(): array
    {
        $leases = Lease::with('store')->get();

        return [
            'leases' => $leases
        ];
    }
    public function costBreakdown(): array
    {
        $leases = Lease::with('store')->get();

        return [
            'leases' => $leases,
        ];
    }

    public function leaseTracker(): array
    {
        $leases = Lease::with('store')->get();

        return [
            'leases' => $leases,
        ];
    }
     public function index($request)
    {
        $query = Lease::with('store', 'renewalCreatedBy');

        $baseQuery = clone $query;

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

        if ($request->has('hvac') && $request->hvac !== 'all') {
            $hvacFilter = function($q) use ($request) {
                $q->where('hvac', $request->hvac === '1');
            };

            $query->where($hvacFilter);
            $baseQuery->where($hvacFilter);
        }

        if ($request->has('expiring') && $request->expiring !== 'all') {
            $expiringFilter = function($q) use ($request) {
                if ($request->expiring === 'franchise') {
                    $q->expiringFranchiseSoon();
                } elseif ($request->expiring === 'lease') {
                    $q->expiringLeaseSoon();
                } elseif ($request->expiring === 'renewal') {
                    $q->renewalsDueSoon();
                }
            };

            $query->where($expiringFilter);
            $baseQuery->where($expiringFilter);
        }

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
                    case 'renewal_pending':
                        $q->where('renewal_status', 'pending')
                            ->whereNotNull('renewal_date');
                        break;
                    case 'renewal_overdue':
                        $q->overdueRenewals();
                        break;
                }
            };

            $query->where($statusFilter);
            $baseQuery->where($statusFilter);
        }

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

        $sortField = $request->get('sort', 'store_number');
        $sortDirection = $request->get('direction', 'asc');

        $validSortFields = [
            'store_number', 'name', 'base_rent',
            'franchise_agreement_expiration_date',
            'initial_lease_expiration_date',
            'renewal_date', 'sqf', 'created_at'
        ];

        if (in_array($sortField, $validSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('store_number', 'asc');
        }

        $leases = $query->paginate(15)->withQueryString();

        $selectedStores = [];

        if ($request->has('portfolio_stores') && is_array($request->portfolio_stores)) {
            $selectedStores = $request->portfolio_stores;
        }

        $overallStats = Lease::getScopedStatistics($selectedStores);
        $availableStores = Store::orderBy('store_number')->get();

        $stats = $this->calculateFilteredStats($baseQuery);

        return [
            'leases' => $leases,
            'stats' => $stats,
            'overallStats' => $overallStats,
            'availableStores' => $availableStores,
            'selectedStores' => $selectedStores
        ];
    }


    private function calculateFilteredStats($query)
    {
        $total = $query->count();
        $withHvac = $query->where('hvac', true)->count();
        $franchiseExpiringSoon = $query->expiringFranchiseSoon()->count();
        $leaseExpiringSoon = $query->expiringLeaseSoon()->count();
        $renewalsDueSoon = $query->renewalsDueSoon()->count();
        $overdueRenewals = $query->overdueRenewals()->count();
        $totalSqf = $query->sum('sqf');
        $totalBaseRent = $query->sum('base_rent');
        $averageRent = $total > 0 ? $totalBaseRent / $total : 0;
        $averageSqf = $total > 0 ? $totalSqf / $total : 0;

        return [
            'total' => $total,
            'with_hvac' => $withHvac,
            'franchise_expiring_soon' => $franchiseExpiringSoon,
            'lease_expiring_soon' => $leaseExpiringSoon,
            'renewals_due_soon' => $renewalsDueSoon,
            'overdue_renewals' => $overdueRenewals,
            'total_sqf' => $totalSqf,
            'total_base_rent' => $totalBaseRent,
            'average_rent' => $averageRent,
            'average_sqf' => $averageSqf,
            // Additional stats
            'active_leases' => $query->where('initial_lease_expiration_date', '>', now())->count(),
            'expired_leases' => $query->where('initial_lease_expiration_date', '<', now())->count(),
            'high_rent_count' => $query->where('base_rent', '>', 15000)->count(),
            'low_rent_count' => $query->where('base_rent', '<', 5000)->count(),
            'pending_renewals' => $query->where('renewal_status', 'pending')->whereNotNull('renewal_date')->count(),
            'completed_renewals' => $query->where('renewal_status', 'completed')->count(),
        ];
    }
    public function store(array $validated)
    {
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

            // Renewal logic
            if ($validated['renewal_date']) {
                $validated['renewal_created_by'] = Auth::id();
                $validated['renewal_status'] = $validated['renewal_status'] ?? 'pending';
            }

            unset($validated['new_store_number'], $validated['new_store_name']);
            $lease = Lease::create($validated);

            DB::commit();

            return $lease;

        } catch (\Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }
    public function show(Lease $lease): array
    {
        $lease->load('store', 'renewalCreatedBy');

        return [
            'lease' => $lease,
        ];
    }
    public function update(array $validated, Lease $lease)
    {

        try {
             DB::beginTransaction();
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

            $oldRenewalDate = $lease->renewal_date;
            $newRenewalDate = $validated['renewal_date']
                ? Carbon::parse($validated['renewal_date'])
                : null;

            if ($newRenewalDate && (!$oldRenewalDate || !$newRenewalDate->equalTo($oldRenewalDate))) {
                $validated['renewal_created_by'] = Auth::id();
                $validated['renewal_reminder_sent'] = false; // Reset reminder flag
                $validated['renewal_reminder_sent_at'] = null;
                if (!$validated['renewal_status']) {
                    $validated['renewal_status'] = 'pending';
                }
            }

            unset($validated['new_store_number'], $validated['new_store_name']);

            $lease->update($validated);

            DB::commit();

            $message = 'Lease updated successfully.';

            if ($newRenewalDate && (!$oldRenewalDate || !$newRenewalDate->equalTo($oldRenewalDate))) {
                $message .= ' Renewal reminders have been updated.';
            }

            return [
                'lease' => $lease,
                'message' => $message
            ];

        } catch (\Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }


    public function destroy(Lease $lease)
    {

        try {
            DB::beginTransaction();
            CalendarEvent::where('related_model_type', Lease::class)
                ->where('related_model_id', $lease->id)
                ->delete();

            CalendarReminder::where('related_model_type', Lease::class)
                ->where('related_model_id', $lease->id)
                ->delete();

            $lease->delete();

            DB::commit();

            return true;

        } catch (\Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }
    public function export($request)
    {
        $query = Lease::with('store', 'renewalCreatedBy');
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
            } elseif ($request->expiring === 'renewal') {
                $query->renewalsDueSoon();
            }
        }

        $leases = $query->get();

        $csvData = [];

        $csvData[] = [
            'Store Number','Store Name (from Store)','Name','Store Address','AWS','Base Rent','% Increase/Year',
            'CAM','Insurance','RE Taxes','Others','Security Deposit',
            'Franchise Expiration','Renewal Options','Current Term Override',
            'Lease Expiration','SQF','HVAC','Total Rent','Current Term','Time Left Current Term',
            'Time Left Last Term','Lease to Sales Ratio','Time Until Franchise Expires',
            'Renewal Date','Renewal Status','Renewal Notes','Renewal Created By','Days Until Renewal','Created At'
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
                $lease->percent_increase_per_year ? $lease->percent_increase_per_year.'%' : '',
                $lease->cam,
                $lease->insurance,
                $lease->re_taxes,
                $lease->others,
                $lease->security_deposit,
                $lease->franchise_agreement_expiration_date?->format('Y-m-d'),
                $lease->renewal_options,
                $lease->current_term ?? 'Auto',
                $lease->initial_lease_expiration_date?->format('Y-m-d'),
                $lease->sqf,
                $lease->hvac ? 'Yes' : 'No',
                $lease->total_rent,
                $currentTerm ? $currentTerm['term_name'] : 'N/A',
                $currentTerm ? $currentTerm['time_left']['formatted'] : 'N/A',
                $lease->time_until_last_term_ends ? $lease->time_until_last_term_ends['formatted'] : 'N/A',
                $lease->lease_to_sales_ratio ? number_format($lease->lease_to_sales_ratio * 100,2).'%' : 'N/A',
                $lease->time_until_franchise_expires ? $lease->time_until_franchise_expires['formatted'] : 'N/A',
                $lease->renewal_date ? $lease->renewal_date->format('Y-m-d') : 'Not Set',
                $lease->renewal_status ? ucfirst($lease->renewal_status) : 'N/A',
                $lease->renewal_notes ?? '',
                $lease->renewalCreatedBy ? $lease->renewalCreatedBy->name : 'N/A',
                $lease->days_until_renewal ?? 'N/A',
                $lease->created_at->format('Y-m-d H:i:s')
            ];
        }

            $file = fopen('php://temp', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            rewind($file);
            $csvContent = stream_get_contents($file);
            fclose($file);

            return $csvContent;
    }
 

    
}