@extends('layouts.app')

@section('title', 'Reminder Details')

@section('content')
    <div class="reminder-details-page">
        <!-- Header -->
        <div class="page-header">
            <div class="header-left">
                <div class="header-title">
                    <h1>
                        <i class="fas fa-eye"></i> Reminder Details
                    </h1>
                    <p class="header-subtitle">View and manage reminder information</p>
                </div>
            </div>
            <div class="header-right">
                <div class="header-actions">
                    <a href="{{ route('reminders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    @if($reminder->status === 'pending')
                        <button onclick="snoozeReminder({{ $reminder->id }})" class="btn btn-warning">
                            <i class="fas fa-clock"></i> Snooze
                        </button>
                        <button onclick="markComplete({{ $reminder->id }})" class="btn btn-success">
                            <i class="fas fa-check"></i> Mark Complete
                        </button>
                    @endif
                    <a href="{{ route('reminders.edit', $reminder->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button onclick="deleteReminder({{ $reminder->id }})" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>

        <div class="content-container">
            <!-- Main Content -->
            <div class="main-content">
                <!-- Reminder Card -->
                <div class="reminder-card {{ $reminder->status }} priority-{{ $reminder->priority ?? 'normal' }}">
                    <div class="card-header">
                        <div class="header-left">
                            <div class="reminder-status">
                                @switch($reminder->status)
                                    @case('pending')
                                        @if($reminder->is_overdue)
                                            <span class="status-badge overdue">⚠️ Overdue</span>
                                        @else
                                            <span class="status-badge pending">📅 Pending</span>
                                        @endif
                                        @break
                                    @case('sent')
                                        <span class="status-badge completed">✅ Completed</span>
                                        @break
                                    @case('dismissed')
                                        <span class="status-badge dismissed">❌ Dismissed</span>
                                        @break
                                    @case('snoozed')
                                        <span class="status-badge snoozed">😴 Snoozed</span>
                                        @break
                                @endswitch
                            </div>
                            <div class="reminder-priority">
                                @switch($reminder->priority ?? 'normal')
                                    @case('high')
                                        <span class="priority-badge high">
                                        <i class="fas fa-exclamation-circle"></i> High Priority
                                    </span>
                                        @break
                                    @case('low')
                                        <span class="priority-badge low">
                                        <i class="fas fa-info-circle"></i> Low Priority
                                    </span>
                                        @break
                                    @default
                                        <span class="priority-badge normal">
                                        <i class="fas fa-circle"></i> Normal Priority
                                    </span>
                                @endswitch
                            </div>
                        </div>

                        <div class="header-right">
                            <div class="reminder-type">
                                @switch($reminder->reminder_type)
                                    @case('maintenance_followup')
                                        <span class="type-badge maintenance">🔧 Maintenance Follow-up</span>
                                        @break
                                    @case('lease_renewal')
                                        <span class="type-badge lease">🏠 Lease Renewal</span>
                                        @break
                                    @case('payment_due')
                                        <span class="type-badge payment">💰 Payment Due</span>
                                        @break
                                    @case('inspection')
                                        <span class="type-badge inspection">🔍 Property Inspection</span>
                                        @break
                                    @case('contract_review')
                                        <span class="type-badge contract">📋 Contract Review</span>
                                        @break
                                    @case('tenant_communication')
                                        <span class="type-badge communication">💬 Tenant Communication</span>
                                        @break
                                    @default
                                        <span class="type-badge general">📝 General Reminder</span>
                                @endswitch
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <h2 class="reminder-title">{{ $reminder->title }}</h2>

                        @if($reminder->description)
                            <div class="reminder-description">
                                <h4><i class="fas fa-align-left"></i> Description</h4>
                                <p>{{ $reminder->description }}</p>
                            </div>
                        @endif

                        <!-- Date and Time Information -->
                        <div class="datetime-section">
                            <h4><i class="fas fa-calendar-clock"></i> Schedule</h4>
                            <div class="datetime-grid">
                                <div class="datetime-item">
                                    <div class="datetime-label">
                                        <i class="fas fa-calendar"></i> Date
                                    </div>
                                    <div class="datetime-value">
                                        {{ $reminder->reminder_date->format('l, F j, Y') }}
                                        <small
                                            class="text-muted">{{ $reminder->reminder_date->diffForHumans() }}</small>
                                    </div>
                                </div>

                                <div class="datetime-item">
                                    <div class="datetime-label">
                                        <i class="fas fa-clock"></i> Time
                                    </div>
                                    <div class="datetime-value">
                                        @if($reminder->reminder_time)
                                            {{ $reminder->reminder_time->format('h:i A') }}
                                            <small class="text-muted">Specific time</small>
                                        @else
                                            All Day
                                            <small class="text-muted">No specific time</small>
                                        @endif
                                    </div>
                                </div>

                                @if($reminder->advance_notice && $reminder->advance_notice > 0)
                                    <div class="datetime-item">
                                        <div class="datetime-label">
                                            <i class="fas fa-bell"></i> Advance Notice
                                        </div>
                                        <div class="datetime-value">
                                            @if($reminder->advance_notice < 60)
                                                {{ $reminder->advance_notice }} minutes before
                                            @elseif($reminder->advance_notice < 1440)
                                                {{ round($reminder->advance_notice / 60) }} hour(s) before
                                            @else
                                                {{ round($reminder->advance_notice / 1440) }} day(s) before
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Recurring Information -->
                        @if($reminder->is_recurring)
                            <div class="recurring-section">
                                <h4><i class="fas fa-repeat"></i> Recurring Settings</h4>
                                <div class="recurring-info">
                                    <div class="recurring-item">
                                        <strong>Recurrence Pattern:</strong>
                                        <span class="recurrence-badge">
                                        @switch($reminder->recurrence_pattern)
                                                @case('daily')
                                                    📅 Daily
                                                    @break
                                                @case('weekly')
                                                    📅 Weekly
                                                    @break
                                                @case('monthly')
                                                    📅 Monthly
                                                    @break
                                                @case('yearly')
                                                    📅 Yearly
                                                    @break
                                                @default
                                                    📅 {{ ucfirst($reminder->recurrence_pattern ?? 'None') }}
                                            @endswitch
                                    </span>
                                    </div>

                                    @if($reminder->recurrence_end)
                                        <div class="recurring-item">
                                            <strong>Recurrence Ends:</strong>
                                            {{ $reminder->recurrence_end->format('F j, Y') }}
                                        </div>
                                    @endif

                                    <div class="recurring-item">
                                        <strong>Recurring Reminder:</strong>
                                        <span class="instance-count">Main recurring reminder</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Notification Settings -->
                        <div class="notification-section">
                            <h4><i class="fas fa-bell"></i> Notification Settings</h4>
                            <div class="notification-grid">
                                <div class="notification-item">
                                    <div
                                        class="notification-status {{ ($reminder->notification_methods && in_array('email', $reminder->notification_methods)) ? 'enabled' : 'disabled' }}">
                                        <i class="fas fa-envelope"></i>
                                        <span>Email Notifications</span>
                                        <div
                                            class="status-indicator">{{ ($reminder->notification_methods && in_array('email', $reminder->notification_methods)) ? 'Enabled' : 'Disabled' }}</div>
                                    </div>
                                </div>

                                <div class="notification-item">
                                    <div
                                        class="notification-status {{ ($reminder->notification_methods && in_array('browser', $reminder->notification_methods)) ? 'enabled' : 'disabled' }}">
                                        <i class="fas fa-globe"></i>
                                        <span>Browser Notifications</span>
                                        <div
                                            class="status-indicator">{{ ($reminder->notification_methods && in_array('browser', $reminder->notification_methods)) ? 'Enabled' : 'Disabled' }}</div>
                                    </div>
                                </div>

                                <div class="notification-item">
                                    <div
                                        class="notification-status {{ ($reminder->notification_methods && in_array('sms', $reminder->notification_methods)) ? 'enabled' : 'disabled' }}">
                                        <i class="fas fa-sms"></i>
                                        <span>SMS Notifications</span>
                                        <div
                                            class="status-indicator">{{ ($reminder->notification_methods && in_array('sms', $reminder->notification_methods)) ? 'Enabled' : 'Disabled' }}</div>
                                    </div>
                                </div>

                                <div class="notification-item">
                                    <div class="notification-status enabled">
                                        <i class="fas fa-dashboard"></i>
                                        <span>Dashboard Notifications</span>
                                        <div class="status-indicator">Enabled</div>
                                    </div>
                                </div>
                            </div>

                            @if($reminder->last_sent_at)
                                <div class="notification-history">
                                    <p class="notification-sent">
                                        <i class="fas fa-check-circle text-success"></i>
                                        Notification sent {{ $reminder->last_sent_at->diffForHumans() }}
                                        <small>({{ $reminder->last_sent_at->format('M j, Y \\a\\t h:i A') }})</small>
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Related Item -->
                        @if($reminder->related_model_type && $reminder->related_model_id)
                            <div class="related-section">
                                <h4><i class="fas fa-link"></i> Related Item</h4>
                                <div class="related-item-card">
                                    @php
                                        $relatedModel = $reminder->safe_related_model;
                                    @endphp

                                    @if($relatedModel)
                                        <div class="related-item-content">
                                            <div class="related-item-icon">
                                                @switch($reminder->related_model_type)
                                                    @case('MaintenanceRequest')
                                                        <i class="fas fa-tools"></i>
                                                        @break
                                                    @case('ApartmentLease')
                                                        <i class="fas fa-home"></i>
                                                        @break
                                                    @case('Lease')
                                                        <i class="fas fa-file-contract"></i>
                                                        @break
                                                    @default
                                                        <i class="fas fa-file"></i>
                                                @endswitch
                                            </div>
                                            <div class="related-item-details">
                                                <div class="related-item-title">
                                                    {{ $relatedModel->title ?? $relatedModel->name ?? 'Related Item' }}
                                                </div>
                                                <div class="related-item-type">
                                                    {{ str_replace(['App\\Models\\', 'Request'], ['', ''], $reminder->related_model_type) }}
                                                    #{{ $reminder->related_model_id }}
                                                </div>
                                                @if(method_exists($relatedModel, 'getRouteKeyName'))
                                                    <div class="related-item-action">
                                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-external-link-alt"></i> View Item
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="related-item-not-found">
                                            <i class="fas fa-unlink text-muted"></i>
                                            <span class="text-muted">
                                            Related {{ $reminder->related_model_type }} #{{ $reminder->related_model_id }}
                                            (no longer available)
                                        </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Additional Notes -->
                        @if($reminder->notes)
                            <div class="notes-section">
                                <h4><i class="fas fa-sticky-note"></i> Additional Notes</h4>
                                <div class="notes-content">
                                    {{ $reminder->notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action History -->
                <div class="history-section">
                    <h3><i class="fas fa-history"></i> History & Timeline</h3>
                    <div class="timeline">
                        <div class="timeline-item created">
                            <div class="timeline-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Reminder Created</div>
                                <div class="timeline-description">
                                    Created by {{ $reminder->adminUser->name ?? 'Unknown User' }}
                                </div>
                                <div class="timeline-time">
                                    {{ $reminder->created_at->format('M j, Y \\a\\t h:i A') }}
                                    <small class="text-muted">({{ $reminder->created_at->diffForHumans() }})</small>
                                </div>
                            </div>
                        </div>

                        @if($reminder->updated_at->gt($reminder->created_at))
                            <div class="timeline-item updated">
                                <div class="timeline-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Last Modified</div>
                                    <div class="timeline-description">Reminder details were updated</div>
                                    <div class="timeline-time">
                                        {{ $reminder->updated_at->format('M j, Y \\a\\t h:i A') }}
                                        <small class="text-muted">({{ $reminder->updated_at->diffForHumans() }})</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($reminder->last_sent_at)
                            <div class="timeline-item notified">
                                <div class="timeline-icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Notification Sent</div>
                                    <div class="timeline-description">Reminder notification was delivered</div>
                                    <div class="timeline-time">
                                        {{ $reminder->last_sent_at->format('M j, Y \\a\\t h:i A') }}
                                        <small class="text-muted">({{ $reminder->last_sent_at->diffForHumans() }}
                                            )</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($reminder->snooze_until)
                            <div class="timeline-item snoozed">
                                <div class="timeline-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Snoozed</div>
                                    <div class="timeline-description">
                                        Reminder snoozed
                                        until {{ $reminder->snooze_until->format('M j, Y \\a\\t h:i A') }}
                                    </div>
                                    <div class="timeline-time">
                                        {{ now()->format('M j, Y \\a\\t h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($reminder->status === 'dismissed')
                            <div class="timeline-item dismissed">
                                <div class="timeline-icon">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Dismissed</div>
                                    <div class="timeline-description">Reminder was dismissed</div>
                                    <div class="timeline-time">
                                        {{ $reminder->updated_at->format('M j, Y \\a\\t h:i A') }}
                                        <small class="text-muted">({{ $reminder->updated_at->diffForHumans() }})</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Removed the problematic recurring instances section that was causing errors --}}
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Quick Actions -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    <div class="quick-actions">
                        @if($reminder->status === 'pending')
                            <button onclick="snoozeReminder({{ $reminder->id }})" class="action-link snooze">
                                <i class="fas fa-clock"></i> Snooze Reminder
                            </button>
                            <button onclick="markComplete({{ $reminder->id }})" class="action-link complete">
                                <i class="fas fa-check"></i> Mark Complete
                            </button>
                            <button onclick="dismissReminder({{ $reminder->id }})" class="action-link dismiss">
                                <i class="fas fa-times"></i> Dismiss
                            </button>
                        @endif
                        <a href="{{ route('reminders.edit', $reminder->id) }}" class="action-link edit">
                            <i class="fas fa-edit"></i> Edit Reminder
                        </a>
                        <a href="{{ route('reminders.create') }}" class="action-link create">
                            <i class="fas fa-plus"></i> Create Similar
                        </a>
                        <a href="{{ route('calendar.index') }}" class="action-link calendar">
                            <i class="fas fa-calendar"></i> View in Calendar
                        </a>
                    </div>
                </div>

                <!-- Reminder Summary -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-info-circle"></i> Summary</h3>
                    <div class="summary-items">
                        <div class="summary-item">
                            <div class="summary-label">Created</div>
                            <div class="summary-value">{{ $reminder->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Due</div>
                            <div class="summary-value">{{ $reminder->time_until }}</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Priority</div>
                            <div class="summary-value">{{ ucfirst($reminder->priority ?? 'Normal') }}</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Type</div>
                            <div
                                class="summary-value">{{ ucwords(str_replace('_', ' ', $reminder->reminder_type)) }}</div>
                        </div>
                        @if($reminder->is_recurring)
                            <div class="summary-item">
                                <div class="summary-label">Recurrence</div>
                                <div class="summary-value">{{ ucfirst($reminder->recurrence_pattern) }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                @if($reminder->calendarEvent)
                    <!-- Calendar Event Info -->
                    <div class="sidebar-card">
                        <h3><i class="fas fa-calendar"></i> Calendar Event</h3>
                        <div class="calendar-event-info">
                            <p>This reminder is linked to a calendar event.</p>
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> View in Calendar
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('admin.reminders.partials.snooze-modal')
    @include('admin.reminders.partials.delete-modal')

    <style>
        /* Reminder Details Page Styles */
        .reminder-details-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-subtitle {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 1rem;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .content-container {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 30px;
            padding: 30px;
        }

        /* Main Content */
        .main-content {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        /* Reminder Card */
        .reminder-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .reminder-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--priority-color);
        }

        .reminder-card.priority-high {
            --priority-color: #dc3545;
        }

        .reminder-card.priority-normal {
            --priority-color: #ffc107;
        }

        .reminder-card.priority-low {
            --priority-color: #28a745;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .status-badge, .priority-badge, .type-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.overdue {
            background: #f8d7da;
            color: #721c24;
        }

        .status-badge.completed {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.dismissed {
            background: #e2e3e5;
            color: #383d41;
        }

        .status-badge.snoozed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .priority-badge.high {
            background: #f8d7da;
            color: #721c24;
        }

        .priority-badge.normal {
            background: #fff3cd;
            color: #856404;
        }

        .priority-badge.low {
            background: #d4edda;
            color: #155724;
        }

        .type-badge.maintenance {
            background: #ff6b35;
            color: white;
        }

        .type-badge.lease {
            background: #4ecdc4;
            color: white;
        }

        .type-badge.payment {
            background: #ffe66d;
            color: #333;
        }

        .type-badge.inspection {
            background: #a8e6cf;
            color: #2d5016;
        }

        .type-badge.contract {
            background: #ffd3a5;
            color: #8b4513;
        }

        .type-badge.communication {
            background: #c7ceea;
            color: #2c3e50;
        }

        .type-badge.general {
            background: #95a5a6;
            color: white;
        }

        .card-body {
            padding: 30px;
        }

        .reminder-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 25px 0;
            line-height: 1.3;
        }

        .reminder-description {
            margin-bottom: 30px;
        }

        .reminder-description h4 {
            color: #2c3e50;
            margin: 0 0 15px 0;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reminder-description p {
            color: #6c757d;
            line-height: 1.6;
            font-size: 1rem;
            margin: 0;
        }

        /* Sections */
        .datetime-section, .recurring-section, .notification-section, .related-section, .notes-section {
            margin-bottom: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 15px;
            border: 1px solid #e9ecef;
        }

        .datetime-section h4, .recurring-section h4, .notification-section h4, .related-section h4, .notes-section h4 {
            color: #2c3e50;
            margin: 0 0 20px 0;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .datetime-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .datetime-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .datetime-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .datetime-value {
            font-size: 1.1rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .datetime-value small {
            display: block;
            margin-top: 4px;
            font-size: 0.8rem;
        }

        /* Recurring Section */
        .recurring-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .recurring-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .recurrence-badge, .instance-count {
            padding: 4px 12px;
            background: #667eea;
            color: white;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Notification Section */
        .notification-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .notification-status {
            background: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .notification-status.enabled {
            border-left: 4px solid #28a745;
        }

        .notification-status.disabled {
            border-left: 4px solid #6c757d;
            opacity: 0.7;
        }

        .status-indicator {
            margin-left: auto;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .notification-status.enabled .status-indicator {
            background: #d4edda;
            color: #155724;
        }

        .notification-status.disabled .status-indicator {
            background: #e2e3e5;
            color: #383d41;
        }

        /* Related Section */
        .related-item-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .related-item-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .related-item-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .related-item-details {
            flex: 1;
        }

        .related-item-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .related-item-type {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .related-item-not-found {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        /* Notes Section */
        .notes-content {
            background: white;
            padding: 20px;
            border-radius: 12px;
            color: #2c3e50;
            line-height: 1.6;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        /* History Section */
        .history-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .history-section h3 {
            color: #2c3e50;
            margin: 0 0 25px 0;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
        }

        .timeline-icon {
            width: 40px;
            height: 40px;
            background: white;
            border: 3px solid #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            left: -20px;
            top: 0;
            z-index: 2;
        }

        .timeline-item.created .timeline-icon {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }

        .timeline-item.updated .timeline-icon {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }

        .timeline-item.notified .timeline-icon {
            border-color: #ffc107;
            background: #ffc107;
            color: white;
        }

        .timeline-item.snoozed .timeline-icon {
            border-color: #17a2b8;
            background: #17a2b8;
            color: white;
        }

        .timeline-item.dismissed .timeline-icon {
            border-color: #6c757d;
            background: #6c757d;
            color: white;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            flex: 1;
        }

        .timeline-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .timeline-description {
            color: #6c757d;
            margin-bottom: 8px;
        }

        .timeline-time {
            font-size: 0.9rem;
            color: #95a5a6;
        }

        /* Sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .sidebar-card h3 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            font-size: 1.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .action-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: #f8f9fa;
            color: #495057;
            text-decoration: none;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            width: 100%;
        }

        .action-link:hover {
            background: #667eea;
            color: white;
            transform: translateX(5px);
            text-decoration: none;
        }

        .summary-items {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: #6c757d;
            font-weight: 500;
        }

        .summary-value {
            color: #2c3e50;
            font-weight: 600;
        }

        .calendar-event-info {
            text-align: center;
            color: #6c757d;
        }

        .calendar-event-info p {
            margin-bottom: 15px;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .content-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .header-actions {
                flex-wrap: wrap;
                justify-content: center;
            }

            .datetime-grid, .notification-grid {
                grid-template-columns: 1fr;
            }

            .timeline {
                padding-left: 20px;
            }

            .timeline-icon {
                left: -10px;
                width: 30px;
                height: 30px;
            }
        }
    </style>

    <script>
        // Action functions
        function snoozeReminder(id) {
            $('#snoozeModal').modal('show');
            document.querySelector('#snoozeModal').setAttribute('data-reminder-id', id);
        }

        function dismissReminder(id) {
            if (confirm('Are you sure you want to dismiss this reminder?')) {
                fetch(`/reminders/${id}/dismiss`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
            }
        }

        function markComplete(id) {
            fetch(`/reminders/${id}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
        }

        function deleteReminder(id) {
            $('#deleteModal').modal('show');
            document.querySelector('#deleteModal').setAttribute('data-reminder-id', id);
        }
    </script>
@endsection
