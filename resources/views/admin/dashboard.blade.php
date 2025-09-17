{{--<!DOCTYPE html>--}}
{{--<html lang="en">--}}
{{--<head>--}}
{{--    <meta charset="utf-8">--}}
{{--    <meta name="viewport" content="width=device-width, initial-scale=1">--}}
{{--    <title>Admin Dashboard - Maintenance Notifications</title>--}}
{{--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">--}}
{{--    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">--}}
{{--</head>--}}
{{--<body>--}}
{{--<nav class="navbar navbar-dark bg-dark">--}}
{{--    <div class="container-fluid">--}}
{{--        <a class="navbar-brand" href="#">Admin Dashboard</a>--}}

{{--        <!-- Notification Bell -->--}}
{{--        <div class="dropdown">--}}
{{--            <a href="#" class="text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">--}}
{{--                <i class="bi bi-bell fs-4 position-relative">--}}
{{--                    <span id="notification-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">0</span>--}}
{{--                </i>--}}
{{--            </a>--}}

{{--            <div class="dropdown-menu dropdown-menu-end" style="width: 400px; max-height: 500px; overflow-y: auto;">--}}
{{--                <div class="dropdown-header bg-light">--}}
{{--                    <h6 class="mb-0">Maintenance Notifications (<span id="total-notifications">0</span>)</h6>--}}
{{--                </div>--}}
{{--                <div id="notification-list">--}}
{{--                    <div class="dropdown-item text-center text-muted" id="no-notifications">--}}
{{--                        No new notifications--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="dropdown-divider"></div>--}}
{{--                <button class="dropdown-item text-center" onclick="clearAllNotifications()">--}}
{{--                    <small>Clear All</small>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</nav>--}}

{{--<div class="container mt-4">--}}
{{--    <div class="row">--}}
{{--        <div class="col-12">--}}
{{--            <h1>Maintenance Requests Dashboard</h1>--}}
{{--            <div class="alert alert-info">--}}
{{--                <i class="bi bi-info-circle"></i>--}}
{{--                Real-time notifications are enabled. You'll be notified when new maintenance requests arrive.--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <!-- Connection Status -->--}}
{{--    <div class="row mb-3">--}}
{{--        <div class="col-12">--}}
{{--            <div id="connection-status" class="badge bg-secondary">Connecting...</div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <!-- Your existing dashboard content goes here -->--}}
{{--    <div class="row">--}}
{{--        <div class="col-12">--}}
{{--            <div class="card">--}}
{{--                <div class="card-header">--}}
{{--                    <h5>Recent Maintenance Requests</h5>--}}
{{--                </div>--}}
{{--                <div class="card-body">--}}
{{--                    <!-- Your maintenance requests table/content -->--}}
{{--                    <p>Your existing maintenance requests content here...</p>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}

{{--<!-- Toast Container -->--}}
{{--<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>--}}

{{--<!-- Scripts -->--}}
{{--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>--}}
{{--<script src="https://js.pusher.com/7.0/pusher.min.js"></script>--}}

{{--<script>--}}
{{--    // Enable Pusher logging for debugging--}}
{{--        // Enable Pusher logging for debugging--}}
{{--        Pusher.logToConsole = true;--}}

{{--        console.log('🔧 Initializing Pusher with:');--}}
{{--        console.log('Key:', '{{ config('broadcasting.connections.pusher.key') }}');--}}
{{--        console.log('Cluster:', '{{ config('broadcasting.connections.pusher.options.cluster') }}');--}}

{{--        // SINGLE Pusher initialization--}}
{{--        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {--}}
{{--        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',--}}
{{--        forceTLS: true,--}}
{{--        enabledTransports: ['ws', 'wss', 'xhr_polling', 'xhr_streaming']--}}
{{--    });--}}

{{--        // Subscribe to channel--}}
{{--        const channel = pusher.subscribe('maintenance-notifications');--}}
{{--        let notificationCount = 0;--}}

{{--        // Connection status handlers--}}
{{--        pusher.connection.bind('connecting', function() {--}}
{{--        console.log('🔄 Connecting to Pusher...');--}}
{{--        updateConnectionStatus('Connecting...', 'warning');--}}
{{--    });--}}

{{--        pusher.connection.bind('connected', function() {--}}
{{--        console.log('✅ Connected to Pusher successfully!');--}}
{{--        console.log('Socket ID:', pusher.connection.socket_id);--}}
{{--        updateConnectionStatus('Connected', 'success');--}}
{{--    });--}}

{{--        pusher.connection.bind('disconnected', function() {--}}
{{--        console.log('❌ Disconnected from Pusher');--}}
{{--        updateConnectionStatus('Disconnected', 'danger');--}}
{{--    });--}}

{{--        pusher.connection.bind('error', function(err) {--}}
{{--        console.log('❌ Pusher connection error:', err);--}}
{{--        updateConnectionStatus('Error', 'danger');--}}
{{--    });--}}

{{--        // Channel subscription debugging--}}
{{--        channel.bind('pusher:subscription_succeeded', function() {--}}
{{--        console.log('✅ Successfully subscribed to maintenance-notifications channel');--}}
{{--    });--}}

{{--        channel.bind('pusher:subscription_error', function(err) {--}}
{{--        console.log('❌ Subscription error:', err);--}}
{{--    });--}}

{{--        // 🚨 ENHANCED DEBUGGING - Listen for ALL events--}}
{{--        channel.bind_global(function(eventName, data) {--}}
{{--        console.log('🚨 ANY EVENT RECEIVED:', eventName, data);--}}
{{--        console.log('Event Type:', typeof eventName);--}}
{{--        console.log('Data Type:', typeof data);--}}

{{--        // Alert for ANY event--}}
{{--        alert('📡 EVENT: ' + eventName + ' | Data: ' + JSON.stringify(data));--}}
{{--    });--}}

{{--        // 🚨 SPECIFIC EVENT LISTENER with more debugging--}}
{{--        channel.bind('maintenance.request.received', function(data) {--}}
{{--        console.log('🎯 SPECIFIC MAINTENANCE EVENT RECEIVED!');--}}
{{--        console.log('Raw Data:', data);--}}
{{--        console.log('Data Type:', typeof data);--}}
{{--        console.log('Data Keys:', Object.keys(data));--}}

{{--        alert('🔔 MAINTENANCE NOTIFICATION: ' + (data.message || 'No message'));--}}

{{--        try {--}}
{{--        addNotificationToDropdown(data);--}}
{{--        showToastNotification(data);--}}
{{--        playNotificationSound();--}}
{{--    } catch (error) {--}}
{{--        console.error('❌ Error processing notification:', error);--}}
{{--        alert('Error processing notification: ' + error.message);--}}
{{--    }--}}
{{--    });--}}

{{--        // 🚨 TEST: Manual event trigger--}}
{{--        setTimeout(function() {--}}
{{--        console.log('🧪 Triggering manual test event...');--}}
{{--        // Manually trigger event to test listener--}}
{{--        channel.emit('maintenance.request.received', {--}}
{{--        message: 'Manual test notification',--}}
{{--        store_name: 'Test Store',--}}
{{--        equipment: 'Test Equipment'--}}
{{--    });--}}
{{--    }, 5000); // 5 seconds after page load--}}

{{--        // Functions with error handling--}}
{{--        function updateConnectionStatus(status, type) {--}}
{{--        try {--}}
{{--        const statusElement = document.getElementById('connection-status');--}}
{{--        if (statusElement) {--}}
{{--        statusElement.className = `badge bg-${type}`;--}}
{{--        statusElement.textContent = status;--}}
{{--    }--}}
{{--    } catch (error) {--}}
{{--        console.error('Error updating connection status:', error);--}}
{{--    }--}}
{{--    }--}}

{{--        function addNotificationToDropdown(data) {--}}
{{--        console.log('📝 Adding notification to dropdown:', data);--}}
{{--        try {--}}
{{--        notificationCount++;--}}

{{--        // Update notification count badge--}}
{{--        const countBadge = document.getElementById('notification-count');--}}
{{--        const totalNotifications = document.getElementById('total-notifications');--}}

{{--        if (countBadge) {--}}
{{--        countBadge.textContent = notificationCount;--}}
{{--        countBadge.style.display = 'inline';--}}
{{--        console.log('✅ Updated count badge to:', notificationCount);--}}
{{--    } else {--}}
{{--        console.error('❌ Count badge element not found');--}}
{{--    }--}}

{{--        if (totalNotifications) {--}}
{{--        totalNotifications.textContent = notificationCount;--}}
{{--        console.log('✅ Updated total notifications to:', notificationCount);--}}
{{--    } else {--}}
{{--        console.error('❌ Total notifications element not found');--}}
{{--    }--}}

{{--        // Hide "no notifications" message--}}
{{--        const noNotifications = document.getElementById('no-notifications');--}}
{{--        if (noNotifications) {--}}
{{--        noNotifications.style.display = 'none';--}}
{{--        console.log('✅ Hidden "no notifications" message');--}}
{{--    }--}}

{{--        // Create notification item (simplified for debugging)--}}
{{--        const notificationHtml = `--}}
{{--                <div class="dropdown-item notification-item" data-id="${data.id || 'unknown'}">--}}
{{--                    <div class="d-flex align-items-start">--}}
{{--                        <div class="flex-shrink-0 me-2">--}}
{{--                            <span class="fs-5">🔧</span>--}}
{{--                        </div>--}}
{{--                        <div class="flex-grow-1">--}}
{{--                            <h6 class="mb-1">${data.message || 'No message'}</h6>--}}
{{--                            <p class="mb-1 small text-muted">Store: ${data.store_name || 'Unknown'}</p>--}}
{{--                            <small class="text-muted">Just now</small>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            `;--}}

{{--        // Add to notification list--}}
{{--        const notificationList = document.getElementById('notification-list');--}}
{{--        if (notificationList) {--}}
{{--        notificationList.insertAdjacentHTML('afterbegin', notificationHtml);--}}
{{--        console.log('✅ Added notification HTML to dropdown');--}}
{{--    } else {--}}
{{--        console.error('❌ Notification list element not found');--}}
{{--    }--}}

{{--    } catch (error) {--}}
{{--        console.error('❌ Error in addNotificationToDropdown:', error);--}}
{{--    }--}}
{{--    }--}}

{{--        function showToastNotification(data) {--}}
{{--        console.log('🍞 Showing toast notification:', data);--}}
{{--        // Simple alert instead of complex toast for debugging--}}
{{--        alert('🔔 TOAST: ' + (data.message || 'Test notification'));--}}
{{--    }--}}

{{--        function playNotificationSound() {--}}
{{--        console.log('🔊 Playing notification sound');--}}
{{--    }--}}

{{--        function clearAllNotifications() {--}}
{{--        console.log('🧹 Clearing all notifications');--}}
{{--        // Function implementation--}}
{{--    }--}}

{{--        // Page load confirmation--}}
{{--        document.addEventListener('DOMContentLoaded', function() {--}}
{{--        console.log('🚀 Maintenance Notification System Initialized');--}}
{{--        console.log('📡 Listening for real-time notifications...');--}}
{{--        console.log('⏰ Manual test will trigger in 5 seconds...');--}}
{{--    });--}}
{{--</script>--}}

{{--</body>--}}
{{--</html>--}}
