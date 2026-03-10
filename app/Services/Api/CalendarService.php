<?php

namespace App\Services\Api;
use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\CalendarReminder; // ➕ NEW - Add this import
use App\Models\AdminActivityLog;
use App\Models\ApartmentLease;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Clocking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class CalendarService
{
    
    protected $formatter;

    public function __construct(CalendarEventFormatterService $formatter)
    {
        $this->formatter = $formatter;
    }

    public function getUpcomingEvents(int $days = 7): Collection
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays($days);

        return $this->getEventsForDateRange($startDate, $endDate);
    }


    public function getCalendarStatistics(Carbon $startDate, Carbon $endDate): array
    {
        $events = $this->getEventsForDateRange($startDate, $endDate);

        return [
            'total_events' => $events->count(),
            'lease_expirations' => $events->where('event_type', 'expiration')->count(),
            'lease_renewals' => $events->whereIn('event_type', ['apartment_lease_renewal', 'lease_renewal'])->count(),
            'maintenance_requests' => $events->where('event_type', 'maintenance_request')->count(),
            'clock_events' => $events->where('event_type', 'clock_event')->count(),
            'admin_actions' => $events->where('event_type', 'admin_action')->count(),
            'reminders' => $events->where('event_type', 'reminder')->count(),

            'urgency_breakdown' => [
                'overdue' => $events->where('urgency', 'overdue')->count(),
                'critical' => $events->where('urgency', 'critical')->count(),
                'high' => $events->where('urgency', 'high')->count(),
                'normal' => $events->where('urgency', 'normal')->count(),
                'info' => $events->where('urgency', 'info')->count(),
                'completed' => $events->where('urgency', 'completed')->count(),
            ]
        ];
    }

      /**
     * Get comprehensive events for date range from ALL models
     */
    private function getLeaseExpirations(Carbon $startDate, Carbon $endDate): Collection
    {
        $events = collect();

        // Get apartment lease expirations
        if (class_exists(ApartmentLease::class)) {
            $apartmentLeases = ApartmentLease::whereBetween('expiration_date', [$startDate, $endDate])
                ->with(['store', 'createdBy'])
                ->get();

            foreach ($apartmentLeases as $lease) {
                $storeNumber = $lease->store_number ?? 'N/A';
                $title = "Apartment Lease Expires - Store #" . $storeNumber;

                $leaseHolder = $lease->lease_holder ?? 'Unknown';
                $apartmentAddress = $lease->apartment_address ?? 'No Address';
                $description = "Address: " . $apartmentAddress . "\nLease Holder: " . $leaseHolder;

                // ➕ NEW - Add urgency calculation (optional enhancement)
                $event = $this->createEventFromModel(
                    $lease,
                    'expiration',
                    $title,
                    $description,
                    $lease->expiration_date
                );

                // ➕ NEW - Calculate urgency for apartment leases
                $daysUntilExpiration = Carbon::now()->diffInDays($lease->expiration_date, false);
                $event->urgency = $daysUntilExpiration <= 0 ? 'overdue' :
                    ($daysUntilExpiration <= 7 ? 'critical' :
                        ($daysUntilExpiration <= 30 ? 'high' : 'normal'));

                $events->push($event);
            }
        }

        // Get regular lease expirations
        if (class_exists(Lease::class)) {
            $leases = Lease::where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('franchise_agreement_expiration_date', [$startDate, $endDate])
                    ->orWhereBetween('initial_lease_expiration_date', [$startDate, $endDate]);
            })->get();

            foreach ($leases as $lease) {
                if ($lease->franchise_agreement_expiration_date &&
                    $lease->franchise_agreement_expiration_date->between($startDate, $endDate)) {

                    $leaseId = $lease->id ?? 'Unknown';
                    $description = "Lease ID: " . $leaseId;

                    // ➕ NEW - Enhanced franchise agreement event
                    $event = $this->createEventFromModel(
                        $lease,
                        'expiration',
                        "Franchise Agreement Expires",
                        $description,
                        $lease->franchise_agreement_expiration_date
                    );

                    // ➕ NEW - Calculate urgency for franchise agreements
                    $daysUntilExpiration = Carbon::now()->diffInDays($lease->franchise_agreement_expiration_date, false);
                    $event->urgency = $daysUntilExpiration <= 0 ? 'overdue' :
                        ($daysUntilExpiration <= 7 ? 'critical' :
                            ($daysUntilExpiration <= 30 ? 'high' : 'normal'));

                    $events->push($event);
                }

                if ($lease->initial_lease_expiration_date &&
                    $lease->initial_lease_expiration_date->between($startDate, $endDate)) {

                    $leaseId = $lease->id ?? 'Unknown';
                    $description = "Lease ID: " . $leaseId;

                    // ➕ NEW - Enhanced initial lease event
                    $event = $this->createEventFromModel(
                        $lease,
                        'expiration',
                        "Initial Lease Expires",
                        $description,
                        $lease->initial_lease_expiration_date
                    );

                    // ➕ NEW - Calculate urgency for initial leases
                    $daysUntilExpiration = Carbon::now()->diffInDays($lease->initial_lease_expiration_date, false);
                    $event->urgency = $daysUntilExpiration <= 0 ? 'overdue' :
                        ($daysUntilExpiration <= 7 ? 'critical' :
                            ($daysUntilExpiration <= 30 ? 'high' : 'normal'));

                    $events->push($event);
                }
            }
        }

        return $events;
    }
     private function getRenewalEvents(Carbon $startDate, Carbon $endDate): Collection
    {
        $events = collect();

        // Get apartment lease renewals
        if (class_exists(ApartmentLease::class)) {
            $apartmentLeases = ApartmentLease::whereNotNull('renewal_date')
                ->whereBetween('renewal_date', [$startDate, $endDate])
                ->get();

            foreach ($apartmentLeases as $lease) {
                // Safe date handling
                $renewalDate = $lease->renewal_date instanceof Carbon
                    ? $lease->renewal_date
                    : Carbon::parse($lease->renewal_date);

                $storeNumber = $lease->store_number ?? 'N/A';
                $statusIcon = match($lease->renewal_status ?? 'pending') {
                    'completed' => '✅',
                    'in_progress' => '⏳',
                    'declined' => '❌',
                    default => '🔔'
                };

                $title = $statusIcon . " Apartment Lease Renewal - Store #" . $storeNumber;

                $description = "Address: " . ($lease->apartment_address ?? 'No Address') . "\n" .
                    "Lease Holder: " . ($lease->lease_holder ?? 'Unknown') . "\n" .
                    "Status: " . ucfirst($lease->renewal_status ?? 'pending');

                if ($lease->renewal_notes) {
                    $description .= "\nNotes: " . $lease->renewal_notes;
                }

                $event = $this->createEventFromModel(
                    $lease,
                    'apartment_lease_renewal',
                    $title,
                    $description,
                    $renewalDate
                );

                // Calculate renewal urgency
                $daysUntilRenewal = Carbon::now()->diffInDays($renewalDate, false);
                $event->urgency = $lease->renewal_status === 'completed' ? 'completed' :
                    ($daysUntilRenewal <= 0 ? 'overdue' :
                        ($daysUntilRenewal <= 7 ? 'critical' :
                            ($daysUntilRenewal <= 30 ? 'high' : 'normal')));

                $event->renewal_status = $lease->renewal_status;
                $events->push($event);
            }
        }

        // Get regular lease renewals (if renewal_date field exists)
        if (class_exists(Lease::class)) {
            try {
                $leases = Lease::whereNotNull('renewal_date')
                    ->whereBetween('renewal_date', [$startDate, $endDate])
                    ->get();

                foreach ($leases as $lease) {
                    $renewalDate = $lease->renewal_date instanceof Carbon
                        ? $lease->renewal_date
                        : Carbon::parse($lease->renewal_date);

                    $statusIcon = match($lease->renewal_status ?? 'pending') {
                        'completed' => '✅',
                        'in_progress' => '⏳',
                        'declined' => '❌',
                        default => '🔔'
                    };

                    $title = $statusIcon . " Lease Renewal - Store " . ($lease->store_number ?? $lease->id);
                    $description = "Lease ID: " . $lease->id . "\n" .
                        "Status: " . ucfirst($lease->renewal_status ?? 'pending');

                    $event = $this->createEventFromModel(
                        $lease,
                        'lease_renewal',
                        $title,
                        $description,
                        $renewalDate
                    );

                    $daysUntilRenewal = Carbon::now()->diffInDays($renewalDate, false);
                    $event->urgency = isset($lease->renewal_status) && $lease->renewal_status === 'completed' ? 'completed' :
                        ($daysUntilRenewal <= 0 ? 'overdue' :
                            ($daysUntilRenewal <= 7 ? 'critical' :
                                ($daysUntilRenewal <= 30 ? 'high' : 'normal')));

                    $events->push($event);
                }
            } catch (\Exception $e) {
                // If renewal_date doesn't exist in Lease table, skip silently
            }
        }

        return $events;
    }
    private function getMaintenanceEvents(Carbon $startDate, Carbon $endDate): Collection
    {
        $events = collect();

        if (class_exists(MaintenanceRequest::class)) {
            Log::info("=== Getting Maintenance Events ===");

            // Get maintenance requests created in date range
            $maintenanceRequests = MaintenanceRequest::where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->orWhereBetween('due_date', [$startDate, $endDate]);
            })
                ->with(['assignedUser', 'store', 'urgencyLevel'])
                ->get();

            Log::info("Found " . $maintenanceRequests->count() . " maintenance requests");

            foreach ($maintenanceRequests as $request) {
                // Event for creation date
                if ($request->created_at->between($startDate, $endDate)) {
                    $issueDescription = $request->description_of_issue ?? 'No description';
                    $title = "New Maintenance: " . Str::limit($issueDescription, 30);

                    $urgency = $request->urgencyLevel->name ?? $request->urgency_level_id ?? 'Normal';
                    $assignedUser = $request->assignedUser->name ?? 'Unassigned';
                    $storeInfo = $request->store ? "Store: " . $request->store->name : "Store ID: " . $request->store_id;
                    $status = ucfirst(str_replace('_', ' ', $request->status ?? 'pending'));

                    $description = "Issue: " . Str::limit($issueDescription, 100) . "\n" .
                        "Priority: " . $urgency . "\n" .
                        "Status: " . $status . "\n" .
                        "Assigned to: " . $assignedUser . "\n" .
                        $storeInfo;

                    $event = $this->createEventFromModel(
                        $request,
                        'maintenance_request',
                        $title,
                        $description,
                        $request->created_at,
                        [
                            'maintenance_request_id' => $request->id,
                            'issue_description' => $issueDescription,
                            'urgency' => $urgency,
                            'status' => $status,
                            'assigned_to' => $assignedUser,
                            'store_info' => $storeInfo,
                            'costs' => $request->costs ? '$' . number_format($request->costs, 2) : 'Not set',
                            'equipment' => $request->equipment_with_issue ?? 'Not specified'
                        ]
                    );

                    $events->push($event);
                }

                // Event for due date if exists
                if ($request->due_date && $request->due_date->between($startDate, $endDate)) {
                    $issueDescription = $request->description_of_issue ?? 'No description';
                    $title = "Maintenance Due: " . Str::limit($issueDescription, 30);

                    $urgency = $request->urgencyLevel->name ?? $request->urgency_level_id ?? 'Normal';
                    $status = ucfirst(str_replace('_', ' ', $request->status ?? 'pending'));
                    $assignedUser = $request->assignedUser->name ?? 'Unassigned';

                    $description = "Due Date Alert\n" .
                        "Issue: " . Str::limit($issueDescription, 100) . "\n" .
                        "Priority: " . $urgency . "\n" .
                        "Status: " . $status . "\n" .
                        "Assigned to: " . $assignedUser;

                    $event = $this->createEventFromModel(
                        $request,
                        'maintenance_request',
                        $title,
                        $description,
                        $request->due_date,
                        [
                            'maintenance_request_id' => $request->id,
                            'issue_description' => $issueDescription,
                            'urgency' => $urgency,
                            'status' => $status,
                            'assigned_to' => $assignedUser,
                            'is_due_date' => true
                        ]
                    );

                    $events->push($event);
                }
            }
        }

        return $events;
    }
    private function getClockEvents(Carbon $startDate, Carbon $endDate): Collection
    {
        $events = collect();

        if (class_exists(Clocking::class)) {
            Log::info("=== Getting Clock Events ===");

            $clockings = Clocking::whereBetween('created_at', [$startDate, $endDate])
                ->with('user')
                ->get();

            Log::info("Found " . $clockings->count() . " clock events");

            // Group by user and date for summary
            $groupedClockings = $clockings->groupBy(function($clocking) {
                return $clocking->user_id . '-' . $clocking->created_at->format('Y-m-d');
            });

            foreach ($groupedClockings as $userDateKey => $userClockings) {
                $firstClocking = $userClockings->first();
                $clockInTimes = $userClockings->whereNotNull('clock_in');
                $clockOutTimes = $userClockings->whereNotNull('clock_out');

                $userName = ($firstClocking->user && $firstClocking->user->name)
                    ? $firstClocking->user->name
                    : 'Unknown User';

                $title = "Clock Activity - " . $userName;

                // Calculate total hours if we have both clock in and out
                $totalHours = 0;
                $clockInCount = $clockInTimes->count();
                $clockOutCount = $clockOutTimes->count();

                // Get additional details
                $usingCar = $userClockings->where('using_car', true)->count() > 0 ? 'Yes' : 'No';
                $boughtSomething = $userClockings->where('bought_something', true)->count() > 0 ? 'Yes' : 'No';
                $fixedSomething = $userClockings->where('fixed_something', true)->count() > 0 ? 'Yes' : 'No';
                $totalCosts = $userClockings->sum('purchase_cost');
                $gasPayments = $userClockings->sum('gas_payment');
                $totalSalary = $userClockings->sum('total_salary');

                $description = "Clock In: {$clockInCount} times, Clock Out: {$clockOutCount} times\n" .
                    "Used Car: {$usingCar}\n" .
                    "Purchased Items: {$boughtSomething}\n" .
                    "Fixed Something: {$fixedSomething}";

                if ($totalCosts > 0) {
                    $description .= "\nTotal Purchase Cost: $" . number_format($totalCosts, 2);
                }
                if ($gasPayments > 0) {
                    $description .= "\nGas Payments: $" . number_format($gasPayments, 2);
                }
                if ($totalSalary > 0) {
                    $description .= "\nTotal Salary: $" . number_format($totalSalary, 2);
                }

                $event = $this->createEventFromModel(
                    $firstClocking,
                    'clock_event',
                    $title,
                    $description,
                    $firstClocking->created_at,
                    [
                        'user_name' => $userName,
                        'clock_in_count' => $clockInCount,
                        'clock_out_count' => $clockOutCount,
                        'using_car' => $usingCar,
                        'bought_something' => $boughtSomething,
                        'fixed_something' => $fixedSomething,
                        'total_costs' => $totalCosts > 0 ? '$' . number_format($totalCosts, 2) : '$0.00',
                        'gas_payments' => $gasPayments > 0 ? '$' . number_format($gasPayments, 2) : '$0.00',
                        'total_salary' => $totalSalary > 0 ? '$' . number_format($totalSalary, 2) : '$0.00',
                        'clocking_ids' => $userClockings->pluck('id')->toArray()
                    ]
                );

                $events->push($event);
            }
        }

        return $events;
    }
    private function getAdminActivityEvents(Carbon $startDate, Carbon $endDate): Collection
    {
        $events = collect();

        $adminActivities = AdminActivityLog::whereBetween('performed_at', [$startDate, $endDate])
            ->with('adminUser')
            ->get();

        // Group by admin and date for summary
        $groupedActivities = $adminActivities->groupBy(function($activity) {
            return $activity->admin_user_id . '-' . $activity->performed_at->format('Y-m-d');
        });

        foreach ($groupedActivities as $adminDateKey => $userActivities) {
            $firstActivity = $userActivities->first();
            $actionCounts = $userActivities->groupBy('action_type')->map->count();

            $adminName = ($firstActivity->adminUser && $firstActivity->adminUser->name)
                ? $firstActivity->adminUser->name
                : 'Unknown';

            $title = "Admin Activity - " . $adminName;

            $description = "Actions: ";
            foreach ($actionCounts as $action => $count) {
                $description .= $action . ": " . $count . ", ";
            }
            $description = rtrim($description, ', ');

            // ➕ NEW - Add urgency to admin events
            $event = $this->createEventFromModel(
                $firstActivity,
                'admin_action',
                $title,
                $description,
                $firstActivity->performed_at
            );
            $event->urgency = 'info'; // Admin activities are informational
            $event->total_actions = $userActivities->count(); // ➕ NEW - Add total actions count
            $events->push($event);
        }

        return $events;
    }
    private function getReminderEvents(Carbon $startDate, Carbon $endDate): Collection
    {
        $events = collect();

        if (class_exists(CalendarReminder::class)) {
            $reminders = CalendarReminder::whereBetween('reminder_date', [$startDate, $endDate])
                ->get();

            foreach ($reminders as $reminder) {
                // Safe date handling
                $reminderDate = $reminder->reminder_date instanceof Carbon
                    ? $reminder->reminder_date
                    : Carbon::parse($reminder->reminder_date);

                $statusIcon = match($reminder->status ?? 'pending') {
                    'sent' => '✅',
                    'dismissed' => '❌',
                    'snoozed' => '😴',
                    default => '🔔'
                };

                $title = $statusIcon . " " . ($reminder->title ?? 'Reminder');

                $description = "Reminder: " . ($reminder->title ?? 'Untitled') . "\n" .
                    "Status: " . ucfirst($reminder->status ?? 'Pending');

                if ($reminder->description) {
                    $description .= "\nDetails: " . $reminder->description;
                }

                $event = $this->createEventFromModel(
                    $reminder,
                    'reminder',
                    $title,
                    $description,
                    $reminderDate
                );

                $event->urgency = $reminder->priority ?? 'normal';
                $event->reminder_status = $reminder->status ?? 'pending';
                $events->push($event);
            }
        }

        return $events;
    }
    public function getEventTypeColor(string $eventType): string
    {
        return match($eventType) {
            'maintenance_request' => '#dc3545', // red
            'admin_action' => '#28a745',        // green
            'clock_event' => '#17a2b8',         // blue
            'reminder' => '#ffc107',            // yellow
            'expiration' => '#fd7e14',          // orange
            'custom' => '#6f42c1',              // purple
            // ➕ NEW - Additional event type colors
            'apartment_lease_renewal' => '#fd7e14',  // orange - important
            'lease_renewal' => '#fd7e14',            // orange - important
            default => '#6c757d',               // gray
        };
    }
    private function createEventFromModel($model, string $eventType, string $title, string $description, Carbon $date, array $additionalData = [])
    {
        Log::info("Creating event from model:");
        Log::info("- Model: " . get_class($model));
        Log::info("- Event Type: " . $eventType);
        Log::info("- Title: " . $title);
        Log::info("- Date: " . $date->format('Y-m-d H:i:s'));

        $uniqueId = "temp_" . uniqid();
        $modelClass = get_class($model);
        $modelId = $model->id ?? 0;
        $userId = auth()->id() ?? 1;
        $colorCode = $this->getEventTypeColor($eventType);
        $isAllDay = in_array($eventType, ['expiration']);

        // Create a simple object that can be formatted for FullCalendar
        $calendarEvent = new class {
            public $id;
            public $title;
            public $description;
            public $event_type;
            public $start_date;
            public $start_time;
            public $is_all_day;
            public $color_code;
            public $related_model_type;
            public $related_model_id;
            public $created_by;
            public $additional_data;
        };

        $calendarEvent->id = $uniqueId;
        $calendarEvent->title = $title;
        $calendarEvent->description = $description;
        $calendarEvent->event_type = $eventType;
        $calendarEvent->start_date = $date->format('Y-m-d');
        $calendarEvent->start_time = $date->format('H:i:s');
        $calendarEvent->is_all_day = $isAllDay;
        $calendarEvent->color_code = $colorCode;
        $calendarEvent->related_model_type = $modelClass;
        $calendarEvent->related_model_id = $modelId;
        $calendarEvent->created_by = $userId;
        $calendarEvent->additional_data = $additionalData; // Store additional data

        Log::info("✓ CalendarEvent created successfully with ID: " . $uniqueId);

        return $calendarEvent;
    }




    public function getEventsForDateRange(Carbon $startDate, Carbon $endDate, ?array $filters = []): Collection
    {
        $allEvents = collect();

        // 1. Get existing calendar events
        $calendarEvents = CalendarEvent::byDateRange($startDate, $endDate)
            ->with(['creator', 'relatedModel'])
            ->get();
        $allEvents = $allEvents->merge($calendarEvents);

        // 2. Get lease expirations (ApartmentLease)
        $leaseExpirations = $this->getLeaseExpirations($startDate, $endDate);
        $allEvents = $allEvents->merge($leaseExpirations);

        // ➕ NEW 3. Get lease renewal events
        $renewalEvents = $this->getRenewalEvents($startDate, $endDate);
        $allEvents = $allEvents->merge($renewalEvents);

        // 3. Get maintenance requests (renumbered to 4)
        $maintenanceEvents = $this->getMaintenanceEvents($startDate, $endDate);
        $allEvents = $allEvents->merge($maintenanceEvents);

        // 4. Get clock events (renumbered to 5)
        $clockEvents = $this->getClockEvents($startDate, $endDate);
        $allEvents = $allEvents->merge($clockEvents);

        // 5. Get admin activity events (renumbered to 6)
        $adminEvents = $this->getAdminActivityEvents($startDate, $endDate);
        $allEvents = $allEvents->merge($adminEvents);

        // ➕ NEW 7. Get reminder events
        $reminderEvents = $this->getReminderEvents($startDate, $endDate);
        $allEvents = $allEvents->merge($reminderEvents);

        // Apply filters
        if (isset($filters['event_type']) && $filters['event_type']) {
            $allEvents = $allEvents->where('event_type', $filters['event_type']);
        }

        // ➕ NEW - Additional urgency filter
        if (isset($filters['urgency']) && $filters['urgency']) {
            $allEvents = $allEvents->where('urgency', $filters['urgency']);
        }

        return $allEvents->sortBy('start_date');
    }


    public function getFormattedEvents(Carbon $startDate, Carbon $endDate, ?array $filters = []): array
    {
        try {

            $events = $this->getEventsForDateRange($startDate, $endDate, $filters);

            return $this->formatter->formatEvents($events);

        } catch (\Exception $e) {

            Log::error('Error in getFormattedEvents: ' . $e->getMessage());

            return [];
        }
    }


}