<?php

namespace App\Services\Api\Admin;

use App\Models\ApartmentLease;
use App\Models\CalendarEvent;
use App\Models\CalendarReminder;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApartmentLeaseService
{
    public function exportLeases($request)
    {
        try {

            $query = ApartmentLease::with(['store','renewalCreatedBy']);

            // Search
            if ($request->filled('search')) {

                $search = $request->search;

                $query->where(function ($q) use ($search) {

                    $q->where('store_number','like',"%{$search}%")
                        ->orWhere('apartment_address','like',"%{$search}%")
                        ->orWhere('lease_holder','like',"%{$search}%")
                        ->orWhere('notes','like',"%{$search}%")
                        ->orWhereHas('store',function($q) use ($search){

                            $q->where('store_number','like',"%{$search}%")
                              ->orWhere('name','like',"%{$search}%");

                        });
                });
            }

            // Family filter
            if ($request->filled('family_filter') && $request->family_filter !== 'all') {

                if ($request->family_filter === 'yes') {
                    $query->whereIn('is_family',['Yes','yes']);
                }

                elseif ($request->family_filter === 'no') {
                    $query->whereIn('is_family',['No','no']);
                }
            }

            // Car filter
            if ($request->filled('car_filter') && $request->car_filter !== 'all') {

                if ($request->car_filter === 'with_car') {
                    $query->where('has_car','>',0);
                }

                elseif ($request->car_filter === 'no_car') {
                    $query->where('has_car',0);
                }
            }

            // Renewal filter
            if ($request->filled('lease_status') && $request->lease_status !== 'all') {

                switch ($request->lease_status) {

                    case 'renewal_pending':

                        $query->whereNotNull('renewal_date')
                              ->where('renewal_status','pending');
                        break;

                    case 'renewal_overdue':

                        $query->whereNotNull('renewal_date')
                              ->where('renewal_date','<',now()->startOfDay())
                              ->where('renewal_status','!=','completed');
                        break;

                    case 'renewal_due_soon':

                        $query->whereNotNull('renewal_date')
                              ->where('renewal_date','>=',now()->startOfDay())
                              ->where('renewal_date','<=',now()->addDays(30)->endOfDay())
                              ->where('renewal_status','!=','completed');
                        break;

                    case 'renewal_completed':

                        $query->where('renewal_status','completed');
                        break;
                }
            }

            $leases = $query->orderBy('store_number')->get();

            $csv = $this->generateCsv($leases);

            return [
                'success' => true,
                'filename' => 'apartment-leases-'.now()->format('Y-m-d-H-i-s').'.csv',
                'data' => $csv
            ];

        } catch (\Exception $e) {

            Log::error('Apartment Lease Export Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }



    private function generateCsv($leases)
    {

        $handle = fopen('php://temp','r+');

        fputcsv($handle,[
            'Store Number',
            'Store Name',
            'Apartment Address',
            'Rent',
            'Utilities',
            'Total Rent',
            'Number of AT',
            'Has Car',
            'Is Family',
            'Expiration Date',
            'Drive Time',
            'Notes',
            'Lease Holder',
            'Expiration Warning',
            'Renewal Date',
            'Renewal Status',
            'Renewal Notes',
            'Days Until Renewal',
            'Renewal Created By'
        ]);

        foreach ($leases as $lease) {

            $renewalDate = '';
            $daysUntilRenewal = '';

            if ($lease->renewal_date) {

                try {

                    $renewalDateCarbon = $lease->renewal_date instanceof Carbon
                        ? $lease->renewal_date
                        : Carbon::parse($lease->renewal_date);

                    $renewalDate = $renewalDateCarbon->format('Y-m-d');

                    $daysUntilRenewal = now()->diffInDays($renewalDateCarbon,false);

                } catch (\Exception $e) {

                    $renewalDate = 'Invalid Date';
                    $daysUntilRenewal = 'N/A';
                }
            }

            fputcsv($handle,[

                $lease->store ? $lease->store->store_number : $lease->store_number,
                $lease->store ? $lease->store->name : 'N/A',
                $lease->apartment_address,
                $lease->rent,
                $lease->utilities ?? 0,
                $lease->total_rent,
                $lease->number_of_AT,
                $lease->has_car,
                $lease->is_family ?? '',
                $lease->expiration_date ? $lease->expiration_date->format('Y-m-d') : '',
                $lease->drive_time ?? '',
                $lease->notes ?? '',
                $lease->lease_holder,
                $lease->expiration_warning ?? '',
                $renewalDate,
                $lease->renewal_status ?? '',
                $lease->renewal_notes ?? '',
                $daysUntilRenewal,
                $lease->renewalCreatedBy->name ?? ''
            ]);
        }

        rewind($handle);

        $csv = stream_get_contents($handle);

        fclose($handle);

        return $csv;
    }


    public function listLeases()
    {
        return ApartmentLease::with('store')->get();
    }

    public function getLeases($request)
    {
        $query = ApartmentLease::with('store');

        // Base query for stats
        $baseQuery = clone $query;

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $searchFilter = function ($q) use ($search) {
                $q->where('store_number', 'like', "%{$search}%")
                    ->orWhere('apartment_address', 'like', "%{$search}%")
                    ->orWhere('lease_holder', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('store', function ($q) use ($search) {
                        $q->where('store_number', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%");
                    });
            };

            $query->where($searchFilter);
            $baseQuery->where($searchFilter);
        }

        // Family filter
        if ($request->filled('family_filter') && $request->family_filter !== 'all') {

            $familyFilter = function ($q) use ($request) {
                if ($request->family_filter === 'yes') {
                    $q->whereIn('is_family', ['Yes', 'yes']);
                } elseif ($request->family_filter === 'no') {
                    $q->whereIn('is_family', ['No', 'no']);
                }
            };

            $query->where($familyFilter);
            $baseQuery->where($familyFilter);
        }

        // Car filter
        if ($request->filled('car_filter') && $request->car_filter !== 'all') {

            $carFilter = function ($q) use ($request) {
                if ($request->car_filter === 'with_car') {
                    $q->where('has_car', '>', 0);
                } elseif ($request->car_filter === 'no_car') {
                    $q->where('has_car', 0);
                }
            };

            $query->where($carFilter);
            $baseQuery->where($carFilter);
        }

        // Store filter
        if ($request->filled('store_id') && $request->store_id !== 'all') {

            $storeFilter = function ($q) use ($request) {
                $q->where('store_id', $request->store_id);
            };

            $query->where($storeFilter);
            $baseQuery->where($storeFilter);
        }

        // Renewal Status filter
        if ($request->filled('lease_status') && $request->lease_status !== 'all') {

            $renewalFilter = function ($q) use ($request) {

                switch ($request->lease_status) {

                    case 'renewal_pending':
                        $q->whereNotNull('renewal_date')
                          ->where('renewal_status', 'pending');
                        break;

                    case 'renewal_overdue':
                        $q->whereNotNull('renewal_date')
                          ->where('renewal_date', '<', now()->startOfDay())
                          ->where('renewal_status', '!=', 'completed');
                        break;

                    case 'renewal_due_soon':
                        $q->whereNotNull('renewal_date')
                          ->where('renewal_date', '>=', now()->startOfDay())
                          ->where('renewal_date', '<=', now()->addDays(30)->endOfDay())
                          ->where('renewal_status', '!=', 'completed');
                        break;

                    case 'renewal_completed':
                        $q->where('renewal_status', 'completed');
                        break;
                }
            };

            $query->where($renewalFilter);
            $baseQuery->where($renewalFilter);
        }

        // Date range filter
        if ($request->has('date_range') && $request->date_range !== 'all') {

            $dateFilter = function ($q) use ($request) {

                switch ($request->date_range) {

                    case 'expiring_this_month':
                        $q->whereMonth('expiration_date', now()->month)
                          ->whereYear('expiration_date', now()->year);
                        break;

                    case 'expiring_next_month':
                        $nextMonth = now()->addMonth();
                        $q->whereMonth('expiration_date', $nextMonth->month)
                          ->whereYear('expiration_date', $nextMonth->year);
                        break;

                    case 'expiring_3_months':
                        $q->whereBetween('expiration_date', [
                            now()->startOfDay(),
                            now()->addMonths(3)->endOfDay()
                        ]);
                        break;

                    case 'expired':
                        $q->where('expiration_date', '<', now()->startOfDay());
                        break;

                    case 'renewal':
                        $q->whereNotNull('renewal_date')
                          ->whereBetween('renewal_date', [
                              now()->startOfDay(),
                              now()->addDays(30)->endOfDay()
                          ]);
                        break;

                    case 'custom':
                        if ($request->has('start_date') && $request->has('end_date')) {
                            $q->whereBetween('expiration_date', [
                                Carbon::parse($request->start_date)->startOfDay(),
                                Carbon::parse($request->end_date)->endOfDay()
                            ]);
                        }
                        break;
                }
            };

            $query->where($dateFilter);
            $baseQuery->where($dateFilter);
        }

        $leases = $query
            ->with(['renewalCreatedBy'])
            ->orderBy('store_number')
            ->paginate(15)
            ->withQueryString();

        $stats = $this->calculateFilteredStats($baseQuery);

        $stores = Store::orderBy('store_number')->get();

        return [
            'leases' => $leases,
            'stats' => $stats,
            'stores' => $stores
        ];
    }


    private function calculateFilteredStats($query)
    {
        $total = (clone $query)->count();

        $totalMonthlyRent = (clone $query)->sum(
            DB::raw('rent + COALESCE(utilities,0)')
        );

        $families = (clone $query)
            ->whereIn('is_family', ['Yes', 'yes'])
            ->count();

        $totalCars = (clone $query)->sum('has_car');

        $totalAT = (clone $query)->sum('number_of_AT');

        $startDate = now()->startOfDay();
        $endDate = now()->addMonth()->endOfMonth();

        $expiringSoon = (clone $query)
            ->whereBetween('expiration_date', [$startDate, $endDate])
            ->count();

        $renewalsDueSoon = (clone $query)
            ->whereNotNull('renewal_date')
            ->whereBetween('renewal_date', [
                now()->startOfDay(),
                now()->addDays(30)->endOfDay()
            ])
            ->where('renewal_status', '!=', 'completed')
            ->count();

        $overdueRenewals = (clone $query)
            ->whereNotNull('renewal_date')
            ->where('renewal_date', '<', now()->startOfDay())
            ->where('renewal_status', '!=', 'completed')
            ->count();

        return [
            'total' => $total,
            'families' => $families,
            'total_cars' => $totalCars,
            'expiring_soon' => $expiringSoon,
            'total_monthly_rent' => $totalMonthlyRent,
            'average_rent' => $total > 0 ? $totalMonthlyRent / $total : 0,
            'total_at' => $totalAT,
            'average_at' => $total > 0 ? $totalAT / $total : 0,
            'occupancy_rate' => $total > 0 ? 100 : 0,
            'renewals_due_soon' => $renewalsDueSoon,
            'overdue_renewals' => $overdueRenewals,
        ];
    }

    public function createLease(array $data)
    {
        DB::beginTransaction();

        try {

            // Create store if needed
            if (!$data['store_id'] && !empty($data['new_store_number'])) {

                $store = Store::create([
                    'store_number' => $data['new_store_number'],
                    'name' => $data['new_store_name'],
                    'is_active' => true,
                ]);

                $data['store_id'] = $store->id;
                $data['store_number'] = $store->store_number;
            }

            elseif ($data['store_id']) {

                $store = Store::find($data['store_id']);
                $data['store_number'] = $store->store_number;
            }

            // Renewal info
            if (!empty($data['renewal_date'])) {

                $data['renewal_created_by'] = auth()->id();
                $data['renewal_status'] = $data['renewal_status'] ?? 'pending';
            }

            $data['created_by'] = auth()->id();

            unset(
                $data['new_store_number'],
                $data['new_store_name']
            );

            $lease = ApartmentLease::create($data);

            // Calendar + reminders
            if ($lease->renewal_date) {

                $this->createRenewalCalendarEvent($lease);
                $this->createRenewalReminders($lease);
            }

            DB::commit();

            return $lease;

        } catch (\Exception $e) {

            DB::rollback();
            throw $e;
        }
    }


      private function createRenewalCalendarEvent(ApartmentLease $apartmentLease)
    {
        try {
            // Validate required data
            if (!$apartmentLease->renewal_date) {
                Log::error('ApartmentLease createRenewalCalendarEvent: No renewal date set', [
                    'apartment_lease_id' => $apartmentLease->id
                ]);
                return null;
            }

            // Ensure renewal_date is a Carbon instance
            $renewalDate = $apartmentLease->renewal_date instanceof \Carbon\Carbon
                ? $apartmentLease->renewal_date
                : \Carbon\Carbon::parse($apartmentLease->renewal_date);

            // Create the calendar event
            $calendarEvent = CalendarEvent::create([
                'title' => 'Apartment Lease Renewal - Store ' . ($apartmentLease->store_number ?: 'N/A'),
                'description' => 'Renewal for apartment lease at ' . $apartmentLease->apartment_address .
                    '. Lease holder: ' . $apartmentLease->lease_holder,
                'event_type' => 'apartment_lease_renewal',
                'start_date' => $renewalDate->format('Y-m-d'),
                'start_time' => '09:00:00',
                'color_code' => '#dc3545', // Red color for apartment lease renewals
                'related_model_type' => ApartmentLease::class,
                'related_model_id' => $apartmentLease->id,
                'created_by' => auth()->id(),
                // Add additional fields if your CalendarEvent model supports them
                'all_day' => false,
                'is_recurring' => false,
                'status' => 'confirmed',
            ]);

            Log::info('ApartmentLease createRenewalCalendarEvent: Calendar event created successfully', [
                'apartment_lease_id' => $apartmentLease->id,
                'calendar_event_id' => $calendarEvent->id,
                'renewal_date' => $renewalDate->format('Y-m-d')
            ]);

            return $calendarEvent;

        } catch (\Exception $e) {
            Log::error('ApartmentLease createRenewalCalendarEvent: Failed to create calendar event', [
                'apartment_lease_id' => $apartmentLease->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    private function createRenewalReminders(ApartmentLease $apartmentLease)
    {
        try {
            // Validate required data
            if (!$apartmentLease->renewal_date) {
                Log::error('ApartmentLease createRenewalReminders: No renewal date set', [
                    'apartment_lease_id' => $apartmentLease->id
                ]);
                return [];
            }

            // Ensure renewal_date is a Carbon instance
            $renewalDate = $apartmentLease->renewal_date instanceof \Carbon\Carbon
                ? $apartmentLease->renewal_date
                : \Carbon\Carbon::parse($apartmentLease->renewal_date);

            // Create reminders for: 90, 60, 30, 14, 7, 1 days before renewal
            $reminderDays = [90, 60, 30, 14, 7, 1];
            $createdReminders = [];
            $now = \Carbon\Carbon::now();

            foreach ($reminderDays as $days) {
                $reminderDate = $renewalDate->copy()->subDays($days);

                // Only create if reminder date is in future
                if ($reminderDate->gt($now)) {
                    try {
                        $reminder = CalendarReminder::create([
                            'admin_user_id' => auth()->id(),
                            'title' => "Apartment Lease Renewal Due - Store " . ($apartmentLease->store_number ?: 'N/A'),
                            'description' => "Apartment lease renewal due in {$days} days on {$renewalDate->format('M j, Y')} for apartment at {$apartmentLease->apartment_address}. Lease holder: {$apartmentLease->lease_holder}",
                            'reminder_date' => $reminderDate->format('Y-m-d'),
                            'reminder_time' => '09:00',
                            'reminder_type' => 'apartment_lease_renewal',
                            'status' => 'pending',
                            'related_model_type' => ApartmentLease::class,
                            'related_model_id' => $apartmentLease->id,
                            'notification_methods' => json_encode(['browser']), // Ensure it's JSON encoded
                            // Add additional fields if your CalendarReminder model supports them
                            'is_sent' => false,
                            'priority' => $days <= 7 ? 'high' : ($days <= 30 ? 'medium' : 'normal'),
                        ]);

                        $createdReminders[] = $reminder;

                        Log::info('ApartmentLease createRenewalReminders: Reminder created', [
                            'apartment_lease_id' => $apartmentLease->id,
                            'reminder_id' => $reminder->id,
                            'days_before' => $days,
                            'reminder_date' => $reminderDate->format('Y-m-d')
                        ]);

                    } catch (\Exception $e) {
                        Log::error('ApartmentLease createRenewalReminders: Failed to create reminder', [
                            'apartment_lease_id' => $apartmentLease->id,
                            'days_before' => $days,
                            'reminder_date' => $reminderDate->format('Y-m-d'),
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    Log::info('ApartmentLease createRenewalReminders: Skipped past reminder date', [
                        'apartment_lease_id' => $apartmentLease->id,
                        'days_before' => $days,
                        'reminder_date' => $reminderDate->format('Y-m-d'),
                        'current_date' => $now->format('Y-m-d')
                    ]);
                }
            }

            Log::info('ApartmentLease createRenewalReminders: Process completed', [
                'apartment_lease_id' => $apartmentLease->id,
                'total_reminders_created' => count($createdReminders),
                'renewal_date' => $renewalDate->format('Y-m-d')
            ]);

            return $createdReminders;

        } catch (\Exception $e) {
            Log::error('ApartmentLease createRenewalReminders: Failed to create reminders', [
                'apartment_lease_id' => $apartmentLease->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
}