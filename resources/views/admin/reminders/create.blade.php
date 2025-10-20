@extends('layouts.app')

@section('title', 'Create New Reminder')

@section('content')
    <div class="create-reminder-page">
        <!-- Header -->
        <div class="page-header">
            <div class="header-left">
                <div class="header-title">
                    <h1><i class="fas fa-plus-circle"></i> Create New Reminder</h1>
                    <p class="header-subtitle">Set up a new personal reminder for browser notifications</p>
                </div>
            </div>
            <div class="header-right">
                <a href="{{ route('reminders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Reminders
                </a>
            </div>
        </div>

        <div class="content-container">
            <div class="form-section">
                <div class="form-card">
                    <form action="{{ route('reminders.store') }}" method="POST" id="reminderForm" class="needs-validation" novalidate>
                        @csrf

                        <!-- Form Steps Indicator -->
                        <div class="form-steps">
                            <div class="step active" data-step="1">
                                <div class="step-number">1</div>
                                <div class="step-label">Basic Info</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" data-step="2">
                                <div class="step-number">2</div>
                                <div class="step-label">Schedule</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" data-step="3">
                                <div class="step-number">3</div>
                                <div class="step-label">Options</div>
                            </div>
                        </div>

                        <!-- Step 1: Basic Information -->
                        <div class="form-step active" id="step1">
                            <div class="step-header">
                                <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                                <p>Provide the essential details for your reminder</p>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="title" class="form-label required">
                                        <i class="fas fa-heading"></i> Reminder Title
                                    </label>
                                    <input type="text"
                                           class="form-control @error('title') is-invalid @enderror"
                                           id="title"
                                           name="title"
                                           value="{{ old('title') }}"
                                           placeholder="e.g., Call John about lease renewal at 5 PM"
                                           required
                                           maxlength="255">
                                    <div class="character-count">
                                        <span id="titleCount">0</span>/255 characters
                                    </div>
                                    @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="reminder_type" class="form-label required">
                                        <i class="fas fa-tag"></i> Reminder Type
                                    </label>
                                    <select class="form-control custom-select @error('reminder_type') is-invalid @enderror"
                                            id="reminder_type"
                                            name="reminder_type"
                                            required>
                                        <option value="">Select reminder type...</option>
                                        <option value="custom_reminder" {{ old('reminder_type') == 'custom_reminder' ? 'selected' : '' }}>
                                            📝 Personal Reminder
                                        </option>
                                        <option value="maintenance_followup" {{ old('reminder_type') == 'maintenance_followup' ? 'selected' : '' }}>
                                            🔧 Maintenance Follow-up
                                        </option>
                                        <option value="lease_renewal" {{ old('reminder_type') == 'lease_renewal' ? 'selected' : '' }}>
                                            🏠 Lease Renewal
                                        </option>
                                        <option value="payment_due" {{ old('reminder_type') == 'payment_due' ? 'selected' : '' }}>
                                            💰 Payment Due
                                        </option>
                                        <option value="expiration_alert" {{ old('reminder_type') == 'expiration_alert' ? 'selected' : '' }}>
                                            ⚠️ Expiration Alert
                                        </option>
                                    </select>
                                    @error('reminder_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left"></i> Description
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description"
                                          name="description"
                                          rows="4"
                                          placeholder="Provide additional details about this reminder...">{{ old('description') }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-primary next-step" data-next="2">
                                    Next: Schedule <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Schedule -->
                        <div class="form-step" id="step2">
                            <div class="step-header">
                                <h3><i class="fas fa-calendar-alt"></i> Schedule Settings</h3>
                                <p>Set exactly when you want to be notified</p>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="reminder_date" class="form-label required">
                                        <i class="fas fa-calendar"></i> Reminder Date
                                    </label>
                                    <input type="date"
                                           class="form-control @error('reminder_date') is-invalid @enderror"
                                           id="reminder_date"
                                           name="reminder_date"
                                           value="{{ old('reminder_date', date('Y-m-d')) }}"
                                           min="{{ date('Y-m-d') }}"
                                           required>
                                    @error('reminder_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="reminder_time" class="form-label required">
                                        <i class="fas fa-clock"></i> Reminder Time
                                    </label>
                                    <input type="time"
                                           class="form-control @error('reminder_time') is-invalid @enderror"
                                           id="reminder_time"
                                           name="reminder_time"
                                           value="{{ old('reminder_time', '09:00') }}"
                                           required>
                                    @error('reminder_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Notification Info Box -->
                            <div class="notification-info-box">
                                <div class="info-header">
                                    <i class="fas fa-bell"></i>
                                    <h5>Browser Notification System</h5>
                                </div>
                                <div class="info-content">
                                    <p><strong>How it works:</strong> You will receive a browser notification within 5 minutes of your scheduled time.</p>
                                    <div class="notification-example">
                                        <div class="browser-notification-preview">
                                            <div class="notification-icon">🔔</div>
                                            <div class="notification-content">
                                                <div class="notification-title">Your Reminder Title</div>
                                                <div class="notification-message">Description text will appear here</div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="notification-note">
                                        <i class="fas fa-info-circle"></i>
                                        Make sure to keep your browser open to receive notifications.
                                    </p>
                                </div>
                            </div>

                            <!-- Quick Date Presets -->
                            <div class="quick-dates">
                                <h5><i class="fas fa-magic"></i> Quick Date Selection</h5>
                                <div class="quick-date-buttons">
                                    <button type="button" class="btn btn-outline-primary quick-date" data-days="0">Today</button>
                                    <button type="button" class="btn btn-outline-primary quick-date" data-days="1">Tomorrow</button>
                                    <button type="button" class="btn btn-outline-primary quick-date" data-days="7">Next Week</button>
                                    <button type="button" class="btn btn-outline-primary quick-date" data-days="14">2 Weeks</button>
                                    <button type="button" class="btn btn-outline-primary quick-date" data-days="30">Next Month</button>
                                </div>
                            </div>

                            <!-- Recurring Options -->
                            <div class="recurring-section">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_recurring">
                                            <i class="fas fa-repeat"></i> Make this a recurring reminder
                                        </label>
                                    </div>
                                </div>

                                <div class="recurring-options" style="display: none;">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="recurrence_pattern" class="form-label">Repeat Every</label>
                                            <select class="form-control custom-select" id="recurrence_pattern" name="recurrence_pattern">
                                                <option value="daily" {{ old('recurrence_pattern') == 'daily' ? 'selected' : '' }}>Daily</option>
                                                <option value="weekly" {{ old('recurrence_pattern') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                <option value="monthly" {{ old('recurrence_pattern', 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                <option value="yearly" {{ old('recurrence_pattern') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary prev-step" data-prev="1">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                                <button type="button" class="btn btn-primary next-step" data-next="3">
                                    Next: Options <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Additional Options -->
                        <div class="form-step" id="step3">
                            <div class="step-header">
                                <h3><i class="fas fa-cogs"></i> Additional Options</h3>
                                <p>Configure additional settings for your reminder</p>
                            </div>

                            <!-- Related Model Selection -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-link"></i> Link to Related Item (Optional)
                                </label>
                                <div class="related-item-section">
                                    <select class="form-control custom-select" id="related_model_type" name="related_model_type">
                                        <option value="">Not linked to any item</option>
                                        <option value="MaintenanceRequest" {{ old('related_model_type') == 'MaintenanceRequest' ? 'selected' : '' }}>
                                            🔧 Maintenance Request
                                        </option>
                                        <option value="ApartmentLease" {{ old('related_model_type') == 'ApartmentLease' ? 'selected' : '' }}>
                                            🏠 Apartment Lease
                                        </option>
                                        <option value="Lease" {{ old('related_model_type') == 'Lease' ? 'selected' : '' }}>
                                            📋 Lease Agreement
                                        </option>
                                    </select>

                                    <div class="related-item-search" style="display: none;">
                                        <input type="text" class="form-control" id="related_item_search" placeholder="Search for item to link...">
                                        <input type="hidden" id="related_model_id" name="related_model_id" value="{{ old('related_model_id') }}">
                                        <div class="search-results"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Browser Notification Settings -->
                            <div class="notification-settings">
                                <h5><i class="fas fa-bell"></i> Notification Settings</h5>

                                <div class="browser-notification-info">
                                    <div class="notification-enabled">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span><strong>Browser notifications enabled</strong></span>
                                    </div>
                                    <p class="notification-description">
                                        You will receive instant browser notifications when your reminders are due.
                                        No email or SMS setup required - everything works through your calendar dashboard.
                                    </p>
                                </div>

                                <!-- Hidden field to always include browser notifications -->
                                <input type="hidden" name="notification_methods[]" value="browser">
                            </div>

                            <!-- Calendar Event Link -->
                            @if(isset($calendarEvent))
                                <input type="hidden" name="calendar_event_id" value="{{ $calendarEvent->id }}">
                                <div class="form-group">
                                    <div class="alert alert-info">
                                        <i class="fas fa-calendar"></i>
                                        This reminder will be linked to the calendar event: <strong>{{ $calendarEvent->title }}</strong>
                                    </div>
                                </div>
                            @endif

                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary prev-step" data-prev="2">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                                <button type="submit" class="btn btn-success" id="createReminderBtn">
                                    <i class="fas fa-save"></i> Create Reminder
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preview Sidebar -->
            <div class="preview-section">
                <div class="preview-card">
                    <h3><i class="fas fa-eye"></i> Preview</h3>
                    <div class="reminder-preview">
                        <div class="preview-header">
                            <div class="preview-type" id="previewType">📝 Personal Reminder</div>
                        </div>

                        <div class="preview-body">
                            <h4 id="previewTitle">Enter a title...</h4>
                            <p id="previewDescription">Add a description...</p>

                            <div class="preview-datetime">
                                <div class="datetime-info">
                                    <i class="fas fa-calendar"></i>
                                    <span id="previewDate">Select date</span>
                                    <i class="fas fa-clock"></i>
                                    <span id="previewTime">Select time</span>
                                </div>
                            </div>
                        </div>

                        <div class="preview-footer">
                            <div class="preview-status">
                                <span class="status-badge pending">🔔 Will notify via browser</span>
                            </div>
                        </div>
                    </div>

                    <div class="preview-info">
                        <h4><i class="fas fa-info-circle"></i> Reminder Info</h4>
                        <div class="info-item">
                            <strong>Status:</strong> <span id="previewStatus">Will be created as pending</span>
                        </div>
                        <div class="info-item">
                            <strong>Created by:</strong> <span>{{ auth()->user()->name }}</span>
                        </div>
                        <div class="info-item">
                            <strong>Notifications:</strong> <span id="previewNotifications">Browser only</span>
                        </div>
                        <div class="info-item">
                            <strong>Delivery:</strong> <span>Within 5 minutes of due time</span>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="tips-card">
                    <h3><i class="fas fa-lightbulb"></i> Tips</h3>
                    <div class="tip-item">
                        <i class="fas fa-check text-success"></i>
                        Use specific titles like "Call John at 5 PM"
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-check text-success"></i>
                        Browser notifications appear within 5 minutes
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-check text-success"></i>
                        Keep your browser open to receive notifications
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-check text-success"></i>
                        Set exact times for better reminder accuracy
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .create-reminder-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 0;
            margin: 0;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.15);
        }

        .page-header .container,
        .content-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-title h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }

        .content-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .form-card, .preview-card, .tips-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .form-card {
            padding: 2.5rem;
        }

        /* Form Steps */
        .form-steps {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 3rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .step-number {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .step.active .step-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: scale(1.1);
        }

        .step.completed .step-number {
            background: #28a745;
            color: white;
        }

        .step-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #6c757d;
            text-align: center;
        }

        .step.active .step-label {
            color: #667eea;
        }

        .step-line {
            width: 80px;
            height: 2px;
            background: #e9ecef;
            margin: 0 1rem;
        }

        /* Form Steps Content */
        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .step-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .step-header h3 {
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .step-header p {
            color: #6c757d;
            font-size: 1.1rem;
            margin: 0;
        }

        /* Form Elements */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-row.single {
            grid-template-columns: 1fr;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .form-label.required::after {
            content: '*';
            color: #dc3545;
            margin-left: 4px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
            outline: none;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .custom-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px 12px;
            padding-right: 2.5rem;
        }

        .character-count {
            font-size: 0.85rem;
            color: #6c757d;
            text-align: right;
            margin-top: 0.25rem;
        }

        /* Notification Info Box */
        .notification-info-box {
            margin: 25px 0;
            padding: 20px;
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border-radius: 15px;
            border: 2px solid #2196f3;
        }

        .info-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .info-header h5 {
            margin: 0;
            color: #1976d2;
            font-weight: 600;
        }

        .info-content p {
            margin: 0 0 15px 0;
            color: #424242;
            line-height: 1.6;
        }

        .browser-notification-preview {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin: 15px 0;
            border-left: 4px solid #2196f3;
        }

        .notification-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .notification-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .notification-message {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .notification-note {
            font-size: 0.85rem;
            color: #666;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        /* Quick Date Buttons */
        .quick-dates {
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .quick-dates h5 {
            color: #2c3e50;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quick-date-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .quick-date {
            border: 2px solid #667eea;
            color: #667eea;
            background: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .quick-date:hover, .quick-date.active {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        /* Recurring Section */
        .recurring-section {
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .custom-control-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .recurring-options {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #e9ecef;
        }

        /* Browser Notification Settings */
        .browser-notification-info {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .notification-enabled {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .notification-description {
            color: #6c757d;
            line-height: 1.6;
            margin: 0;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 2px solid #f8f9fa;
        }

        .btn {
            padding: 0.875rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            background: white;
        }

        /* Preview Section */
        .preview-section {
            position: sticky;
            top: 2rem;
            height: fit-content;
        }

        .preview-card, .tips-card {
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }

        .preview-card h3, .tips-card h3 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .reminder-preview {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .preview-type {
            background: #667eea;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .preview-body h4 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .preview-body p {
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .datetime-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .status-badge {
            background: linear-gradient(135deg, #2196f3, #1976d2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .preview-info {
            border-top: 2px solid #f8f9fa;
            padding-top: 1rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .tip-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            color: #2c3e50;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .content-container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .preview-section {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .form-steps {
                padding: 1rem;
            }

            .step-number {
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }

            .step-line {
                width: 40px;
            }

            .quick-date-buttons {
                justify-content: center;
            }

            .form-actions {
                flex-direction: column;
                gap: 1rem;
            }
        }

        /* Validation Styles */
        .is-invalid {
            border-color: #dc3545 !important;
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc3545;
        }

        .text-success {
            color: #28a745 !important;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentStep = 1;
            const maxSteps = 3;

            // Form Elements
            const titleInput = document.getElementById('title');
            const descriptionInput = document.getElementById('description');
            const reminderTypeSelect = document.getElementById('reminder_type');
            const reminderDateInput = document.getElementById('reminder_date');
            const reminderTimeInput = document.getElementById('reminder_time');
            const isRecurringCheckbox = document.getElementById('is_recurring');
            const recurringOptions = document.querySelector('.recurring-options');

            // Preview Elements
            const previewTitle = document.getElementById('previewTitle');
            const previewDescription = document.getElementById('previewDescription');
            const previewType = document.getElementById('previewType');
            const previewDate = document.getElementById('previewDate');
            const previewTime = document.getElementById('previewTime');

            // Step Navigation
            document.querySelectorAll('.next-step').forEach(button => {
                button.addEventListener('click', function() {
                    const nextStep = parseInt(this.dataset.next);
                    if (validateCurrentStep()) {
                        goToStep(nextStep);
                    }
                });
            });

            document.querySelectorAll('.prev-step').forEach(button => {
                button.addEventListener('click', function() {
                    const prevStep = parseInt(this.dataset.prev);
                    goToStep(prevStep);
                });
            });

            function goToStep(step) {
                // Hide current step
                document.querySelector(`#step${currentStep}`).classList.remove('active');
                document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');

                // Show new step
                currentStep = step;
                document.querySelector(`#step${currentStep}`).classList.add('active');
                document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');

                // Mark previous steps as completed
                for (let i = 1; i < currentStep; i++) {
                    document.querySelector(`.step[data-step="${i}"]`).classList.add('completed');
                }
            }

            function validateCurrentStep() {
                let isValid = true;

                if (currentStep === 1) {
                    if (!titleInput.value.trim()) {
                        showFieldError(titleInput, 'Title is required');
                        isValid = false;
                    }
                    if (!reminderTypeSelect.value) {
                        showFieldError(reminderTypeSelect, 'Please select a reminder type');
                        isValid = false;
                    }
                } else if (currentStep === 2) {
                    if (!reminderDateInput.value) {
                        showFieldError(reminderDateInput, 'Date is required');
                        isValid = false;
                    }
                    if (!reminderTimeInput.value) {
                        showFieldError(reminderTimeInput, 'Time is required');
                        isValid = false;
                    }
                }

                return isValid;
            }

            function showFieldError(field, message) {
                field.classList.add('is-invalid');

                // Remove existing error message
                const existingError = field.parentNode.querySelector('.invalid-feedback');
                if (existingError) {
                    existingError.remove();
                }

                // Add new error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = message;
                field.parentNode.appendChild(errorDiv);

                // Remove error on input
                field.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    const errorMsg = this.parentNode.querySelector('.invalid-feedback');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }, { once: true });
            }

            // Preview Updates
            function updatePreview() {
                const title = titleInput.value || 'Enter a title...';
                const description = descriptionInput.value || 'Add a description...';
                const reminderType = reminderTypeSelect;
                const reminderDate = reminderDateInput.value;
                const reminderTime = reminderTimeInput.value;

                previewTitle.textContent = title;
                previewDescription.textContent = description;

                // Update type
                const typeText = reminderType.options[reminderType.selectedIndex]?.text || '📝 Personal Reminder';
                previewType.textContent = typeText;

                // Update date/time
                if (reminderDate) {
                    const date = new Date(reminderDate);
                    previewDate.textContent = date.toLocaleDateString();
                } else {
                    previewDate.textContent = 'Select date';
                }

                if (reminderTime) {
                    const time = new Date('2000-01-01 ' + reminderTime);
                    previewTime.textContent = time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                } else {
                    previewTime.textContent = 'Select time';
                }
            }

            // Character Counter
            titleInput.addEventListener('input', function() {
                const count = this.value.length;
                document.getElementById('titleCount').textContent = count;
                updatePreview();
            });

            // Event Listeners
            [titleInput, descriptionInput, reminderTypeSelect, reminderDateInput, reminderTimeInput].forEach(element => {
                element.addEventListener('input', updatePreview);
                element.addEventListener('change', updatePreview);
            });

            // Quick Date Selection
            document.querySelectorAll('.quick-date').forEach(button => {
                button.addEventListener('click', function() {
                    const days = parseInt(this.dataset.days);
                    const date = new Date();
                    date.setDate(date.getDate() + days);

                    reminderDateInput.value = date.toISOString().split('T')[0];
                    updatePreview();

                    // Visual feedback
                    document.querySelectorAll('.quick-date').forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Recurring Options Toggle
            isRecurringCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    recurringOptions.style.display = 'block';
                } else {
                    recurringOptions.style.display = 'none';
                }
            });

            // Form Submission
            document.getElementById('reminderForm').addEventListener('submit', function(e) {
                if (!validateCurrentStep()) {
                    e.preventDefault();
                    return false;
                }

                // Show loading state
                const submitBtn = document.getElementById('createReminderBtn');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
                submitBtn.disabled = true;
            });

            // Initialize
            updatePreview();
        });
    </script>
@endsection
