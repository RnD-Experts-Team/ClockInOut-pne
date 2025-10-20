@extends('layouts.app')

@section('title', 'Daily Overview - ' . $selectedDate->format('F j, Y'))

@section('content')
    <div class="daily-overview-page">
        <!-- Header -->
        <div class="page-header">
            <div class="header-left">
                <div class="header-title">
                    <h1><i class="fas fa-calendar-day"></i> Daily Overview</h1>
                    <p class="header-subtitle">{{ $selectedDate->format('l, F j, Y') }}</p>
                </div>
            </div>
            <div class="header-right">
                <div class="header-actions">
                    <a href="{{ route('calendar.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Calendar
                    </a>
                    <a href="{{ route('calendar.daily', date('Y-m-d')) }}" class="btn btn-info">
                        <i class="fas fa-calendar-check"></i> Today
                    </a>
                    <button onclick="exportDailyData()" class="btn btn-success">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Date Navigation -->
        <div class="date-navigation">
            <div class="nav-container">
                <a href="{{ route('calendar.daily', $selectedDate->copy()->subDay()->format('Y-m-d')) }}"
                   class="nav-btn prev-btn">
                    <i class="fas fa-chevron-left"></i>
                    <span>{{ $selectedDate->copy()->subDay()->format('M j') }}</span>
                </a>

                <div class="current-date">
                    <div class="date-main">{{ $selectedDate->format('F j, Y') }}</div>
                    <div class="date-weekday">{{ $selectedDate->format('l') }}</div>
                    @if($selectedDate->isToday())
                        <div class="today-badge">Today</div>
                    @elseif($selectedDate->isTomorrow())
                        <div class="tomorrow-badge">Tomorrow</div>
                    @elseif($selectedDate->isYesterday())
                        <div class="yesterday-badge">Yesterday</div>
                    @endif
                </div>

                <a href="{{ route('calendar.daily', $selectedDate->copy()->addDay()->format('Y-m-d')) }}"
                   class="nav-btn next-btn">
                    <span>{{ $selectedDate->copy()->addDay()->format('M j') }}</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="totalEvents">0</div>
                        <div class="stat-label">Total Events</div>
                    </div>
                </div>

                <div class="stat-card leases">
                    <div class="stat-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="leaseEvents">0</div>
                        <div class="stat-label">Lease Events</div>
                    </div>
                </div>

                <div class="stat-card maintenance">
                    <div class="stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="maintenanceEvents">0</div>
                        <div class="stat-label">Maintenance</div>
                    </div>
                </div>

                <div class="stat-card admin">
                    <div class="stat-icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="adminEvents">0</div>
                        <div class="stat-label">Admin Actions</div>
                    </div>
                </div>

                <div class="stat-card reminders">
                    <div class="stat-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="reminderEvents">0</div>
                        <div class="stat-label">Reminders</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-container">
            <!-- Events Timeline -->
            <div class="main-content">
                <div class="events-timeline">
                    <div class="timeline-header">
                        <h2><i class="fas fa-clock"></i> Timeline</h2>
                        <div class="timeline-controls">
                            <button class="filter-btn active" data-filter="all">All Events</button>
                            <button class="filter-btn" data-filter="lease_expirations">Leases</button>
                            <button class="filter-btn" data-filter="maintenance_requests">Maintenance</button>
                            <button class="filter-btn" data-filter="admin_actions">Admin</button>
                            <button class="filter-btn" data-filter="reminders">Reminders</button>
                        </div>
                    </div>

                    <div class="timeline-content" id="timelineContent">
                        <!-- Loading state -->
                        <div class="loading-state">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading events...</p>
                        </div>
                    </div>

                    <!-- Empty state template -->
                    <div class="empty-state" id="emptyState" style="display: none;">
                        <div class="empty-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <h3>No Events Found</h3>
                        <p>There are no events scheduled for this date.</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Weather Widget (Optional) -->
                <div class="sidebar-card weather-card">
                    <h3><i class="fas fa-cloud-sun"></i> Today's Weather</h3>
                    <div class="weather-info">
                        <div class="weather-main">
                            <div class="temperature">22°C</div>
                            <div class="condition">Sunny</div>
                        </div>
                        <div class="weather-details">
                            <div class="detail">
                                <i class="fas fa-eye"></i>
                                <span>Visibility: 10km</span>
                            </div>
                            <div class="detail">
                                <i class="fas fa-tint"></i>
                                <span>Humidity: 65%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    <div class="quick-actions">
                        <a href="{{ route('reminders.create') }}" class="action-link">
                            <i class="fas fa-plus"></i> Add Reminder
                        </a>
                        <a href="{{ route('calendar.index') }}" class="action-link">
                            <i class="fas fa-calendar"></i> Full Calendar
                        </a>
                        <button onclick="refreshEvents()" class="action-link">
                            <i class="fas fa-sync-alt"></i> Refresh Events
                        </button>
                        <button onclick="exportDailyData()" class="action-link">
                            <i class="fas fa-file-export"></i> Export Data
                        </button>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-clock"></i> Upcoming</h3>
                    <div class="upcoming-events" id="upcomingEvents">
                        <div class="loading-text">Loading upcoming events...</div>
                    </div>
                </div>

                <!-- Event Types Legend -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-palette"></i> Legend</h3>
                    <div class="legend-items">
                        <div class="legend-item">
                            <div class="legend-color lease"></div>
                            <span>Lease Events</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color maintenance"></div>
                            <span>Maintenance</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color admin"></div>
                            <span>Admin Actions</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color reminder"></div>
                            <span>Reminders</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color clock"></div>
                            <span>Clock Events</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Daily Overview Styles */
        .daily-overview-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .page-header {
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
            font-size: 1.1rem;
            font-weight: 500;
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
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* Date Navigation */
        .date-navigation {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 1px solid #e9ecef;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 800px;
            margin: 0 auto;
        }

        .nav-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: #f8f9fa;
            color: #495057;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            text-decoration: none;
        }

        .current-date {
            text-align: center;
            position: relative;
        }

        .date-main {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .date-weekday {
            font-size: 1rem;
            color: #6c757d;
            font-weight: 500;
        }

        .today-badge, .tomorrow-badge, .yesterday-badge {
            position: absolute;
            top: -10px;
            right: -20px;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
            color: white;
        }

        .today-badge { background: #28a745; }
        .tomorrow-badge { background: #17a2b8; }
        .yesterday-badge { background: #6c757d; }

        /* Quick Stats */
        .quick-stats {
            padding: 30px;
            background: rgba(255,255,255,0.5);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
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
            background: var(--card-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .stat-card.total { --card-color: #667eea; }
        .stat-card.leases { --card-color: #4ecdc4; }
        .stat-card.maintenance { --card-color: #ffc107; }
        .stat-card.admin { --card-color: #28a745; }
        .stat-card.reminders { --card-color: #dc3545; }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--card-color), var(--card-color));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
            margin-top: 5px;
        }

        /* Content Layout */
        .content-container {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 30px;
            padding: 30px;
        }

        .main-content {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }

        /* Timeline */
        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fa;
        }

        .timeline-header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .timeline-controls {
            display: flex;
            gap: 8px;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #e9ecef;
            background: white;
            color: #6c757d;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .filter-btn.active, .filter-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .timeline-content {
            min-height: 400px;
        }

        .loading-state, .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .loading-state i {
            font-size: 2rem;
            margin-bottom: 15px;
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

        /* Timeline Items */
        .timeline-item {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid var(--event-color, #667eea);
            transition: all 0.3s ease;
        }

        .timeline-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .timeline-time {
            min-width: 80px;
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .time-main {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .time-period {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 2px;
        }

        .timeline-content-item {
            flex: 1;
        }

        .event-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .event-description {
            color: #6c757d;
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .event-meta {
            display: flex;
            gap: 15px;
            font-size: 0.8rem;
            color: #95a5a6;
        }

        .event-type {
            display: flex;
            align-items: center;
            gap: 5px;
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

        /* Weather Card */
        .weather-info {
            text-align: center;
        }

        .weather-main {
            margin-bottom: 15px;
        }

        .temperature {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            line-height: 1;
        }

        .condition {
            color: #6c757d;
            font-weight: 500;
            margin-top: 5px;
        }

        .weather-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .detail {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #95a5a6;
            font-size: 0.9rem;
        }

        /* Quick Actions */
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

        /* Legend */
        .legend-items {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 3px;
        }

        .legend-color.lease { background: #4ecdc4; }
        .legend-color.maintenance { background: #ffc107; }
        .legend-color.admin { background: #28a745; }
        .legend-color.reminder { background: #dc3545; }
        .legend-color.clock { background: #17a2b8; }

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

            .nav-container {
                flex-direction: column;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .timeline-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }

            .timeline-controls {
                flex-wrap: wrap;
                justify-content: center;
            }

            .timeline-item {
                flex-direction: column;
                gap: 15px;
            }

            .timeline-time {
                align-self: flex-start;
                min-width: auto;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectedDate = '{{ $selectedDate->format("Y-m-d") }}';
            let currentFilter = 'all';
            let eventsData = {};

            // Initialize the page
            loadDailyEvents();
            loadUpcomingEvents();

            // Filter button handlers
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Update active filter
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    currentFilter = this.getAttribute('data-filter');
                    displayFilteredEvents();
                });
            });

            /**
             * Load daily events via AJAX
             */
            function loadDailyEvents() {
                fetch(`/calendar/daily/${selectedDate}/events`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            eventsData = data.data;
                            updateStatistics(data.statistics);
                            displayFilteredEvents();
                        } else {
                            showError('Failed to load events');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading events:', error);
                        showError('Error loading events');
                    });
            }

            /**
             * Update statistics counters
             */
            function updateStatistics(stats) {
                document.getElementById('totalEvents').textContent = stats.total_events || 0;
                document.getElementById('leaseEvents').textContent = stats.by_type.lease_expirations || 0;
                document.getElementById('maintenanceEvents').textContent = stats.by_type.maintenance_requests || 0;
                document.getElementById('adminEvents').textContent = stats.by_type.admin_actions || 0;
                document.getElementById('reminderEvents').textContent = stats.by_type.reminders || 0;

                // Animate counters
                animateCounters();
            }

            /**
             * Display filtered events in timeline
             */
            function displayFilteredEvents() {
                const timelineContent = document.getElementById('timelineContent');
                const emptyState = document.getElementById('emptyState');

                let filteredEvents = [];

                if (currentFilter === 'all') {
                    // Combine all event types
                    Object.values(eventsData).forEach(events => {
                        if (Array.isArray(events)) {
                            filteredEvents.push(...events);
                        }
                    });
                } else {
                    // Filter specific type
                    filteredEvents = eventsData[currentFilter] || [];
                }

                if (filteredEvents.length === 0) {
                    timelineContent.innerHTML = '';
                    emptyState.style.display = 'block';
                    return;
                }

                emptyState.style.display = 'none';

                // Sort events by time
                filteredEvents.sort((a, b) => {
                    const timeA = a.start_time || '00:00';
                    const timeB = b.start_time || '00:00';
                    return timeA.localeCompare(timeB);
                });

                // Generate timeline HTML
                const timelineHTML = filteredEvents.map(event => createTimelineItem(event)).join('');
                timelineContent.innerHTML = timelineHTML;
            }

            /**
             * Create timeline item HTML
             */
            function createTimelineItem(event) {
                const eventColors = {
                    'lease_expiration': '#4ecdc4',
                    'maintenance_request': '#ffc107',
                    'admin_action': '#28a745',
                    'reminder': '#dc3545',
                    'clock_event': '#17a2b8'
                };

                const eventIcons = {
                    'lease_expiration': 'fas fa-home',
                    'maintenance_request': 'fas fa-tools',
                    'admin_action': 'fas fa-user-cog',
                    'reminder': 'fas fa-bell',
                    'clock_event': 'fas fa-clock'
                };

                const color = eventColors[event.event_type] || '#667eea';
                const icon = eventIcons[event.event_type] || 'fas fa-calendar';
                const time = event.start_time ? formatTime(event.start_time) : 'All Day';

                return `
            <div class="timeline-item" style="--event-color: ${color}">
                <div class="timeline-time">
                    <div class="time-main">${time}</div>
                </div>
                <div class="timeline-content-item">
                    <div class="event-title">${event.title}</div>
                    <div class="event-description">${event.description || 'No description'}</div>
                    <div class="event-meta">
                        <div class="event-type">
                            <i class="${icon}"></i>
                            <span>${formatEventType(event.event_type)}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
            }

            /**
             * Format time for display
             */
            function formatTime(timeString) {
                try {
                    const time = new Date('2000-01-01 ' + timeString);
                    return time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                } catch (e) {
                    return timeString;
                }
            }

            /**
             * Format event type for display
             */
            function formatEventType(type) {
                const types = {
                    'lease_expiration': 'Lease Event',
                    'maintenance_request': 'Maintenance',
                    'admin_action': 'Admin Action',
                    'reminder': 'Reminder',
                    'clock_event': 'Clock Event'
                };
                return types[type] || type;
            }

            /**
             * Animate counter numbers
             */
            function animateCounters() {
                document.querySelectorAll('.stat-number').forEach(counter => {
                    const target = parseInt(counter.textContent);
                    const increment = target / 30;
                    let current = 0;

                    const updateCounter = () => {
                        if (current < target) {
                            current += increment;
                            counter.textContent = Math.floor(current);
                            setTimeout(updateCounter, 50);
                        } else {
                            counter.textContent = target;
                        }
                    };

                    counter.textContent = '0';
                    setTimeout(updateCounter, 100);
                });
            }

            /**
             * Load upcoming events for sidebar
             */
            function loadUpcomingEvents() {
                const upcomingContainer = document.getElementById('upcomingEvents');

                // This would typically fetch from your API
                upcomingContainer.innerHTML = `
            <div class="upcoming-item">
                <div class="upcoming-time">Tomorrow 9:00 AM</div>
                <div class="upcoming-title">Maintenance Check</div>
            </div>
            <div class="upcoming-item">
                <div class="upcoming-time">Oct 20 2:00 PM</div>
                <div class="upcoming-title">Lease Renewal Meeting</div>
            </div>
        `;
            }

            /**
             * Show error message
             */
            function showError(message) {
                const timelineContent = document.getElementById('timelineContent');
                timelineContent.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle text-danger"></i>
                <p>${message}</p>
                <button onclick="loadDailyEvents()" class="btn btn-primary btn-sm">Try Again</button>
            </div>
        `;
            }
        });

        /**
         * Refresh events
         */
        function refreshEvents() {
            const selectedDate = '{{ $selectedDate->format("Y-m-d") }}';
            window.location.reload();
        }

        /**
         * Export daily data
         */
        function exportDailyData() {
            const selectedDate = '{{ $selectedDate->format("Y-m-d") }}';
            window.location.href = `/calendar/daily/${selectedDate}/export`;
        }
    </script>
@endsection
