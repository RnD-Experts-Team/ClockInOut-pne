@extends('layouts.app')

@section('title', 'Calendar Dashboard')

@section('content')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css' rel='stylesheet' />

    <div class="calendar-dashboard">
        <!-- Enhanced Header with Notification Badge -->
        <div class="dashboard-header">
            <div class="header-left">
                <div class="header-title">
                    <h1><i class="fas fa-calendar-alt"></i> Calendar Dashboard</h1>
                    <p class="current-date">{{ $currentDate->format('F Y') }}</p>
                </div>
            </div>
            <div class="header-right">
                <!-- Notification Bell -->
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span id="notification-badge" class="notification-badge">0</span>
                </div>

                <div class="view-switcher">
                    <a href="{{ route('calendar.index') }}" class="view-btn active">
                        <i class="fas fa-calendar"></i> Month
                    </a>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Cards (Fixed Duplicates) -->
        <div class="stats-grid">
            <div class="stat-card expiration-card" data-count="{{ max(0, $statistics['lease_expirations'] ?? 0) }}">
                <div class="stat-icon expiration">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" data-target="{{ max(0, $statistics['lease_expirations'] ?? 0) }}">0</div>
                    <div class="stat-label">Lease Expirations</div>
                    <div class="stat-trend">
                        <i class="fas fa-clock"></i>
                        <span>This Month</span>
                    </div>
                </div>
            </div>

            <div class="stat-card maintenance-card" data-count="{{ max(0, $statistics['maintenance_requests'] ?? 0) }}">
                <div class="stat-icon maintenance">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" data-target="{{ max(0, $statistics['maintenance_requests'] ?? 0) }}">0</div>
                    <div class="stat-label">Maintenance Requests</div>
                    <div class="stat-trend">
                        <i class="fas fa-wrench"></i>
                        <span>Active</span>
                    </div>
                </div>
            </div>

            <div class="stat-card clock-card" data-count="{{ max(0, $statistics['clock_events'] ?? 0) }}">
                <div class="stat-icon clock">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" data-target="{{ max(0, $statistics['clock_events'] ?? 0) }}">0</div>
                    <div class="stat-label">Clock Events</div>
                    <div class="stat-trend">
                        <i class="fas fa-user-clock"></i>
                        <span>Today</span>
                    </div>
                </div>
            </div>

            <div class="stat-card renewal-card" data-count="{{ max(0, $statistics['lease_renewals'] ?? 0) }}">
                <div class="stat-icon renewal">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" data-target="{{ max(0, $statistics['lease_renewals'] ?? 0) }}">0</div>
                    <div class="stat-label">Lease Renewals</div>
                    <div class="stat-trend">
                        <i class="fas fa-calendar-check"></i>
                        <span>Pending</span>
                    </div>
                </div>
            </div>

            <div class="stat-card reminder-card" data-count="{{ max(0, $statistics['reminders'] ?? 0) }}">
                <div class="stat-icon reminder">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" data-target="{{ max(0, $statistics['reminders'] ?? 0) }}">0</div>
                    <div class="stat-label">Personal Reminders</div>
                    <div class="stat-trend">
                        <i class="fas fa-clock"></i>
                        <span>Active</span>
                    </div>
                </div>
            </div>

            <div class="stat-card admin-card" data-count="{{ max(0, $statistics['admin_actions'] ?? 0) }}">
                <div class="stat-icon admin">
                    <i class="fas fa-user-cog"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" data-target="{{ max(0, $statistics['admin_actions'] ?? 0) }}">0</div>
                    <div class="stat-label">Admin Actions</div>
                    <div class="stat-trend">
                        <i class="fas fa-shield-alt"></i>
                        <span>Recent</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Calendar Area -->
        <div class="calendar-container">
            <div class="calendar-main">
                <!-- Enhanced Calendar Filter Bar -->
                <div class="calendar-filters">
                    <div class="filter-section">
                        <div class="filter-group">
                            <label><i class="fas fa-filter"></i> Filter by Type:</label>
                            <select id="eventTypeFilter" class="custom-select">
                                <option value="">🌟 All Events</option>
                                <option value="expiration">🏠 Lease Expirations</option>
                                <option value="apartment_lease_renewal">🔄 Apartment Renewals</option>
                                <option value="lease_renewal">🔄 Lease Renewals</option>
                                <option value="maintenance_request">🔧 Maintenance Requests</option>
                                <option value="clock_event">⏰ Clock Events</option>
                                <option value="admin_action">👤 Admin Actions</option>
                                <option value="reminder">🔔 Personal Reminders</option>
                            </select>
                        </div>
                        <div class="filter-stats">
                            <span id="eventCount" class="event-count">Loading events...</span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="{{ route('reminders.create') }}" class="btn btn-success" title="Create New Reminder">
                            <i class="fas fa-plus"></i> New Reminder
                        </a>
                        <button id="refreshBtn" class="btn btn-primary" title="Refresh Calendar">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>

                <!-- FullCalendar Container -->
                <div class="calendar-wrapper">
                    <div id="calendarLoading" class="calendar-loading">
                        <div class="loading-spinner"></div>
                        <p>Loading calendar events...</p>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>

            <!-- Enhanced Sidebar -->
            <div class="calendar-sidebar">
                <!-- Upcoming Events Section -->
                <div class="sidebar-section">
                    <div class="section-header">
                        <h3><i class="fas fa-clock"></i> Upcoming Events</h3>
                        <span class="event-badge">{{ count($upcomingEvents->take(10)) }}</span>
                    </div>
                    <div class="upcoming-events">
                        @forelse($upcomingEvents->take(10) as $event)
                            <div class="upcoming-event fade-in" style="border-left-color: {{ $event->color_code ?? '#6c757d' }}">
                                <div class="event-date">
                                    <div class="date-day">{{ \Carbon\Carbon::parse($event->start_date)->format('j') }}</div>
                                    <div class="date-month">{{ \Carbon\Carbon::parse($event->start_date)->format('M') }}</div>
                                </div>
                                <div class="event-info">
                                    <div class="event-title">{{ $event->title }}</div>
                                    <div class="event-type">
                                        <i class="fas fa-tag"></i>
                                        {{ ucwords(str_replace('_', ' ', $event->event_type)) }}
                                    </div>
                                </div>
                                <div class="event-action">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </div>
                        @empty
                            <div class="no-events">
                                <i class="fas fa-calendar-times"></i>
                                <p>No upcoming events</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Enhanced Legend Section -->
                <div class="sidebar-section">
                    <div class="section-header">
                        <h3><i class="fas fa-palette"></i> Event Types</h3>
                    </div>
                    <div class="event-legend">
                        <div class="legend-item" data-type="expiration">
                            <div class="legend-color expiration-color">
                                <i class="fas fa-home"></i>
                            </div>
                            <span>Lease Expirations</span>
                            <div class="legend-count">{{ max(0, $statistics['lease_expirations'] ?? 0) }}</div>
                        </div>

                        <div class="legend-item" data-type="apartment_lease_renewal">
                            <div class="legend-color renewal-color">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <span>Apartment Renewals</span>
                            <div class="legend-count">{{ max(0, $statistics['lease_renewals'] ?? 0) }}</div>
                        </div>

                        <div class="legend-item" data-type="lease_renewal">
                            <div class="legend-color renewal-color">
                                <i class="fas fa-redo"></i>
                            </div>
                            <span>Lease Renewals</span>
                            <div class="legend-count">{{ max(0, $statistics['lease_renewals'] ?? 0) }}</div>
                        </div>

                        <div class="legend-item" data-type="maintenance_request">
                            <div class="legend-color maintenance-color">
                                <i class="fas fa-tools"></i>
                            </div>
                            <span>Maintenance Requests</span>
                            <div class="legend-count">{{ max(0, $statistics['maintenance_requests'] ?? 0) }}</div>
                        </div>

                        <div class="legend-item" data-type="clock_event">
                            <div class="legend-color clock-color">
                                <i class="fas fa-clock"></i>
                            </div>
                            <span>Clock Events</span>
                            <div class="legend-count">{{ max(0, $statistics['clock_events'] ?? 0) }}</div>
                        </div>

                        <div class="legend-item" data-type="admin_action">
                            <div class="legend-color admin-color">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <span>Admin Actions</span>
                            <div class="legend-count">{{ max(0, $statistics['admin_actions'] ?? 0) }}</div>
                        </div>

                        <div class="legend-item" data-type="reminder">
                            <div class="legend-color reminder-color">
                                <i class="fas fa-bell"></i>
                            </div>
                            <span>Personal Reminders</span>
                            <div class="legend-count">{{ max(0, $statistics['reminders'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Section -->
                <div class="sidebar-section">
                    <div class="section-header">
                        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    </div>
                    <div class="quick-actions">
                        <a href="{{ route('calendar.daily', date('Y-m-d')) }}" class="quick-action-btn">
                            <i class="fas fa-calendar-day"></i>
                            <span>Today's Events</span>
                        </a>
                        <a href="{{ route('reminders.create') }}" class="quick-action-btn">
                            <i class="fas fa-plus"></i>
                            <span>New Reminder</span>
                        </a>
                        <button class="quick-action-btn" id="printCalendar">
                            <i class="fas fa-print"></i>
                            <span>Print Calendar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Event Details Modal -->
    <div id="eventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <div class="modal-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="title-content">
                        <h3 id="eventModalTitle">Event Details</h3>
                        <span id="eventModalType" class="event-type-badge"></span>
                    </div>
                </div>
                <span class="close" onclick="hideModal()">
                    <i class="fas fa-times"></i>
                </span>
            </div>

            <div class="modal-body">
                <div id="eventModalContent" class="enhanced-content">
                    <!-- Content will be dynamically inserted here -->
                </div>
            </div>

            <div class="modal-footer">
                <div class="footer-left">
                    <button class="btn btn-outline" onclick="hideModal()">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
                <div class="footer-right">
                    <button class="btn btn-primary" id="viewFullDetails">
                        <i class="fas fa-external-link-alt"></i> View Full Details
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="loading-spinner large"></div>
            <p>Loading Calendar...</p>
        </div>
    </div>

    <style>
        /* ===== NOTIFICATION SYSTEM STYLES ===== */
        .notification-bell {
            position: relative;
            margin-right: 20px;
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            color: #667eea;
            font-size: 20px;
            transition: all 0.3s ease;
        }

        .notification-bell:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: bold;
            display: none;
            min-width: 20px;
            text-align: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .reminder-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            z-index: 9999;
            max-width: 350px;
            animation: slideInFromRight 0.5s ease-out;
            border-left: 4px solid #ffc107;
        }

        .reminder-toast-content h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }

        .reminder-toast-content p {
            margin: 0 0 15px 0;
            color: #666;
            font-size: 14px;
            line-height: 1.4;
        }

        .btn-dismiss {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-dismiss:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        @keyframes slideInFromRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* ===== BASE DASHBOARD STYLES ===== */
        .calendar-dashboard {
            padding: 30px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Enhanced Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
        }

        .header-title h1 {
            margin: 0;
            font-size: 2.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .current-date {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .view-switcher {
            display: flex;
            gap: 5px;
            background: #f8f9fa;
            padding: 5px;
            border-radius: 10px;
        }

        .view-btn {
            padding: 12px 20px;
            border: none;
            text-decoration: none;
            border-radius: 8px;
            color: #666;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-btn:hover {
            background: #e9ecef;
            color: #333;
            transform: translateY(-2px);
        }

        .view-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        /* Enhanced Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--card-color), var(--card-color-light));
        }

        .expiration-card { --card-color: #fd7e14; --card-color-light: #ff922b; }
        .maintenance-card { --card-color: #dc3545; --card-color-light: #e55368; }
        .clock-card { --card-color: #17a2b8; --card-color-light: #20c0db; }
        .admin-card { --card-color: #28a745; --card-color-light: #34ce57; }
        .renewal-card { --card-color: #17a2b8; --card-color-light: #20c0db; }
        .reminder-card { --card-color: #ffc107; --card-color-light: #ffcd39; }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .stat-icon.expiration { background: linear-gradient(135deg, #fd7e14, #ff922b); }
        .stat-icon.maintenance { background: linear-gradient(135deg, #dc3545, #e55368); }
        .stat-icon.clock { background: linear-gradient(135deg, #17a2b8, #20c0db); }
        .stat-icon.admin { background: linear-gradient(135deg, #28a745, #34ce57); }
        .stat-icon.renewal { background: linear-gradient(135deg, #17a2b8, #20c0db); }
        .stat-icon.reminder { background: linear-gradient(135deg, #ffc107, #ffcd39); }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 5px;
            color: #333;
        }

        .stat-label {
            color: #666;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #28a745;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Calendar Container */
        .calendar-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }

        .calendar-main {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
            padding: 25px;
            position: relative;
        }

        /* Enhanced Filter Bar */
        .calendar-filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }

        .filter-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filter-group label {
            font-weight: 600;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .custom-select {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            background: white;
            font-size: 0.95rem;
            font-weight: 500;
            min-width: 200px;
            transition: all 0.3s ease;
        }

        .custom-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .event-count {
            background: #e9ecef;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #495057;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
            color: white;
        }

        .btn-outline {
            background: transparent;
            color: #6c757d;
            border: 2px solid #e9ecef;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* Calendar Wrapper */
        .calendar-wrapper {
            position: relative;
            min-height: 600px;
        }

        .calendar-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 10;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        .loading-spinner.large {
            width: 60px;
            height: 60px;
            border-width: 6px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Enhanced Calendar Styles */
        #calendar { height: 650px; }

        .fc-event {
            font-size: 11px !important;
            padding: 2px 6px !important;
            margin: 1px 0 !important;
            border-radius: 6px !important;
            border: none !important;
            opacity: 0.9;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .fc-event:hover {
            opacity: 1 !important;
            transform: scale(1.05);
            z-index: 100;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        /* Event type colors */
        .fc-event.event-expiration { background: linear-gradient(135deg, #fd7e14, #ff922b) !important; }
        .fc-event.event-maintenance_request { background: linear-gradient(135deg, #dc3545, #e55368) !important; }
        .fc-event.event-clock_event { background: linear-gradient(135deg, #17a2b8, #20c0db) !important; }
        .fc-event.event-admin_action { background: linear-gradient(135deg, #28a745, #34ce57) !important; }
        .fc-event.event-reminder { background: linear-gradient(135deg, #ffc107, #ffcd39) !important; color: #000 !important; }
        .fc-event.event-apartment_lease_renewal { background: linear-gradient(135deg, #fd7e14, #ff922b) !important; }
        .fc-event.event-lease_renewal { background: linear-gradient(135deg, #fd7e14, #ff922b) !important; }

        /* Enhanced Sidebar */
        .calendar-sidebar {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .sidebar-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
            padding: 25px;
            transition: all 0.3s ease;
        }

        .sidebar-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 35px rgba(0,0,0,0.15);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fa;
        }

        .section-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .event-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* Enhanced Upcoming Events */
        .upcoming-event {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-left: 4px solid;
            background: #f8f9fa;
            margin-bottom: 12px;
            border-radius: 0 10px 10px 0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upcoming-event:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .event-date {
            text-align: center;
            min-width: 45px;
        }

        .date-day {
            font-size: 1.4rem;
            font-weight: 800;
            color: #333;
            line-height: 1;
        }

        .date-month {
            font-size: 0.8rem;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
        }

        .event-info { flex: 1; }

        .event-title {
            font-weight: 600;
            margin-bottom: 4px;
            color: #333;
        }

        .event-type {
            font-size: 0.8rem;
            color: #666;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .event-action {
            color: #ccc;
            transition: all 0.3s ease;
        }

        .upcoming-event:hover .event-action {
            color: #667eea;
            transform: translateX(3px);
        }

        .no-events {
            text-align: center;
            padding: 30px;
            color: #999;
        }

        .no-events i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Enhanced Legend */
        .legend-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .legend-item:hover { background: #f8f9fa; }
        .legend-item.active {
            background: #e3f2fd;
            border: 2px solid #2196f3;
        }

        .legend-color {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }

        .expiration-color { background: linear-gradient(135deg, #fd7e14, #ff922b); }
        .maintenance-color { background: linear-gradient(135deg, #dc3545, #e55368); }
        .clock-color { background: linear-gradient(135deg, #17a2b8, #20c0db); }
        .admin-color { background: linear-gradient(135deg, #28a745, #34ce57); }
        .reminder-color { background: linear-gradient(135deg, #ffc107, #ffcd39); }
        .renewal-color { background: linear-gradient(135deg, #fd7e14, #ff922b); }

        .legend-count {
            margin-left: auto;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #495057;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            color: #495057;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .quick-action-btn:hover {
            background: #e9ecef;
            border-color: #667eea;
            color: #667eea;
            transform: translateY(-2px);
        }

        /* ENHANCED MODAL */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            width: 95%;
            max-width: 750px;
            max-height: 90vh;
            border-radius: 20px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
            transform: scale(0.8) translateY(50px);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal.show .modal-content {
            transform: scale(1) translateY(0);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: none;
        }

        .modal-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .modal-icon {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .title-content h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .event-type-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .close {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .close:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
            flex: 1;
            overflow-y: auto;
            max-height: 60vh;
        }

        .enhanced-content {
            display: grid;
            gap: 20px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .detail-item:hover {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
        }

        .detail-icon {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            flex-shrink: 0;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-size: 0.85rem;
            color: #666;
            font-weight: 600;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 1rem;
            color: #333;
            font-weight: 500;
        }

        .modal-footer {
            padding: 25px 30px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-left, .footer-right {
            display: flex;
            gap: 10px;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.9);
            z-index: 3000;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .loading-content {
            text-align: center;
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .calendar-container {
                grid-template-columns: 1fr 300px;
            }
        }

        @media (max-width: 768px) {
            .calendar-dashboard { padding: 15px; }
            .dashboard-header { flex-direction: column; gap: 20px; text-align: center; }
            .header-title h1 { font-size: 2rem; }
            .calendar-container { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .calendar-filters { flex-direction: column; gap: 15px; }
            .filter-section { flex-direction: column; gap: 10px; }
            .custom-select { min-width: 100%; }
            #calendar { height: 500px; }
            .modal-content { width: 98%; }
            .detail-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .stat-card { padding: 15px; }
            .stat-number { font-size: 2rem; }
            .modal-header, .modal-body, .modal-footer { padding: 20px; }
        }
    </style>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Enhanced Calendar with Notifications loaded');

            // Initialize loading overlay
            const loadingOverlay = document.getElementById('loadingOverlay');
            const calendarLoading = document.getElementById('calendarLoading');

            if (loadingOverlay) loadingOverlay.style.display = 'flex';

            // Animate statistics cards
            animateStatCards();

            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) {
                console.error('Calendar element not found!');
                return;
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                height: 650,
                dayMaxEvents: false,
                moreLinkClick: 'popover',
                eventDisplay: 'block',
                displayEventTime: false,

                loading: function(isLoading) {
                    if (calendarLoading) {
                        calendarLoading.style.display = isLoading ? 'block' : 'none';
                    }
                },

                events: function(fetchInfo, successCallback, failureCallback) {
                    console.log('Fetching events...');
                    const eventType = document.getElementById('eventTypeFilter')?.value || '';

                    const url = `{{ route('calendar.events') }}?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}&event_type=${eventType}`;

                    fetch(url)
                        .then(response => {
                            if (!response.ok) throw new Error(`HTTP ${response.status}`);
                            return response.json();
                        })
                        .then(data => {
                            let eventsArray = [];

                            if (Array.isArray(data)) {
                                eventsArray = data;
                            } else if (data && typeof data === 'object') {
                                if (data.events && Array.isArray(data.events)) {
                                    eventsArray = data.events;
                                } else if (data.data && Array.isArray(data.data)) {
                                    eventsArray = data.data;
                                } else {
                                    eventsArray = Object.values(data).filter(item =>
                                        item && typeof item === 'object' && item.title
                                    );
                                }
                            }

                            updateEventCount(eventsArray.length);

                            const formattedEvents = eventsArray.map((event, index) => ({
                                id: event.id || `event-${index}`,
                                title: event.title || 'Untitled Event',
                                start: event.start || event.start_date || event.date,
                                allDay: event.allDay !== undefined ? event.allDay : true,
                                backgroundColor: event.color || event.backgroundColor || event.color_code || '#6c757d',
                                borderColor: event.color || event.borderColor || event.color_code || '#6c757d',
                                textColor: event.textColor || '#ffffff',
                                className: [`event-${event.event_type || event.extendedProps?.event_type || 'default'}`],
                                extendedProps: {
                                    description: event.description || event.extendedProps?.description || '',
                                    event_type: event.event_type || event.extendedProps?.event_type || 'default',
                                    related_model_id: event.related_model_id || event.extendedProps?.related_model_id,
                                    related_model_type: event.related_model_type || event.extendedProps?.related_model_type,
                                    additional_data: event.additional_data || event.extendedProps?.additional_data || {}
                                }
                            }));

                            successCallback(formattedEvents);

                            if (loadingOverlay) {
                                setTimeout(() => {
                                    loadingOverlay.style.display = 'none';
                                }, 500);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching events:', error);
                            updateEventCount(0);
                            successCallback([]);
                            if (loadingOverlay) loadingOverlay.style.display = 'none';
                        });
                },

                eventClick: function(info) {
                    showEventDetails(info.event);
                },

                dateClick: function(info) {
                    window.location.href = `{{ route('calendar.daily', ':date') }}`.replace(':date', info.dateStr);
                },

                eventDidMount: function(info) {
                    info.el.title = `${info.event.title}\\n${info.event.extendedProps.description || 'No description'}`;
                    info.el.classList.add('fade-in');
                }
            });

            calendar.render();
            setupEventListeners(calendar);
            setupModal();

            // ===== PERSONAL REMINDER NOTIFICATION SYSTEM =====
            class PersonalReminderNotifications {
                constructor() {
                    this.pollingInterval = null;
                    this.lastNotificationTime = null;
                    this.isPolling = false;
                    this.notificationDebounce = 600000; // 10 minutes
                }

                async init() {
                    await this.requestNotificationPermission();
                    this.startPolling();
                    this.checkImmediately();
                }

                async requestNotificationPermission() {
                    if ("Notification" in window) {
                        if (Notification.permission === "default") {
                            await Notification.requestPermission();
                        }
                    }
                }

                startPolling() {
                    if (this.isPolling) return;

                    this.isPolling = true;

                    // Poll every 5 minutes (300,000 milliseconds)
                    this.pollingInterval = setInterval(() => {
                        this.checkForReminders();
                    }, 300000);

                    console.log('Personal reminder polling started (5-minute intervals)');
                }

                stopPolling() {
                    if (this.pollingInterval) {
                        clearInterval(this.pollingInterval);
                        this.pollingInterval = null;
                    }
                    this.isPolling = false;
                }

                checkImmediately() {
                    this.checkForReminders();
                }

                async checkForReminders() {
                    console.log('🔔 POLLING: Checking for reminders...', new Date().toLocaleTimeString());

                    if (this.shouldSkipCheck()) {
                        console.log('⏭️ POLLING: Skipping check due to debounce');
                        return;
                    }

                    try {
                        console.log('📡 POLLING: Making API request to /check-reminders');

                        const response = await fetch('/check-reminders', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        console.log('📥 POLLING: Response status:', response.status);

                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}`);
                        }

                        const data = await response.json();
                        console.log('📄 POLLING: Response data:', data);

                        if (data.success && data.reminders && data.reminders.length > 0) {
                            console.log('🎉 POLLING: Found reminders:', data.reminders);
                            this.showReminders(data.reminders);
                            this.lastNotificationTime = Date.now();
                        } else {
                            console.log('📭 POLLING: No reminders found or empty response');
                            // ➕ ADD THIS: Update badge to 0 when no reminders
                            this.updateNotificationBadge(0);
                        }

                    } catch (error) {
                        console.error('❌ POLLING: Error checking reminders:', error);
                        this.handlePollingError(error);
                    }
                }

                shouldSkipCheck() {
                    if (!this.lastNotificationTime) return false;

                    const timeSinceLastNotification = Date.now() - this.lastNotificationTime;
                    return timeSinceLastNotification < this.notificationDebounce;
                }

                showReminders(reminders) {
                    reminders.forEach(reminder => {
                        this.showSingleReminder(reminder);
                    });

                    this.updateNotificationBadge(reminders.length);
                }

                showSingleReminder(reminder) {
                    // Browser notification
                    if (Notification.permission === "granted") {
                        const notification = new Notification(reminder.title, {
                            body: reminder.description || `Reminder: ${reminder.title}`,
                            icon: '/favicon.ico',
                            tag: `reminder-${reminder.id}`,
                            requireInteraction: false
                        });

                        setTimeout(() => notification.close(), 10000);

                        notification.onclick = () => {
                            window.focus();
                            this.openReminderModal(reminder);
                            notification.close();
                        };
                    }

                    // Dashboard toast notification
                    this.showToastNotification(reminder);

                    // Optional: Play sound
                    this.playNotificationSound();
                }

                showToastNotification(reminder) {
                    const toast = document.createElement('div');
                    toast.className = 'reminder-toast';
                    toast.innerHTML = `
                        <div class="reminder-toast-content">
                            <h4>${reminder.title}</h4>
                            <p>${reminder.description || 'Time for your reminder!'}</p>
                            <button onclick="window.reminderNotifications.dismissReminder(${reminder.id})" class="btn-dismiss">
                                Dismiss
                            </button>
                        </div>
                    `;

                    document.body.appendChild(toast);

                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 15000);
                }

                updateNotificationBadge(count) {
                    const badge = document.querySelector('#notification-badge');
                    if (badge) {
                        badge.textContent = count;
                        badge.style.display = count > 0 ? 'block' : 'none';
                    }
                }

                playNotificationSound() {
                    try {
                        const audio = new Audio('/sounds/notification.mp3');
                        audio.volume = 0.3;
                        audio.play().catch(() => {
                            // Ignore audio errors
                        });
                    } catch (error) {
                        // Audio not available, ignore
                    }
                }

                handlePollingError(error) {
                    console.warn('Reminder polling error:', error.message);
                }

                openReminderModal(reminder) {
                    // Use existing modal system or create reminder-specific modal
                    alert(`Reminder: ${reminder.title}\n${reminder.description || ''}`);
                }

                async dismissReminder(reminderId) {
                    try {
                        await fetch(`/reminders/${reminderId}/dismiss`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        // Remove toast if exists
                        const toasts = document.querySelectorAll('.reminder-toast');
                        toasts.forEach(toast => {
                            if (toast.innerHTML.includes(`dismissReminder(${reminderId})`)) {
                                toast.remove();
                            }
                        });

                    } catch (error) {
                        console.error('Error dismissing reminder:', error);
                    }
                }
            }

            // Initialize notification system
            window.reminderNotifications = new PersonalReminderNotifications();
            window.reminderNotifications.init();

            // Functions
            function animateStatCards() {
                const statNumbers = document.querySelectorAll('.stat-number[data-target]');
                statNumbers.forEach(stat => {
                    const target = parseInt(stat.getAttribute('data-target')) || 0;
                    const safeTarget = Math.max(0, target);
                    animateNumber(stat, 0, safeTarget, safeTarget === 0 ? 100 : 1000);
                });
            }

            function animateNumber(element, start, end, duration) {
                const safeStart = Math.max(0, start);
                const safeEnd = Math.max(0, end);

                if (safeEnd === 0) {
                    element.textContent = '0';
                    return;
                }

                const range = safeEnd - safeStart;
                if (range <= 0) {
                    element.textContent = safeEnd;
                    return;
                }

                const increment = 1;
                const stepTime = Math.max(1, Math.floor(duration / range));
                let current = safeStart;

                const timer = setInterval(() => {
                    current += increment;
                    element.textContent = Math.max(0, current);
                    if (current >= safeEnd) {
                        element.textContent = safeEnd;
                        clearInterval(timer);
                    }
                }, stepTime);
            }

            function updateEventCount(count) {
                const eventCountEl = document.getElementById('eventCount');
                if (eventCountEl) {
                    const safeCount = Math.max(0, count);
                    eventCountEl.textContent = `${safeCount} events found`;
                    eventCountEl.classList.add('fade-in');
                }
            }

            function setupEventListeners(calendar) {
                // Filter change
                const filterElement = document.getElementById('eventTypeFilter');
                if (filterElement) {
                    filterElement.addEventListener('change', function() {
                        calendar.refetchEvents();

                        const legendItems = document.querySelectorAll('.legend-item');
                        legendItems.forEach(item => item.classList.remove('active'));

                        if (this.value) {
                            const activeItem = document.querySelector(`[data-type="${this.value}"]`);
                            if (activeItem) activeItem.classList.add('active');
                        }
                    });
                }

                // Refresh button
                const refreshBtn = document.getElementById('refreshBtn');
                if (refreshBtn) {
                    refreshBtn.addEventListener('click', function() {
                        this.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i>';
                        calendar.refetchEvents();
                        setTimeout(() => {
                            this.innerHTML = '<i class="fas fa-sync-alt"></i>';
                        }, 1000);
                    });
                }

                // Print button
                const printBtn = document.getElementById('printCalendar');
                if (printBtn) {
                    printBtn.addEventListener('click', function() {
                        window.print();
                    });
                }

                // Legend and stat card interactions
                document.querySelectorAll('.legend-item, .stat-card').forEach(item => {
                    item.addEventListener('click', function() {
                        const eventType = this.getAttribute('data-type') ||
                            this.classList[1]?.replace('-card', '').replace('expiration', 'expiration').replace('maintenance', 'maintenance_request').replace('clock', 'clock_event').replace('admin', 'admin_action');

                        const filterSelect = document.getElementById('eventTypeFilter');
                        if (filterSelect && eventType) {
                            filterSelect.value = eventType;
                            filterSelect.dispatchEvent(new Event('change'));
                        }
                    });
                });
            }

            function setupModal() {
                const modal = document.getElementById('eventModal');

                window.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        hideModal();
                    }
                });

                window.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && modal.classList.contains('show')) {
                        hideModal();
                    }
                });
            }

            // Global functions
            window.showEventDetails = showEventDetails;
            window.hideModal = hideModal;
            window.navigateToDate = function(date) {
                calendar.gotoDate(date);
            };
        });

        // Clean up when page unloads
        window.addEventListener('beforeunload', function() {
            if (window.reminderNotifications) {
                window.reminderNotifications.stopPolling();
            }
        });

        function showEventDetails(event) {
            const modal = document.getElementById('eventModal');
            const titleEl = document.getElementById('eventModalTitle');
            const typeEl = document.getElementById('eventModalType');
            const contentEl = document.getElementById('eventModalContent');
            const viewFullDetailsBtn = document.getElementById('viewFullDetails');

            if (titleEl) titleEl.textContent = event.title;

            const eventType = event.extendedProps?.event_type || 'N/A';
            const typeIcon = getEventTypeIcon(eventType);

            if (typeEl) {
                typeEl.textContent = `${typeIcon} ${eventType.replace('_', ' ').toUpperCase()}`;
            }

            if (contentEl) {
                const additionalData = event.extendedProps?.additional_data || {};

                let detailsHTML = '<div class="detail-grid">';

                // Basic Info
                detailsHTML += `
            <div class="detail-item">
                <div class="detail-icon"><i class="fas fa-calendar"></i></div>
                <div class="detail-content">
                    <div class="detail-label">Date</div>
                    <div class="detail-value">${event.start ? event.start.toLocaleDateString() : 'N/A'}</div>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-icon"><i class="fas fa-clock"></i></div>
                <div class="detail-content">
                    <div class="detail-label">Time</div>
                    <div class="detail-value">${event.allDay ? 'All Day' : (event.start ? event.start.toLocaleTimeString() : 'N/A')}</div>
                </div>
            </div>
        `;

                // Event Type Specific Details
                if (eventType === 'maintenance_request') {
                    detailsHTML += `
                <div class="detail-item">
                    <div class="detail-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Priority</div>
                        <div class="detail-value">${additionalData.urgency || 'Normal'}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-icon"><i class="fas fa-info-circle"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">${additionalData.status || 'Unknown'}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-icon"><i class="fas fa-user"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Assigned To</div>
                        <div class="detail-value">${additionalData.assigned_to || 'Unassigned'}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Costs</div>
                        <div class="detail-value">${additionalData.costs || 'Not set'}</div>
                    </div>
                </div>
            `;
                } else if (eventType === 'reminder') {
                    detailsHTML += `
                <div class="detail-item">
                    <div class="detail-icon"><i class="fas fa-bell"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Type</div>
                        <div class="detail-value">Personal Reminder</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-icon"><i class="fas fa-flag"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Priority</div>
                        <div class="detail-value">${additionalData.priority || 'Normal'}</div>
                    </div>
                </div>
            `;
                } else if (eventType === 'clock_event') {
                    detailsHTML += `
                <div class="detail-item">
                    <div class="detail-icon"><i class="fas fa-user"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Employee</div>
                        <div class="detail-value">${additionalData.user_name || 'Unknown'}</div>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Total Costs</div>
                        <div class="detail-value">${additionalData.total_costs || '$0.00'}</div>
                    </div>
                </div>
            `;
                }

                detailsHTML += '</div>';

                // Add description if available
                const description = event.extendedProps?.description || additionalData.issue_description;
                if (description) {
                    detailsHTML += `
                <div class="detail-description">
                    <div class="detail-label">Description</div>
                    <div class="detail-value">${description}</div>
                </div>
            `;
                }

                contentEl.innerHTML = detailsHTML;
            }

            // ➕ FIX: Setup "View Full Details" button click handler
            if (viewFullDetailsBtn) {
                // Remove any existing event listeners
                viewFullDetailsBtn.replaceWith(viewFullDetailsBtn.cloneNode(true));
                const newViewFullDetailsBtn = document.getElementById('viewFullDetails');

                newViewFullDetailsBtn.addEventListener('click', function() {
                    handleViewFullDetails(event);
                });
            }

            modal.classList.add('show');
        }

        // ➕ NEW: Handle "View Full Details" click
        function handleViewFullDetails(event) {
            const eventType = event.extendedProps?.event_type || 'N/A';
            const eventId = event.id;
            const relatedModelId = event.extendedProps?.related_model_id;
            const relatedModelType = event.extendedProps?.related_model_type;

            console.log('View Full Details clicked:', { eventType, eventId, relatedModelId, relatedModelType });

            // Route to appropriate detail page based on event type
            switch (eventType) {
                case 'maintenance_request':
                    if (relatedModelId) {
                        // ✅ TRY THESE DIFFERENT ROUTES:
                        const possibleRoutes = [
                            `/maintenance-requests/${relatedModelId}`,
                            `/admin/maintenance-requests/${relatedModelId}`,
                            `/maintenance/${relatedModelId}`,
                            `/admin/maintenance/${relatedModelId}`
                        ];

                        // Try the first route (you can change this based on your actual route)
                        console.log('Navigating to:', possibleRoutes[0]);
                        window.location.href = possibleRoutes[0];

                        // OR use window.open to open in new tab:
                        // window.open(possibleRoutes[0], '_blank');
                    } else {
                        alert('Maintenance request details not available.');
                    }
                    break;

                case 'clock_event':
                    if (relatedModelId) {
                        window.location.href = `/admin/clockings/${relatedModelId}`;
                    } else {
                        alert('Clock event details not available.');
                    }
                    break;

                case 'expiration':
                    if (relatedModelId && relatedModelType) {
                        if (relatedModelType.includes('ApartmentLease')) {
                            window.location.href = `/admin/apartment-leases/${relatedModelId}`;
                        } else if (relatedModelType.includes('Lease')) {
                            window.location.href = `/leases/${relatedModelId}`;
                        } else {
                            alert('Lease details not available.');
                        }
                    } else {
                        alert('Lease details not available.');
                    }
                    break;

                default:
                    alert(`No detailed view available for ${eventType} events.`);
                    break;
            }
        }

        function hideModal() {
            const modal = document.getElementById('eventModal');
            modal.classList.remove('show');
        }

        function getEventTypeIcon(type) {
            const icons = {
                'expiration': '🏠',
                'maintenance_request': '🔧',
                'clock_event': '⏰',
                'admin_action': '👤',
                'reminder': '🔔'
            };
            return icons[type] || '📅';
        }


    </script>

@endsection
