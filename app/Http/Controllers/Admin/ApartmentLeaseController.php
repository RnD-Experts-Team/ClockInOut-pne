<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ApartmentLeasesImport;
use App\Models\ApartmentLease;
use App\Models\Store;
use App\Models\CalendarEvent;
use App\Models\CalendarReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class ApartmentLeaseController extends Controller
{
    public function index(Request $request)
    {
        $query = ApartmentLease::with('store');

        // Create a base query for stats calculation
        $baseQuery = clone $query;

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $searchFilter = function ($q) use ($search) {
                $q->where('store_number', 'like', "%{$search}%")
                    ->orWhere('apartment_address', 'like', "%{$search}%")
                    ->orWhere('lease_holder', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('store', function($q) use ($search) {
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
                    $q->where('has_car', '=', 0);
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

        // NEW: Renewal Status Filter
        if ($request->filled('lease_status') && $request->lease_status !== 'all') {
            $renewalFilter = function($q) use ($request) {
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

        // Date range filter for expiration dates
        if ($request->has('date_range') && $request->date_range !== 'all') {
            $dateFilter = function($q) use ($request) {
                switch ($request->date_range) {
                    case 'expiring_this_month':
                        $q->whereMonth('expiration_date', Carbon::now()->month)
                            ->whereYear('expiration_date', Carbon::now()->year);
                        break;
                    case 'expiring_next_month':
                        $nextMonth = Carbon::now()->addMonth();
                        $q->whereMonth('expiration_date', $nextMonth->month)
                            ->whereYear('expiration_date', $nextMonth->year);
                        break;
                    case 'expiring_3_months':
                        $q->whereBetween('expiration_date', [
                            Carbon::now()->startOfDay(),
                            Carbon::now()->addMonths(3)->endOfDay()
                        ]);
                        break;
                    case 'expired':
                        $q->where('expiration_date', '<', Carbon::now()->startOfDay());
                        break;
                    case 'renewal':
                        $q->whereNotNull('renewal_date')
                            ->whereBetween('renewal_date', [
                                Carbon::now()->startOfDay(),
                                Carbon::now()->addDays(30)->endOfDay()
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

        // Load renewal relationships
        $leases = $query->with(['renewalCreatedBy'])
            ->orderBy('store_number')
            ->paginate(15)
            ->withQueryString();

        // Calculate filtered stats using baseQuery
        $stats = $this->calculateFilteredStats($baseQuery);

        $stores = Store::orderBy('store_number')->get();

        return view('admin.apartment-leases.index', compact('leases', 'stats', 'stores'));
    }

    private function calculateFilteredStats($query)
    {
        $total = (clone $query)->count();
        $totalMonthlyRent = (clone $query)->sum(DB::raw('rent + COALESCE(utilities, 0)'));
        $families = (clone $query)->whereIn('is_family', ['Yes', 'yes'])->count();
        $totalCars = (clone $query)->sum('has_car');
        $totalAT = (clone $query)->sum('number_of_AT');

        // Fix the date range calculation
        $startDate = now()->startOfDay();
        $endDate = now()->addMonth()->endOfMonth(); // End of next month instead of exact 1 month

        $expiringSoon = (clone $query)->whereBetween('expiration_date', [$startDate, $endDate])->count();

        // NEW: Renewal statistics
        $renewalsDueSoon = (clone $query)->whereNotNull('renewal_date')
            ->where('renewal_date', '>=', now()->startOfDay())
            ->where('renewal_date', '<=', now()->addDays(30)->endOfDay())
            ->where('renewal_status', '!=', 'completed')
            ->count();

        $overdueRenewals = (clone $query)->whereNotNull('renewal_date')
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
            'expiring_this_month' => $query->whereMonth('expiration_date', now()->month)
                ->whereYear('expiration_date', now()->year)->count(),
            'expiring_next_month' => $query->whereMonth('expiration_date', now()->addMonth()->month)
                ->whereYear('expiration_date', now()->addMonth()->year)->count(),
            'expiring_next_3_months' => $query->whereBetween('expiration_date', [now(), now()->addMonths(3)])->count(),
            // NEW: Renewal stats
            'renewals_due_soon' => $renewalsDueSoon,
            'overdue_renewals' => $overdueRenewals,
        ];
    }

    public function create()
    {
        $stores = Store::orderBy('store_number')->get();
        return view('admin.apartment-leases.create', compact('stores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'nullable|exists:stores,id',
            'new_store_number' => 'nullable|string|max:255',
            'new_store_name' => 'nullable|string|max:255',
            'store_number' => 'nullable|integer',
            'apartment_address' => 'required|string',
            'rent' => 'required|numeric|min:0',
            'utilities' => 'nullable|numeric|min:0',
            'number_of_AT' => 'required|integer|min:1',
            'has_car' => 'required|integer|min:0',
            'is_family' => 'nullable|in:Yes,No,yes,no',
            'expiration_date' => 'nullable|date',
            'drive_time' => 'nullable|string',
            'notes' => 'nullable|string',
            'lease_holder' => 'required|string',
            // NEW: Renewal fields
            'renewal_date' => 'nullable|date|after:today',
            'renewal_status' => 'nullable|in:pending,in_prLogress,completed,declined',
            'renewal_notes' => 'nullable|string',
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

            // NEW: Set renewal created by
            if (isset($validated['renewal_date']) && $validated['renewal_date']) {
                $validated['renewal_created_by'] = auth()->id();
                $validated['renewal_status'] = $validated['renewal_status'] ?? 'pending';
            }

            $validated['created_by'] = auth()->id();
            unset($validated['new_store_number'], $validated['new_store_name']);

            $apartmentLease = ApartmentLease::create($validated);

//             NEW: Create renewal calendar event and reminders
            if ($apartmentLease->renewal_date) {
                $this->createRenewalCalendarEvent($apartmentLease);
                $this->createRenewalReminders($apartmentLease);
            }

            DB::commit();

            return redirect()->route('admin.apartment-leases.index')
                ->with('success', 'Apartment lease created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create apartment lease: ' . $e->getMessage()]);
        }
    }

    public function show(ApartmentLease $apartmentLease)
    {
        $apartmentLease->load(['store', 'renewalCreatedBy']);
        return view('admin.apartment-leases.show', compact('apartmentLease'));
    }

    public function edit(ApartmentLease $apartmentLease)
    {
        $stores = Store::orderBy('store_number')->get();
        $apartmentLease->load('renewalCreatedBy');
        return view('admin.apartment-leases.edit', compact('apartmentLease', 'stores'));
    }

    public function update(Request $request, ApartmentLease $apartmentLease)
    {
        $validated = $request->validate([
            'store_id' => 'nullable|exists:stores,id',
            'new_store_number' => 'nullable|string|max:255',
            'new_store_name' => 'nullable|string|max:255',
            'store_number' => 'nullable|numeric',
            'apartment_address' => 'required|string',
            'rent' => 'required|numeric|min:0',
            'utilities' => 'nullable|numeric|min:0',
            'number_of_AT' => 'required|integer|min:1',
            'has_car' => 'required|integer|min:0',
            'is_family' => 'nullable|in:Yes,No,yes,no',
            'expiration_date' => 'nullable|date',
            'drive_time' => 'nullable|string',
            'notes' => 'nullable|string',
            'lease_holder' => 'required|string',
            // NEW: Renewal fields
            'renewal_date' => 'nullable|date',
            'renewal_status' => 'nullable|in:pending,in_prLogress,completed,declined',
            'renewal_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldRenewalDate = $apartmentLease->renewal_date;

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

            // NEW: Handle renewal date changes
            if (isset($validated['renewal_date']) && $validated['renewal_date']) {
                if (!$oldRenewalDate || $oldRenewalDate != $validated['renewal_date']) {
                    $validated['renewal_created_by'] = auth()->id();
                }
                $validated['renewal_status'] = $validated['renewal_status'] ?? 'pending';
            }

            unset($validated['new_store_number'], $validated['new_store_name']);

            $apartmentLease->update($validated);

            // NEW: Update calendar events and reminders if renewal date changed
            if ($oldRenewalDate != $apartmentLease->renewal_date) {
                // Delete old calendar event
                if ($oldRenewalDate) {
                    CalendarEvent::where('related_model_type', ApartmentLease::class)
                        ->where('related_model_id', $apartmentLease->id)
                        ->where('event_type', 'apartment_lease_renewal')
                        ->delete();

                    // Delete old reminders
                    CalendarReminder::where('related_model_type', ApartmentLease::class)
                        ->where('related_model_id', $apartmentLease->id)
                        ->where('reminder_type', 'apartment_lease_renewal')
                        ->delete();
                }

                // Create new ones if renewal date is set
                if ($apartmentLease->renewal_date) {
                    $this->createRenewalCalendarEvent($apartmentLease);
                    $this->createRenewalReminders($apartmentLease);
                }
            }

            DB::commit();

            return redirect()->route('admin.apartment-leases.index')
                ->with('success', 'Apartment lease updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update apartment lease: ' . $e->getMessage()]);
        }
    }

    public function destroy(ApartmentLease $apartmentLease)
    {
        DB::beginTransaction();
        try {
            // NEW: Delete related calendar events and reminders
            CalendarEvent::where('related_model_type', ApartmentLease::class)
                ->where('related_model_id', $apartmentLease->id)
                ->delete();

            CalendarReminder::where('related_model_type', ApartmentLease::class)
                ->where('related_model_id', $apartmentLease->id)
                ->delete();

            $apartmentLease->delete();

            DB::commit();

            return redirect()->route('admin.apartment-leases.index')
                ->with('success', 'Apartment lease deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to delete apartment lease: ' . $e->getMessage()]);
        }
    }

    // NEW: Renewal Management Methods
    public function completeRenewal(Request $request, ApartmentLease $apartmentLease)
    {
        $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $apartmentLease->update([
                'renewal_status' => 'completed',
                'renewal_completed_at' => now(),
                'renewal_notes' => $request->completion_notes ?
                    ($apartmentLease->renewal_notes ? $apartmentLease->renewal_notes . "\n\n--- Completed ---\n" . $request->completion_notes : $request->completion_notes) :
                    $apartmentLease->renewal_notes,
            ]);

            // Mark related reminders as completed
            CalendarReminder::where('related_model_type', ApartmentLease::class)
                ->where('related_model_id', $apartmentLease->id)
                ->where('reminder_type', 'apartment_lease_renewal')
                ->update(['status' => 'completed']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Apartment lease renewal completed successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Failed to complete renewal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendRenewalReminder(Request $request, ApartmentLease $apartmentLease)
    {
        DB::beginTransaction();
        try {
            // Update the reminder sent timestamp
            $apartmentLease->update([
                'renewal_reminder_sent_at' => now(),
            ]);

            // Create a manual reminder entry
            CalendarReminder::create([
                'admin_user_id' => auth()->id(),
                'title' => "Apartment Lease Renewal Reminder - Store {$apartmentLease->store_number}",
                'description' => "Manual reminder sent for apartment lease renewal due on {$apartmentLease->renewal_date->format('M j, Y')}",
                'reminder_date' => now()->toDateString(),
                'reminder_time' => now()->format('H:i'),
                'reminder_type' => 'apartment_lease_renewal',
                'status' => 'sent',
                'related_model_type' => ApartmentLease::class,
                'related_model_id' => $apartmentLease->id,
                'notification_methods' => ['browser'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Renewal reminder sent successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Failed to send reminder: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRenewalStats()
    {
        $stats = [
            'renewals_due_soon' => ApartmentLease::whereNotNull('renewal_date')
                ->where('renewal_date', '>=', now()->startOfDay())
                ->where('renewal_date', '<=', now()->addDays(30)->endOfDay())
                ->where('renewal_status', '!=', 'completed')
                ->count(),

            'overdue_renewals' => ApartmentLease::whereNotNull('renewal_date')
                ->where('renewal_date', '<', now()->startOfDay())
                ->where('renewal_status', '!=', 'completed')
                ->count(),

            'completed_renewals_this_month' => ApartmentLease::where('renewal_status', 'completed')
                ->whereMonth('renewal_completed_at', now()->month)
                ->whereYear('renewal_completed_at', now()->year)
                ->count(),

            'pending_renewals' => ApartmentLease::where('renewal_status', 'pending')->count(),
        ];

        return response()->json($stats);
    }

    // NEW: Private helper methods for renewal management


    private function createRenewalCalendarEvent(ApartmentLease $apartmentLease)
    {
        try {
            // Validate required data
            if (!$apartmentLease->renewal_date) {
                LLog::error('ApartmentLease createRenewalCalendarEvent: No renewal date set', [
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

// NEW: Function to call both calendar event and reminders creation
    private function setupApartmentRenewalSchedule(ApartmentLease $apartmentLease)
    {
        try {
            Log::info('ApartmentLease setupRenewalSchedule: Starting setup', [
                'apartment_lease_id' => $apartmentLease->id,
                'renewal_date' => $apartmentLease->renewal_date
            ]);

            $results = [
                'calendar_event' => null,
                'reminders' => [],
                'success' => false
            ];

            // Create calendar event
            $results['calendar_event'] = $this->createRenewalCalendarEvent($apartmentLease);

            // Create reminders
            $results['reminders'] = $this->createRenewalReminders($apartmentLease);

            $results['success'] = $results['calendar_event'] !== null || count($results['reminders']) > 0;

            Log::info('ApartmentLease setupRenewalSchedule: Completed', [
                'apartment_lease_id' => $apartmentLease->id,
                'calendar_event_created' => $results['calendar_event'] !== null,
                'reminders_created' => count($results['reminders']),
                'success' => $results['success']
            ]);

            return $results;

        } catch (\Exception $e) {
            Log::error('ApartmentLease setupRenewalSchedule: Failed', [
                'apartment_lease_id' => $apartmentLease->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

// NEW: Function to remove existing renewal schedule
    private function removeApartmentRenewalSchedule(ApartmentLease $apartmentLease)
    {
        try {
            // Remove existing calendar events
            CalendarEvent::where('related_model_type', ApartmentLease::class)
                ->where('related_model_id', $apartmentLease->id)
                ->where('event_type', 'apartment_lease_renewal')
                ->delete();

            // Remove existing reminders
            CalendarReminder::where('related_model_type', ApartmentLease::class)
                ->where('related_model_id', $apartmentLease->id)
                ->where('reminder_type', 'apartment_lease_renewal')
                ->delete();

            Log::info('ApartmentLease removeRenewalSchedule: Cleanup completed', [
                'apartment_lease_id' => $apartmentLease->id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('ApartmentLease removeRenewalSchedule: Cleanup failed', [
                'apartment_lease_id' => $apartmentLease->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }


    public function export(Request $request)
    {
        $query = ApartmentLease::with(['store', 'renewalCreatedBy']);

        // Apply filters (same as index method)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('store_number', 'like', "%{$search}%")
                    ->orWhere('apartment_address', 'like', "%{$search}%")
                    ->orWhere('lease_holder', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('store', function($q) use ($search) {
                        $q->where('store_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('family_filter') && $request->family_filter !== 'all') {
            if ($request->family_filter === 'yes') {
                $query->whereIn('is_family', ['Yes', 'yes']);
            } elseif ($request->family_filter === 'no') {
                $query->whereIn('is_family', ['No', 'no']);
            }
        }

        if ($request->filled('car_filter') && $request->car_filter !== 'all') {
            if ($request->car_filter === 'with_car') {
                $query->where('has_car', '>', 0);
            } elseif ($request->car_filter === 'no_car') {
                $query->where('has_car', '=', 0);
            }
        }

        // Apply renewal status filter if present
        if ($request->filled('lease_status') && $request->lease_status !== 'all') {
            switch ($request->lease_status) {
                case 'renewal_pending':
                    $query->whereNotNull('renewal_date')
                        ->where('renewal_status', 'pending');
                    break;
                case 'renewal_overdue':
                    $query->whereNotNull('renewal_date')
                        ->where('renewal_date', '<', now()->startOfDay())
                        ->where('renewal_status', '!=', 'completed');
                    break;
                case 'renewal_due_soon':
                    $query->whereNotNull('renewal_date')
                        ->where('renewal_date', '>=', now()->startOfDay())
                        ->where('renewal_date', '<=', now()->addDays(30)->endOfDay())
                        ->where('renewal_status', '!=', 'completed');
                    break;
                case 'renewal_completed':
                    $query->where('renewal_status', 'completed');
                    break;
            }
        }

        try {
            $leases = $query->orderBy('store_number')->get();

            $filename = 'apartment-leases-' . now()->format('Y-m-d-H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ];

            $callback = function () use ($leases) {
                $file = fopen('php://output', 'w');

                // CSV Headers - Updated with renewal fields
                fputcsv($file, [
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
                    // NEW: Renewal columns
                    'Renewal Date',
                    'Renewal Status',
                    'Renewal Notes',
                    'Days Until Renewal',
                    'Renewal Created By'
                ]);

                foreach ($leases as $lease) {
                    // FIXED: Safe renewal date handling
                    $renewalDate = '';
                    $daysUntilRenewal = '';

                    if ($lease->renewal_date) {
                        try {
                            $renewalDateCarbon = $lease->renewal_date instanceof \Carbon\Carbon
                                ? $lease->renewal_date
                                : \Carbon\Carbon::parse($lease->renewal_date);
                            $renewalDate = $renewalDateCarbon->format('Y-m-d');
                            $daysUntilRenewal = now()->diffInDays($renewalDateCarbon, false);
                        } catch (\Exception $e) {
                            $renewalDate = 'Invalid Date';
                            $daysUntilRenewal = 'N/A';
                        }
                    }

                    fputcsv($file, [
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
                        // NEW: Renewal data - safely handled
                        $renewalDate,
                        $lease->renewal_status ?? '',
                        $lease->renewal_notes ?? '',
                        $daysUntilRenewal,
                        $lease->renewalCreatedBy->name ?? ''
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            // LLog the error
            LLog::error('Apartment Lease Export Error: ' . $e->getMessage());

            // Return a user-friendly error
            return response()->json([
                'error' => 'Failed to export apartment leases: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateStats()
    {
        $total = ApartmentLease::count();
        $families = ApartmentLease::whereIn('is_family', ['Yes', 'yes'])->count();
        $totalCars = ApartmentLease::sum('has_car');
        $expiringSoon = ApartmentLease::whereBetween('expiration_date', [now(), now()->addMonth()])->count();
        $totalMonthlyRent = ApartmentLease::sum(DB::raw('rent + COALESCE(utilities, 0)'));
        $averageRent = ApartmentLease::avg(DB::raw('rent + COALESCE(utilities, 0)'));
        $averageAT = ApartmentLease::avg('number_of_AT');
        $totalAT = ApartmentLease::sum('number_of_AT');
        $occupancyRate = $total ? 100 : 0;

        return [
            'total' => $total,
            'families' => $families,
            'total_cars' => $totalCars,
            'expiring_soon' => $expiringSoon,
            'total_monthly_rent' => $totalMonthlyRent,
            'average_rent' => $averageRent,
            'average_at' => $averageAT,
            'total_at' => $totalAT,
            'occupancy_rate' => $occupancyRate,
            'expiring_this_month' => ApartmentLease::whereMonth('expiration_date', now()->month)->whereYear('expiration_date', now()->year)->count(),
            'expiring_next_month' => ApartmentLease::whereMonth('expiration_date', now()->addMonth()->month)->whereYear('expiration_date', now()->addMonth()->year)->count(),
            'expiring_next_3_months' => ApartmentLease::whereBetween('expiration_date', [now(), now()->addMonths(3)])->count(),
        ];
    }

    public function list()
    {
        $leases = ApartmentLease::with('store')->get();
        return view('admin.apartment-leases.list', compact('leases'));
    }

    public function importXlsx(Request $request)
    {
        $request->validate([
            'xlsx_file' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new ApartmentLeasesImport, $request->file('xlsx_file'));

            return redirect()->route('admin.apartment-leases.index')
                ->with('success', 'Excel file imported successfully!');

        } catch (\Exception $e) {
            return redirect()->route('admin.apartment-leases.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
