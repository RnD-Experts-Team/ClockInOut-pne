@extends('layouts.app')

@section('title', 'Edit Reminder')

@section('content')
    <div class="reminders-dashboard">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="header-left">
                <div class="header-title">
                    <h1><i class="fas fa-edit"></i> Edit Reminder</h1>
                    <p class="header-subtitle">Update reminder details, schedule, and notifications</p>
                </div>
            </div>
            <div class="header-right">
                <div class="header-actions">
                    <a href="{{ route('reminders.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('reminders.show', $reminder) }}" class="btn btn-primary">
                        <i class="fas fa-eye"></i> View Reminder
                    </a>
                </div>
            </div>
        </div>

        <div class="content-grid" style="padding-top:30px;">
            <!-- Main -->
            <div class="main-content">
                {{-- Flash success --}}
                @if (session('status'))
                    <div class="alert alert-success mb-3">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Validation errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        <strong>Fix the following:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('reminders.update', $reminder) }}" id="editReminderForm">
                    @csrf
                    @method('PUT')

                    <!-- Card: Basic Info -->
                    <div class="card-like">
                        <h3 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h3>
                        <div class="grid-2">
                            <div class="form-group">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control"
                                       value="{{ old('title', $reminder->title) }}" required maxlength="255">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="reminder_type" class="form-control" required>
                                    @php
                                        $type = old('reminder_type', $reminder->reminder_type);
                                        $types = [
                                          'maintenance_followup' => '🔧 Maintenance Follow-up',
                                          'custom_reminder'      => '📝 Custom Reminder',
                                          'expiration_alert'     => '⏳ Expiration Alert',
                                          'lease_renewal'        => '🏠 Lease Renewal',
                                          'payment_due'          => '💰 Payment Due',
                                        ];
                                    @endphp
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}" @selected($type===$key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="4" class="form-control">{{ old('description', $reminder->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Schedule -->
                    <div class="card-like">
                        <h3 class="section-title"><i class="fas fa-calendar"></i> Schedule</h3>
                        <div class="grid-3">
                            <div class="form-group">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="reminder_date" class="form-control"
                                       value="{{ old('reminder_date', optional($reminder->reminder_date)->format('Y-m-d')) }}"
                                       required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Time <span class="text-danger">*</span></label>
                                <input type="time" name="reminder_time" class="form-control"
                                       value="{{ old('reminder_time', optional($reminder->reminder_time)->format('H:i')) }}"
                                       required>
                                <small class="text-muted">24h format (HH:MM)</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label d-flex align-items-center gap-2">Priority</label>
                                @php $priority = old('priority', $reminder->priority ?? 'normal'); @endphp
                                <select name="priority" class="form-control">
                                    <option value="high" @selected($priority==='high')>🔴 High</option>
                                    <option value="normal" @selected($priority==='normal')>🟡 Normal</option>
                                    <option value="low" @selected($priority==='low')>🟢 Low</option>
                                </select>
                            </div>
                        </div>

                        {{-- Optional advance notice (if you support it) --}}
                        @php $advance = old('advance_notice', $reminder->advance_notice ?? null); @endphp
                        <div class="grid-3">
                            <div class="form-group">
                                <label class="form-label">Advance Notice (minutes)</label>
                                <input type="number" min="0" name="advance_notice" class="form-control"
                                       value="{{ $advance }}">
                                <small class="text-muted">Leave blank for none</small>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Recurrence -->
                    <div class="card-like">
                        <h3 class="section-title"><i class="fas fa-repeat"></i> Recurrence</h3>
                        <div class="grid-3">
                            <div class="form-group">
                                <label class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_recurring_cb"
                                           name="is_recurring" value="1"
                                        @checked(old('is_recurring', $reminder->is_recurring))>
                                    <span class="ms-1">This reminder repeats</span>
                                </label>
                            </div>

                            <div class="form-group recurrence-only">
                                <label class="form-label">Pattern</label>
                                @php $pattern = old('recurrence_pattern', $reminder->recurrence_pattern); @endphp
                                <select name="recurrence_pattern" class="form-control">
                                    <option value="">— Select —</option>
                                    <option value="daily"   @selected($pattern==='daily')>Daily</option>
                                    <option value="weekly"  @selected($pattern==='weekly')>Weekly</option>
                                    <option value="monthly" @selected($pattern==='monthly')>Monthly</option>
                                    <option value="yearly"  @selected($pattern==='yearly')>Yearly</option>
                                </select>
                            </div>

                            <div class="form-group recurrence-only">
                                <label class="form-label">Ends On (optional)</label>
                                <input type="date" name="recurrence_end" class="form-control"
                                       value="{{ old('recurrence_end', optional($reminder->recurrence_end)->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Card: Notifications -->
                    <div class="card-like">
                        <h3 class="section-title"><i class="fas fa-bell"></i> Notification Methods</h3>
                        @php
                            $methods = old('notification_methods', $reminder->notification_methods ?? []);
                            if (!is_array($methods)) { $methods = []; }
                        @endphp
                        <div class="grid-3">
                            <label class="form-check">
                                <input type="checkbox" class="form-check-input" name="notification_methods[]"
                                       value="email" @checked(in_array('email', $methods))>
                                <span class="ms-1"><i class="fas fa-envelope"></i> Email</span>
                            </label>

                            <label class="form-check">
                                <input type="checkbox" class="form-check-input" name="notification_methods[]"
                                       value="browser" @checked(in_array('browser', $methods))>
                                <span class="ms-1"><i class="fas fa-globe"></i> Browser</span>
                            </label>

                            <label class="form-check">
                                <input type="checkbox" class="form-check-input" name="notification_methods[]"
                                       value="sms" @checked(in_array('sms', $methods))>
                                <span class="ms-1"><i class="fas fa-sms"></i> SMS</span>
                            </label>
                        </div>
                    </div>

                    <!-- Card: Relations (optional) -->
                    <div class="card-like">
                        <h3 class="section-title"><i class="fas fa-link"></i> Related Item (optional)</h3>
                        <div class="grid-3">
                            <div class="form-group">
                                <label class="form-label">Related Model Type</label>
                                <input type="text" name="related_model_type" class="form-control"
                                       value="{{ old('related_model_type', $reminder->related_model_type) }}"
                                       placeholder="e.g. MaintenanceRequest, Lease">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Related Model ID</label>
                                <input type="number" name="related_model_id" class="form-control"
                                       value="{{ old('related_model_id', $reminder->related_model_id) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Calendar Event</label>
                                @if($reminder->calendar_event_id)
                                    <input type="text" class="form-control" value="Linked (ID: {{ $reminder->calendar_event_id }})" disabled>
                                    <small class="text-muted">Event will be kept in sync when you save.</small>
                                @else
                                    <input type="text" class="form-control" value="No calendar event linked" disabled>
                                    <small class="text-muted">An event can be created on save (controller logic).</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>

                        <a href="{{ route('reminders.show', $reminder) }}" class="btn btn-secondary-ghost">
                            <i class="fas fa-times"></i> Cancel
                        </a>

                        {{-- Delete (optional) --}}
                        <button type="button" class="btn btn-danger-ghost ms-auto" onclick="confirmDelete()">
                            <i class="fas fa-trash"></i> Delete Reminder
                        </button>
                    </div>
                </form>

                <form id="deleteForm" method="POST" action="{{ route('reminders.destroy', $reminder) }}" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-card">
                    <h3><i class="fas fa-info-circle"></i> Summary</h3>
                    <div class="breakdown-item">
                        <div>Created</div>
                        <div class="breakdown-count">{{ $reminder->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="breakdown-item">
                        <div>Status</div>
                        <div class="breakdown-count">{{ ucfirst($reminder->status) }}</div>
                    </div>
                    <div class="breakdown-item">
                        <div>Type</div>
                        <div class="breakdown-count">{{ ucwords(str_replace('_',' ', $reminder->reminder_type)) }}</div>
                    </div>
                    <div class="breakdown-item">
                        <div>Priority</div>
                        <div class="breakdown-count">{{ ucfirst($reminder->priority ?? 'normal') }}</div>
                    </div>
                    @if($reminder->is_recurring)
                        <div class="breakdown-item">
                            <div>Recurs</div>
                            <div class="breakdown-count">{{ ucfirst($reminder->recurrence_pattern) }}</div>
                        </div>
                    @endif
                </div>

                <div class="sidebar-card">
                    <h3><i class="fas fa-bolt"></i> Quick Links</h3>
                    <div class="quick-actions">
                        <a href="{{ route('reminders.index') }}" class="action-link">
                            <i class="fas fa-list"></i> All Reminders
                        </a>
                        <a href="{{ route('reminders.create') }}" class="action-link">
                            <i class="fas fa-plus"></i> Create New
                        </a>
                        <a href="{{ route('calendar.index') }}" class="action-link">
                            <i class="fas fa-calendar"></i> Calendar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Styles tailored to match your dashboard --}}
    <style>
        .card-like{
            background:#fff;border-radius:20px;padding:25px;margin-bottom:25px;
            box-shadow:0 8px 30px rgba(0,0,0,0.08); border:1px solid #e9ecef;
        }
        .section-title{margin:0 0 15px 0;color:#2c3e50;font-size:1.2rem;font-weight:700;display:flex;gap:10px;align-items:center}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
        .form-group{display:flex;flex-direction:column;gap:6px}
        .form-label{font-weight:600;color:#2c3e50}
        .form-control{border:2px solid #e9ecef;border-radius:12px;padding:12px;font-size:1rem}
        .form-control:focus{border-color:#667eea;outline:none;box-shadow:0 0 0 3px rgba(102,126,234,.1)}
        .form-check{display:flex;align-items:center;gap:10px}
        .form-check-input{width:18px;height:18px}
        .form-actions{display:flex;gap:12px;align-items:center;margin-top:8px}
        .btn-secondary-ghost{background:#f8f9fa;border:2px solid #e9ecef;color:#495057;padding:12px 18px;border-radius:12px;font-weight:600}
        .btn-secondary-ghost:hover{background:#e9ecef}
        .btn-danger-ghost{background:#fff;border:2px solid #dc3545;color:#dc3545;padding:12px 18px;border-radius:12px;font-weight:600}
        .btn-danger-ghost:hover{background:#dc3545;color:#fff}
        @media(max-width:1200px){.grid-3{grid-template-columns:1fr 1fr}}
        @media(max-width:768px){.grid-2,.grid-3{grid-template-columns:1fr}}
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Show/hide recurrence fields
            const recurCb = document.getElementById('is_recurring_cb');
            const recurFields = document.querySelectorAll('.recurrence-only');

            function toggleRecur() {
                const on = recurCb.checked;
                recurFields.forEach(el => {
                    el.style.display = on ? 'block' : 'none';
                    const inputs = el.querySelectorAll('input,select');
                    inputs.forEach(i => i.disabled = !on);
                });
            }

            toggleRecur();
            recurCb.addEventListener('change', toggleRecur);
        });

        function confirmDelete(){
            if(confirm('Delete this reminder? This action cannot be undone.')){
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
@endsection
