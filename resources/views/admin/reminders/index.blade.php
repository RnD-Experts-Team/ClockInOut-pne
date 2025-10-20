@extends('layouts.app')

@section('title', 'Reminders Management')

@section('content')
    <div class="reminders-dashboard">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="header-left">
                <div class="header-title">
                    <h1><i class="fas fa-bell"></i> Reminders Management</h1>
                    <p class="header-subtitle">Manage your personal reminders and notifications</p>
                </div>
            </div>
            <div class="header-right">
                <div class="header-actions">
                    <a href="{{ route('reminders.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Reminder
                    </a>
                    <div class="view-switcher">
                        <button class="view-btn active" data-view="cards">
                            <i class="fas fa-th-large"></i> Cards
                        </button>
                        <button class="view-btn" data-view="list">
                            <i class="fas fa-list"></i> List
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-card total-card" data-count="{{ $statistics['total'] ?? 0 }}">
                <div class="stat-icon total"><i class="fas fa-bell"></i></div>
                <div class="stat-content">
                    <div class="stat-number" data-target="{{ $statistics['total'] ?? 0 }}">0</div>
                    <div class="stat-label">Total Reminders</div>
                    <div class="stat-trend"><i class="fas fa-chart-line"></i><span>All Time</span></div>
                </div>
            </div>

            <div class="stat-card pending-card" data-count="{{ $statistics['pending'] ?? 0 }}">
                <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
                <div class="stat-content">
                    <div class="stat-number" data-target="{{ $statistics['pending'] ?? 0 }}">0</div>
                    <div class="stat-label">Pending</div>
                    <div class="stat-trend"><i class="fas fa-hourglass-half"></i><span>Waiting</span></div>
                </div>
            </div>

            <div class="stat-card overdue-card" data-count="{{ $statistics['overdue'] ?? 0 }}">
                <div class="stat-icon overdue"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-content">
                    <div class="stat-number" data-target="{{ $statistics['overdue'] ?? 0 }}">0</div>
                    <div class="stat-label">Overdue</div>
                    <div class="stat-trend"><i class="fas fa-warning"></i><span>Needs Action</span></div>
                </div>
            </div>

            <div class="stat-card upcoming-card" data-count="{{ $statistics['upcoming_7_days'] ?? 0 }}">
                <div class="stat-icon upcoming"><i class="fas fa-calendar-week"></i></div>
                <div class="stat-content">
                    <div class="stat-number" data-target="{{ $statistics['upcoming_7_days'] ?? 0 }}">0</div>
                    <div class="stat-label">This Week</div>
                    <div class="stat-trend"><i class="fas fa-calendar-alt"></i><span>Upcoming</span></div>
                </div>
            </div>

            <div class="stat-card completed-card" data-count="{{ $statistics['sent'] ?? 0 }}">
                <div class="stat-icon completed"><i class="fas fa-check-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-number" data-target="{{ $statistics['sent'] ?? 0 }}">0</div>
                    <div class="stat-label">Completed</div>
                    <div class="stat-trend"><i class="fas fa-thumbs-up"></i><span>Done</span></div>
                </div>
            </div>
        </div>

        <!-- Grid -->
        <div class="content-grid">
            <!-- Main -->
            <div class="main-content">
                <!-- Controls -->
                <div class="content-controls">
                    <div class="search-container">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="reminderSearch" placeholder="Search reminders..." class="search-input">
                        </div>
                    </div>

                    <div class="filter-container">
                        <select id="statusFilter" class="custom-select">
                            <option value="">All Statuses</option>
                            <option value="pending">📅 Pending</option>
                            <option value="sent">✅ Completed</option>
                            <option value="dismissed">❌ Dismissed</option>
                            <option value="snoozed">😴 Snoozed</option>
                        </select>

                        <select id="typeFilter" class="custom-select">
                            <option value="">All Types</option>
                            <option value="general">📝 General</option>
                            <option value="maintenance_followup">🔧 Maintenance</option>
                            <option value="lease_renewal">🏠 Lease Renewal</option>
                            <option value="payment_due">💰 Payment</option>
                        </select>

                        <select id="priorityFilter" class="custom-select">
                            <option value="">All Priorities</option>
                            <option value="high">🔴 High</option>
                            <option value="normal">🟡 Normal</option>
                            <option value="low">🟢 Low</option>
                        </select>
                    </div>

                    <div class="bulk-actions" style="display:none;">
                        <select id="bulkActionSelect" class="custom-select">
                            <option value="">Bulk Actions</option>
                            <option value="dismiss">Dismiss Selected</option>
                            <option value="delete">Delete Selected</option>
                            <option value="mark_sent">Mark as Complete</option>
                        </select>
                        <button id="applyBulkAction" class="btn btn-secondary">Apply</button>
                    </div>
                </div>

                <!-- Containers -->
                <div class="reminders-container">
                    <!-- Cards View -->
                    <div id="cardsView" class="reminders-cards active">
                        @forelse($reminders as $reminder)
                            <div class="reminder-card {{ $reminder->status }} type-{{ $reminder->reminder_type }} priority-{{ $reminder->priority ?? 'normal' }}" data-reminder-id="{{ $reminder->id }}">
                                <div class="reminder-header">
                                    <div class="reminder-checkbox">
                                        <input type="checkbox" class="reminder-select" value="{{ $reminder->id }}">
                                    </div>
                                    <div class="reminder-priority priority-{{ $reminder->priority ?? 'normal' }}">
                                        @switch($reminder->priority ?? 'normal')
                                            @case('high') <i class="fas fa-exclamation-circle"></i> @break
                                            @case('low') <i class="fas fa-info-circle"></i> @break
                                            @default <i class="fas fa-circle"></i>
                                        @endswitch
                                    </div>
                                    <div class="reminder-type">
                                        @switch($reminder->reminder_type)
                                            @case('maintenance_followup') <span class="type-badge maintenance">🔧 Maintenance</span> @break
                                            @case('lease_renewal')       <span class="type-badge lease">🏠 Lease</span> @break
                                            @case('payment_due')         <span class="type-badge payment">💰 Payment</span> @break
                                            @default                      <span class="type-badge general">📝 General</span>
                                        @endswitch
                                    </div>
                                </div>

                                <div class="reminder-body">
                                    <div class="reminder-title">
                                        <h3>{{ $reminder->title }}</h3>
                                    </div>
                                    <div class="reminder-description">
                                        <p>{{ \Illuminate\Support\Str::limit($reminder->description ?? '', 120) }}</p>
                                    </div>
                                    <div class="reminder-datetime">
                                        <div class="datetime-info">
                                            <i class="fas fa-calendar"></i>
                                            <span class="date">{{ $reminder->reminder_date->format('M d, Y') }}</span>
                                            <i class="fas fa-clock"></i>
                                            <span class="time">{{ $reminder->reminder_time ? $reminder->reminder_time->format('h:i A') : 'All Day' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="reminder-footer">
                                    <div class="reminder-status">
                                        @switch($reminder->status)
                                            @case('pending')
                                                @if($reminder->reminder_date->isPast())
                                                    <span class="status-badge overdue">⚠️ Overdue</span>
                                                @else
                                                    <span class="status-badge pending">📅 Pending</span>
                                                @endif
                                                @break
                                            @case('sent')      <span class="status-badge completed">✅ Completed</span> @break
                                            @case('dismissed') <span class="status-badge dismissed">❌ Dismissed</span> @break
                                            @case('snoozed')   <span class="status-badge snoozed">😴 Snoozed</span> @break
                                        @endswitch
                                    </div>

                                    <div class="reminder-actions">
                                        @if($reminder->status === 'pending')
                                            <button class="action-btn snooze-btn" onclick="snoozeReminder({{ $reminder->id }})" title="Snooze"><i class="fas fa-clock"></i></button>
                                            <button class="action-btn dismiss-btn" onclick="dismissReminder({{ $reminder->id }})" title="Dismiss"><i class="fas fa-times"></i></button>
                                            <button class="action-btn complete-btn" onclick="markComplete({{ $reminder->id }})" title="Mark Complete"><i class="fas fa-check"></i></button>
                                        @endif
                                        <a href="{{ route('reminders.show', $reminder->id) }}" class="action-btn view-btn" title="View Details"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('reminders.edit', $reminder->id) }}" class="action-btn edit-btn" title="Edit"><i class="fas fa-edit"></i></a>
                                        <button class="action-btn delete-btn" onclick="deleteReminder({{ $reminder->id }})" title="Delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-bell-slash"></i></div>
                                <h3>No Reminders Found</h3>
                                <p>You haven't created any reminders yet. Start by adding your first reminder!</p>
                                <a href="{{ route('reminders.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Your First Reminder
                                </a>
                            </div>
                        @endforelse
                    </div>

                    <!-- List View -->
                    <div id="listView" class="reminders-list">
                        <div class="table-container">
                            <table class="reminders-table">
                                <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>Priority</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($reminders as $reminder)
                                    <tr class="reminder-row {{ $reminder->status }} type-{{ $reminder->reminder_type }} priority-{{ $reminder->priority ?? 'normal' }}" data-reminder-id="{{ $reminder->id }}">
                                        <td><input type="checkbox" class="reminder-select" value="{{ $reminder->id }}"></td>
                                        <td>
                                            <div class="priority-indicator priority-{{ $reminder->priority ?? 'normal' }}">
                                                @switch($reminder->priority ?? 'normal')
                                                    @case('high') <i class="fas fa-exclamation-circle text-danger"></i> @break
                                                    @case('low')  <i class="fas fa-info-circle text-info"></i> @break
                                                    @default      <i class="fas fa-circle text-warning"></i>
                                                @endswitch
                                            </div>
                                        </td>
                                        <td>
                                            <div class="reminder-info">
                                                <strong>{{ $reminder->title }}</strong>
                                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($reminder->description ?? '', 50) }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @switch($reminder->reminder_type)
                                                @case('maintenance_followup') <span class="type-badge maintenance">🔧 Maintenance</span> @break
                                                @case('lease_renewal')       <span class="type-badge lease">🏠 Lease</span> @break
                                                @case('payment_due')         <span class="type-badge payment">💰 Payment</span> @break
                                                @default                      <span class="type-badge general">📝 General</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="datetime-display">
                                                <div class="date">{{ $reminder->reminder_date->format('M d, Y') }}</div>
                                                <div class="time text-muted">{{ $reminder->reminder_time ? $reminder->reminder_time->format('h:i A') : 'All Day' }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            @switch($reminder->status)
                                                @case('pending')
                                                    @if($reminder->reminder_date->isPast())
                                                        <span class="status-badge overdue">⚠️ Overdue</span>
                                                    @else
                                                        <span class="status-badge pending">📅 Pending</span>
                                                    @endif
                                                    @break
                                                @case('sent')      <span class="status-badge completed">✅ Completed</span> @break
                                                @case('dismissed') <span class="status-badge dismissed">❌ Dismissed</span> @break
                                                @case('snoozed')   <span class="status-badge snoozed">😴 Snoozed</span> @break
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                @if($reminder->status === 'pending')
                                                    <button class="btn-sm btn-warning" onclick="snoozeReminder({{ $reminder->id }})" title="Snooze"><i class="fas fa-clock"></i></button>
                                                    <button class="btn-sm btn-success" onclick="markComplete({{ $reminder->id }})" title="Complete"><i class="fas fa-check"></i></button>
                                                @endif
                                                <a href="{{ route('reminders.show', $reminder->id) }}" class="btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="{{ route('reminders.edit', $reminder->id) }}" class="btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <button class="btn-sm btn-danger" onclick="deleteReminder({{ $reminder->id }})" title="Delete"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty-state-inline">
                                                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                                <p>No reminders found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                @if($reminders->hasPages())
                    <div class="pagination-container">
                        {{ $reminders->links() }}
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-card">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    <div class="quick-actions">
                        <a href="{{ route('reminders.create') }}" class="action-link create">
                            <i class="fas fa-plus"></i> Add New Reminder
                        </a>
                        <button onclick="showDueReminders()" class="action-link due">
                            <i class="fas fa-bell"></i> View Due Reminders
                        </button>
                        <button onclick="exportReminders()" class="action-link export">
                            <i class="fas fa-download"></i> Export Reminders
                        </button>
                        <a href="{{ route('calendar.index') }}" class="action-link calendar">
                            <i class="fas fa-calendar"></i> View Calendar
                        </a>
                    </div>
                </div>

                <!-- Types Breakdown -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-chart-pie"></i> By Type</h3>
                    <div class="type-breakdown">
                        @foreach($statistics['by_type'] ?? [] as $type => $count)
                            <div class="breakdown-item">
                                <div class="breakdown-label">
                                    @switch($type)
                                        @case('maintenance_followup') <span>🔧 Maintenance</span> @break
                                        @case('lease_renewal')       <span>🏠 Lease Renewal</span> @break
                                        @case('payment_due')         <span>💰 Payment</span> @break
                                        @default                      <span>📝 {{ ucwords(str_replace('_', ' ', $type)) }}</span>
                                    @endswitch
                                </div>
                                <div class="breakdown-count">{{ $count }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-history"></i> Recent Activity</h3>
                    @php
                        $recent = \Illuminate\Support\Collection::make($reminders instanceof \Illuminate\Pagination\AbstractPaginator ? $reminders->items() : $reminders)->take(5);
                    @endphp
                    <div class="activity-feed">
                        @foreach($recent as $rem)
                            <div class="activity-item">
                                <div class="activity-icon">
                                    @if($rem->status === 'pending')
                                        <i class="fas fa-clock text-warning"></i>
                                    @elseif($rem->status === 'sent')
                                        <i class="fas fa-check text-success"></i>
                                    @else
                                        <i class="fas fa-times text-muted"></i>
                                    @endif
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">{{ \Illuminate\Support\Str::limit($rem->title, 25) }}</div>
                                    <div class="activity-time">{{ $rem->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Snooze Modal (Bootstrap 4 markup; if using v5, change data-dismiss -> data-bs-dismiss) -->
    <div class="modal fade" id="snoozeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Snooze Reminder</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>How long would you like to snooze this reminder?</p>
                    <div class="snooze-options">
                        <button class="btn btn-outline-primary" data-minutes="15">15 minutes</button>
                        <button class="btn btn-outline-primary" data-minutes="30">30 minutes</button>
                        <button class="btn btn-outline-primary" data-minutes="60">1 hour</button>
                        <button class="btn btn-outline-primary" data-minutes="240">4 hours</button>
                        <button class="btn btn-outline-primary" data-minutes="1440">1 day</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Modern Reminders Dashboard Styles */
        .reminders-dashboard {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
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
            gap: 20px;
        }

        .btn-primary {
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        .view-switcher {
            display: flex;
            gap: 5px;
            background: rgba(255,255,255,0.2);
            padding: 5px;
            border-radius: 12px;
        }

        .view-btn {
            padding: 10px 16px;
            background: transparent;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .view-btn.active, .view-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Statistics Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            padding: 30px;
            margin-top: -10px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--card-color, #667eea);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        /* Card Colors */
        .total-card { --card-color: #667eea; }
        .pending-card { --card-color: #ffc107; }
        .overdue-card { --card-color: #dc3545; }
        .upcoming-card { --card-color: #17a2b8; }
        .completed-card { --card-color: #28a745; }

        .stat-icon {
            width: 65px;
            height: 65px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-icon.total { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.pending { background: linear-gradient(135deg, #ffc107, #ffcd39); }
        .stat-icon.overdue { background: linear-gradient(135deg, #dc3545, #e55a6b); }
        .stat-icon.upcoming { background: linear-gradient(135deg, #17a2b8, #20c0db); }
        .stat-icon.completed { background: linear-gradient(135deg, #28a745, #34ce57); }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
            line-height: 1;
        }

        .stat-label {
            font-size: 1.1rem;
            color: #7f8c8d;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            color: #95a5a6;
        }

        /* Content Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 30px;
            padding: 0 30px 30px;
        }

        .main-content {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }

        /* Content Controls */
        .content-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            position: relative;
            min-width: 300px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .search-input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filter-container {
            display: flex;
            gap: 12px;
        }

        .custom-select {
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            background: white;
            color: #495057;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .custom-select:focus {
            border-color: #667eea;
            outline: none;
        }

        /* Reminder Cards */
        .reminders-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .reminders-list {
            display: none;
        }

        .reminders-list.active {
            display: block;
        }

        .reminders-cards.active {
            display: grid;
        }

        .reminder-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            overflow: hidden;
        }

        .reminder-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .reminder-card.overdue {
            border-left: 4px solid #dc3545;
        }

        .reminder-card.pending {
            border-left: 4px solid #ffc107;
        }

        .reminder-card.sent {
            border-left: 4px solid #28a745;
        }

        .reminder-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 20px 15px;
            border-bottom: 1px solid #f8f9fa;
        }

        .reminder-priority.priority-high {
            color: #dc3545;
        }

        .reminder-priority.priority-normal {
            color: #ffc107;
        }

        .reminder-priority.priority-low {
            color: #28a745;
        }

        .type-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
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

        .type-badge.general {
            background: #95a5a6;
            color: white;
        }

        .reminder-body {
            padding: 15px 20px;
        }

        .reminder-title h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .reminder-description p {
            margin: 0;
            color: #7f8c8d;
            line-height: 1.5;
        }

        .reminder-datetime {
            margin-top: 15px;
        }

        .datetime-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #95a5a6;
            font-size: 0.9rem;
        }

        .datetime-info i {
            color: #667eea;
        }

        .reminder-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px 20px;
            background: #f8f9fa;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
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

        .reminder-actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            color: white;
        }

        .snooze-btn { background: #ffc107; }
        .dismiss-btn { background: #6c757d; }
        .complete-btn { background: #28a745; }
        .view-btn { background: #17a2b8; }
        .edit-btn { background: #667eea; }
        .delete-btn { background: #dc3545; }

        .action-btn:hover {
            transform: scale(1.1);
        }

        /* Table View */
        .table-container {
            overflow-x: auto;
        }

        .reminders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .reminders-table th,
        .reminders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .reminders-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .table-actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            font-size: 0.8rem;
        }

        .btn-warning { background: #ffc107; }
        .btn-success { background: #28a745; }
        .btn-info { background: #17a2b8; }
        .btn-primary { background: #667eea; }
        .btn-danger { background: #dc3545; }

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
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
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

        .breakdown-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .breakdown-item:last-child {
            border-bottom: none;
        }

        .breakdown-count {
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #95a5a6;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 4rem;
            color: #e9ecef;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #6c757d;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #adb5bd;
            margin-bottom: 30px;
        }

        /* Snooze Modal */
        .snooze-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .stats-row {
                grid-template-columns: 1fr;
                padding: 20px;
            }

            .content-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: auto;
            }

            .reminders-cards {
                grid-template-columns: 1fr;
            }

            .filter-container {
                flex-direction: column;
            }
        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View switcher
            const viewBtns = document.querySelectorAll('.view-btn');
            const cardsView = document.getElementById('cardsView');
            const listView  = document.getElementById('listView');

            viewBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const view = this.getAttribute('data-view');
                    viewBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    if (view === 'cards') { cardsView.classList.add('active'); listView.classList.remove('active'); }
                    else { listView.classList.add('active'); cardsView.classList.remove('active'); }
                });
            });

            // Stat animation
            function animateStatNumbers() {
                document.querySelectorAll('.stat-number').forEach(counter => {
                    const target = parseInt(counter.getAttribute('data-target')) || 0;
                    const increment = Math.max(1, Math.ceil(target / 50));
                    let current = 0;
                    const tick = () => {
                        current = Math.min(target, current + increment);
                        counter.textContent = current;
                        if (current < target) setTimeout(tick, 20);
                    };
                    tick();
                });
            }
            animateStatNumbers();

            // Search
            const searchInput = document.getElementById('reminderSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const q = this.value.toLowerCase();
                    const cards = document.querySelectorAll('.reminder-card');
                    const rows  = document.querySelectorAll('.reminder-row');

                    cards.forEach(card => {
                        const title = card.querySelector('.reminder-title h3')?.textContent.toLowerCase() || '';
                        const desc  = card.querySelector('.reminder-description p')?.textContent.toLowerCase() || '';
                        card.style.display = (title.includes(q) || desc.includes(q)) ? 'block' : 'none';
                    });

                    rows.forEach(row => {
                        const title = row.querySelector('.reminder-info strong')?.textContent.toLowerCase() || '';
                        row.style.display = title.includes(q) ? 'table-row' : 'none';
                    });
                });
            }

            // Filters (status + type + priority)
            const statusFilter   = document.getElementById('statusFilter');
            const typeFilter     = document.getElementById('typeFilter');
            const priorityFilter = document.getElementById('priorityFilter');
            [statusFilter, typeFilter, priorityFilter].forEach(el => el.addEventListener('change', applyFilters));

            function matchesFilters(el) {
                const status   = statusFilter.value;
                const type     = typeFilter.value;
                const priority = priorityFilter.value;

                if (status && !el.classList.contains(status)) return false;
                if (type && !el.classList.contains(`type-${type}`)) return false;
                if (priority && !el.classList.contains(`priority-${priority}`)) return false;
                return true;
            }

            function applyFilters() {
                const cards = document.querySelectorAll('.reminder-card');
                const rows  = document.querySelectorAll('.reminder-row');

                cards.forEach(card => { card.style.display = matchesFilters(card) ? 'block' : 'none'; });
                rows.forEach(row => { row.style.display   = matchesFilters(row)  ? 'table-row' : 'none'; });

                toggleBulkActions(); // keep bulk area in sync
            }

            // Bulk select
            const selectAll = document.getElementById('selectAll');
            const selects   = document.querySelectorAll('.reminder-select');

            function toggleBulkActions() {
                const anyChecked = document.querySelectorAll('.reminder-select:checked').length > 0;
                const bulk = document.querySelector('.bulk-actions');
                if (bulk) bulk.style.display = anyChecked ? 'flex' : 'none';
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    document.querySelectorAll('.reminder-select').forEach(cb => {
                        // only select visible rows/cards
                        const rowOrCard = cb.closest('.reminder-row, .reminder-card');
                        if (rowOrCard && rowOrCard.style.display !== 'none') cb.checked = selectAll.checked;
                    });
                    toggleBulkActions();
                });
            }

            selects.forEach(cb => cb.addEventListener('change', toggleBulkActions));
        });

        // --- Actions (AJAX)
        function snoozeReminder(id) {
            // Bootstrap 4: $('#snoozeModal').modal('show');
            // Bootstrap 5: new bootstrap.Modal(document.getElementById('snoozeModal')).show();
            if (window.$) { $('#snoozeModal').modal('show'); }
            document.getElementById('snoozeModal').setAttribute('data-reminder-id', id);
        }

        function dismissReminder(id) {
            if (!confirm('Are you sure you want to dismiss this reminder?')) return;
            fetch(`/reminders/${id}/dismiss`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
        }

        function markComplete(id) {
            fetch(`/reminders/${id}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
        }

        function deleteReminder(id) {
            if (!confirm('Are you sure you want to delete this reminder? This action cannot be undone.')) return;
            fetch(`/reminders/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
        }

        function bulkAction(action, ids) {
            fetch('/reminders/bulk-action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ action, reminder_ids: ids })
            }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
        }

        function showDueReminders() {
            // Hook up your desired “due reminders” filtering here (e.g., pre-fill filters / open a modal)
            document.getElementById('statusFilter').value = 'pending';
            applyFilters && applyFilters();
        }

        function exportReminders() {
            window.location.href = '/reminders/export';
        }

        // Snooze modal quick options
        document.addEventListener('DOMContentLoaded', function() {
            const btns = document.querySelectorAll('#snoozeModal .btn[data-minutes]');
            btns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const minutes = parseInt(this.getAttribute('data-minutes'), 10);
                    const id = document.getElementById('snoozeModal').getAttribute('data-reminder-id');
                    fetch(`/reminders/${id}/snooze`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ minutes })
                    }).then(r => r.json()).then(data => {
                        if (data.success) {
                            if (window.$) { $('#snoozeModal').modal('hide'); }
                            location.reload();
                        }
                    });
                });
            });
        });
    </script>
@endsection
