// pusher-notifications.js
window.loadMoreNotifications = loadMoreNotifications;
window.handleDatabaseNotificationClick = handleDatabaseNotificationClick;
window.markNotificationAsRead = markNotificationAsRead;
window.viewMaintenanceRequest = viewMaintenanceRequest;
window.markAllAsRead = markAllAsRead;
window.closeToast = closeToast;

const BUTTON_STATES = {
    NORMAL: 'normal',
    LOADING: 'loading',
    ERROR: 'error',
    HIDDEN: 'hidden'
};

// Create Load More button with proper structure
function createLoadMoreButton() {
    return `
    <div id="load-more-container" class="px-4 py-3 border-t border-orange-100">
        <button id="load-more-btn"
                onclick="loadMoreNotifications()"
                class="w-full text-center text-sm text-orange-600 hover:text-orange-800 font-medium py-2 hover:bg-orange-50 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2"
                data-state="normal">
            <span id="load-more-icon">📖</span>
            <span id="load-more-text">Load More Notifications</span>
        </button>
    </div>`;
}
// resources/js/notifications.js
document.addEventListener('DOMContentLoaded', () => {
    const desktopButton = document.getElementById('notifications-button');
    const mobileButton = document.getElementById('mobile-notifications-button');

    if (desktopButton) {
        desktopButton.addEventListener('click', toggleNotifications);
    } else {
        console.error('❌ Desktop notifications button not found!');
    }

    if (mobileButton) {
        mobileButton.addEventListener('click', toggleNotifications);
    } else {
        console.error('❌ Mobile notifications button not found!');
    }
});
function updateLoadMoreButtonState(state, customText = null) {
    const button = document.getElementById('load-more-btn');
    const icon = document.getElementById('load-more-icon');
    const text = document.getElementById('load-more-text');

    if (!button || !icon || !text) {
        console.warn('⚠️ Load More button elements not found');
        return;
    }

    // Remove all state classes
    button.classList.remove('opacity-50', 'cursor-not-allowed', 'bg-red-50', 'text-red-600', 'hover:bg-red-100');
    button.disabled = false;
    button.setAttribute('data-state', state);

    switch (state) {
        case BUTTON_STATES.LOADING:
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
            icon.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
            text.textContent = customText || 'Loading more...';
            break;

        case BUTTON_STATES.ERROR:
            button.classList.add('bg-red-50', 'text-red-600', 'hover:bg-red-100');
            icon.innerHTML = '⚠️';
            text.textContent = customText || 'Error loading - Click to retry';
            break;

        case BUTTON_STATES.NORMAL:
            button.classList.add('text-orange-600', 'hover:text-orange-800', 'hover:bg-orange-50');
            icon.innerHTML = '📖';
            text.textContent = customText || 'Load More Notifications';
            break;

        case BUTTON_STATES.HIDDEN:
            removeLoadMoreButton();
            break;
    }
}

function manageLoadMoreButtonVisibility(hasMore, isAppending = false) {
    const existingButton = document.getElementById('load-more-btn');
    if (hasMore) {
        if (!existingButton) {
            const container = document.getElementById('notifications-list');
            if (container) {
                container.insertAdjacentHTML('afterend', createLoadMoreButton());
                console.log('➕ Load More button created');
            }
        } else if (isAppending) {
            updateLoadMoreButtonState(BUTTON_STATES.NORMAL);
        }
    } else {
        removeLoadMoreButton();
    }
}

function removeLoadMoreButton() {
    const container = document.getElementById('load-more-container');
    if (container) {
        container.remove();
        console.log('➖ Load More button removed');
    }
}

// Enhanced notification system with database persistence
let notificationCount = 0;
let notifications = [];
let notificationPage = 1;
let hasMoreNotifications = true;

// Initialize Pusher for real-time notifications
const pusher = new Pusher(
    document.querySelector('meta[name="pusher-key"]').getAttribute('content'),
    {
        cluster: document.querySelector('meta[name="pusher-cluster"]').getAttribute('content'),
        forceTLS: true
    }
);

if (window.pusherConfig.isAdmin) {
    const channel = pusher.subscribe('maintenance-notifications');

    channel.bind('maintenance.request.received', function(data) {
        console.log('🔔 New maintenance notification:', data);
        addNotification(data);
        showToast(data);
        playNotificationSound();
    });

    // Connection status
    pusher.connection.bind('connected', function() {
        console.log('✅ Connected to notification system');
    });
}
// DATABASE INTEGRATION FUNCTIONS
async function loadNotifications(page = 1, append = false) {
    try {
        console.log('🔍 Attempting to load notifications...', {
            page,
            append,
            url: `/notifications?page=${page}&per_page=3`
        });

        const response = await fetch(`/notifications?page=${page}&per_page=3`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        });

        console.log('📡 Response status:', response.status);
        console.log('📡 Response headers:', Object.fromEntries(response.headers.entries()));

        const contentType = response.headers.get('content-type');
        console.log('📄 Content-Type:', contentType);

        if (!contentType || !contentType.includes('application/json')) {
            console.error('❌ Response is not JSON!');
            const text = await response.text();
            console.error('📄 Response text (first 500 chars):', text.substring(0, 500));

            if (text.includes('<!DOCTYPE')) {
                throw new Error('Server returned HTML instead of JSON - likely an authentication or routing issue');
            }
        }

        const data = await response.json();
        console.log('✅ JSON Response received:', data);

        if (!append) {
            notifications = data.notifications || [];
            const container = document.getElementById('notifications-list');
            if (container) container.innerHTML = '';
        } else {
            notifications = [...notifications, ...(data.notifications || [])];
        }

        notificationCount = data.unread_count || 0;
        hasMoreNotifications = data.has_more || false;

        updateNotificationBadges();
        renderNotifications(data.notifications || [], append);

        if ((data.notifications || []).length === 0 && !append) {
            showNoNotifications();
        }

        console.log(`📊 Loaded ${(data.notifications || []).length} notifications (page ${page})`);

    } catch (error) {
        console.error('❌ Error loading notifications:', error);
        console.error('📄 Full error details:', {
            name: error.name,
            message: error.message,
            stack: error.stack
        });
        showNotificationError();
    }
}

// Render notifications in dropdown
function renderNotifications(notificationsList, append = false) {
    const container = document.getElementById('notifications-list');
    if (!container) return;

    if (!append) {
        container.innerHTML = '';
    }

    notificationsList.forEach(notification => {
        const notificationHtml = createDatabaseNotificationHTML(notification);
        container.insertAdjacentHTML('beforeend', notificationHtml);
    });

    if (hasMoreNotifications && !document.getElementById('load-more-btn')) {
        const loadMoreBtn = `
        <div class="px-4 py-3 border-t border-orange-100">
            <button id="load-more-btn" onclick="loadMoreNotifications()"
                    class="w-full text-center text-sm text-orange-600 hover:text-orange-800 font-medium py-2 hover:bg-orange-50 rounded-lg transition-colors">
                📖 Load More Notifications
            </button>
        </div>
    `;
        container.insertAdjacentHTML('afterend', loadMoreBtn);
    }
}

// Create notification HTML from database data
function createDatabaseNotificationHTML(notification) {
    const isUrgent = notification.type === 'urgent_request';
    const urgencyClass = isUrgent ? 'border-l-4 border-red-500 bg-red-50' : 'border-l-4 border-orange-500 bg-orange-50';
    const urgencyIcon = isUrgent ? '🚨' : '🔧';
    const isRead = notification.read_at !== null;
    const timeAgo = formatTimeAgo(notification.created_at);

    const maintenanceRequest = notification.maintenance_request;
    const storeName = maintenanceRequest?.store?.name || 'Unknown Store';
    const equipment = maintenanceRequest?.equipment_with_issue || 'Unknown Equipment';
    const urgencyLevel = maintenanceRequest?.urgency_level || 'Normal';

    return `
    <div class="notification-item relative p-4 hover:bg-gray-50 transition-colors duration-200 cursor-pointer ${urgencyClass} ${isRead ? 'opacity-60' : ''}"
         onclick="handleDatabaseNotificationClick(${notification.id}, ${notification.maintenance_request_id}, this)">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <span class="text-2xl">${urgencyIcon}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 mb-1">
                    ${notification.message}
                </p>
                <div class="text-xs text-gray-600 space-y-1">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                        </svg>
                        <span>${storeName}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996-.608 2.296-.07 2.572 1.065z"></path>
                        </svg>
                        <span>${equipment}</span>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-xs text-gray-400">${timeAgo}</span>
                </div>
                <div class="flex items-center space-x-2 mt-3 pt-2 border-t border-gray-100">
                    <button onclick="event.stopPropagation(); viewMaintenanceRequest(${notification.maintenance_request_id})"
                            class="flex-1 inline-flex items-center justify-center px-3 py-1 bg-orange-600 text-white text-xs font-medium rounded-md hover:bg-orange-700 transition-colors">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        View Details
                    </button>
                    ${!isRead ? `
                    <button onclick="event.stopPropagation(); markNotificationAsRead(${notification.id}, this.closest('.notification-item'))"
                            class="px-3 py-1 text-xs text-gray-600 hover:text-gray-800 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                        Mark Read
                    </button>
                    ` : `
                    <span class="px-3 py-1 text-xs text-green-600 bg-green-50 rounded-md">
                        ✓ Read
                    </span>
                    `}
                </div>
            </div>
        </div>
        ${isRead ? '<div class="absolute top-2 right-2"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>' : ''}
    </div>
`;
}

// NOTIFICATION INTERACTION FUNCTIONS
function toggleNotifications() {
    const dropdown = document.getElementById('notifications-dropdown');
    if (!dropdown) {
        console.error('❌ Notifications dropdown not found!');
        return;
    }

    const isHidden = dropdown.style.display === 'none' || dropdown.style.display === '';

    if (isHidden) {
        dropdown.style.display = 'block';
        dropdown.style.visibility = 'visible';
        dropdown.style.opacity = '1';
        dropdown.style.transform = 'translateY(0)';
        dropdown.style.position = 'absolute';
        dropdown.style.top = '100%';
        dropdown.style.right = '0';
        dropdown.style.zIndex = '9999';

        console.log('✅ Dropdown opened, loading notifications...');

        notificationPage = 1;
        loadNotifications();

        setTimeout(() => document.addEventListener('click', closeOnClickOutside), 100);
    } else {
        dropdown.style.display = 'none';
        document.removeEventListener('click', closeOnClickOutside);
        console.log('✅ Dropdown closed');
    }
}

function handleDatabaseNotificationClick(notificationId, maintenanceRequestId, element) {
    console.log(`🖱️ Notification clicked: ${notificationId}, maintenanceRequestId: ${maintenanceRequestId}, element:`, element);
    if (element && !element.classList.contains('opacity-60')) {
        markNotificationAsRead(notificationId, element);
    }
    viewMaintenanceRequest(maintenanceRequestId);
}async function markNotificationAsRead(notificationId, element) {
    try {
        const response = await fetch(`/notifications/${notificationId}/read`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        });

        if (response.ok) {
            element.classList.add('opacity-60');

            if (!element.querySelector('.absolute.top-2.right-2')) {
                element.innerHTML += '<div class="absolute top-2 right-2"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>';
            }

            notificationCount = Math.max(0, notificationCount - 1);
            updateNotificationBadges();

            console.log(`✅ Notification ${notificationId} marked as read`);
        }
    } catch (error) {
        console.error('❌ Error marking notification as read:', error);
    }
}

async function markAllAsRead() {
    try {
        const response = await fetch('/notifications/mark-all-read', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        });

        if (response.ok) {
            document.querySelectorAll('.notification-item').forEach(item => {
                item.classList.add('opacity-60');
            });

            notificationCount = 0;
            updateNotificationBadges();

            console.log('✅ All notifications marked as read');
        }
    } catch (error) {
        console.error('❌ Error marking all notifications as read:', error);
    }
}

function loadMoreNotifications() {
    notificationPage++;
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        updateLoadMoreButtonState(BUTTON_STATES.LOADING, 'Loading more...');
    }

    loadNotifications(notificationPage, true).then(() => {
        // Success case: revert to normal state
        if (loadMoreBtn) {
            updateLoadMoreButtonState(BUTTON_STATES.NORMAL);
        }
    }).catch((error) => {
        // Error case: show error state
        console.error('❌ Error loading more notifications:', error);
        if (loadMoreBtn) {
            updateLoadMoreButtonState(BUTTON_STATES.ERROR, 'Error loading - Click to retry');
        }
    });
}
// REAL-TIME NOTIFICATION FUNCTIONS
function addNotification(data) {
    console.log('📝 Adding real-time notification:', data);

    notificationCount++;

    const newNotification = {
        id: data.id,
        message: data.message,
        type: data.type,
        read_at: null,
        created_at: data.created_at,
        maintenance_request_id: data.maintenance_request_id,
        maintenance_request: {
            id: data.maintenance_request_id,
            store: { name: data.store_name },
            equipment_with_issue: data.equipment,
            urgency_level: data.urgency_level
        }
    };

    notifications.unshift(newNotification);

    updateNotificationBadges();

    const notificationsList = document.getElementById('notifications-list');
    if (notificationsList && !document.getElementById('no-notifications')) {
        const notificationHtml = createDatabaseNotificationHTML(newNotification);
        notificationsList.insertAdjacentHTML('afterbegin', notificationHtml);

        const items = notificationsList.querySelectorAll('.notification-item');
        if (items.length > 10) {
            items[items.length - 1].remove();
        }
    }
}

// TOAST NOTIFICATION FUNCTIONS
function showToast(notification) {
    console.log('📋 Toast notification received:', notification);
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        console.error('❌ Toast container not found!');
        return;
    }

    const toastId = `toast-${Date.now()}`;
    const isUrgent = notification.is_urgent;
    const urgencyIcon = isUrgent ? '🚨' : '🔧';
    const maintenanceRequestId = notification.maintenance_request_id || notification.id || null; // Fallback to id if maintenance_request_id is missing

    if (!maintenanceRequestId) {
        console.warn('⚠️ No maintenance_request_id found in toast data, using fallback or null');
    }

    const toastHTML = `
        <div id="${toastId}" class="relative max-w-md w-full bg-gradient-to-r ${isUrgent ? 'from-red-500 to-red-600' : 'from-orange-500 to-orange-600'} shadow-2xl rounded-xl pointer-events-auto transform transition-all duration-500 translate-x-full hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent rounded-xl"></div>
            <div class="relative p-4 text-white">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 relative">
                        <div class="w-10 h-10 ${isUrgent ? 'bg-red-400' : 'bg-orange-400'} rounded-full flex items-center justify-center animate-pulse">
                            <span class="text-xl">${urgencyIcon}</span>
                        </div>
                        <div class="absolute inset-0 ${isUrgent ? 'bg-red-400' : 'bg-orange-400'} rounded-full animate-ping opacity-20"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-bold text-sm ${isUrgent ? 'text-red-100' : 'text-orange-100'}">
                                ${isUrgent ? 'URGENT' : 'NEW REQUEST'}
                            </h4>
                            <button onclick="event.stopPropagation(); closeToast('${toastId}')"
                                    class="text-white/70 hover:text-white transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="text-white font-medium text-sm mb-2 leading-tight">
                            ${notification.message}
                        </p>
                        <div class="space-y-1">
                            <div class="flex items-center text-xs text-white/90">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                                </svg>
                                <span class="font-medium">${notification.store_name}</span>
                            </div>
                            <div class="flex items-center text-xs text-white/90">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996-.608 2.296-.07 2.572 1.065z"></path>
                                </svg>
                                <span>${notification.equipment}</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-t border-white/20">
                            <button onclick="handleDatabaseNotificationClick(${notification.id}, ${maintenanceRequestId}, null); closeToast('${toastId}')"
                                    class="w-full bg-white/20 hover:bg-white/30 text-white text-xs font-medium py-2 px-3 rounded-lg transition-all duration-200 backdrop-blur-sm">
                                👁️ View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 bg-white/30 rounded-b-xl">
                <div class="h-full bg-white rounded-b-xl animate-toast-progress"></div>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);

    setTimeout(() => {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.remove('translate-x-full');
            toast.onclick = () => {
                handleDatabaseNotificationClick(notification.id, maintenanceRequestId, null);
                closeToast(toastId);
            };
        }
    }, 100);

    setTimeout(() => {
        closeToast(toastId);
    }, 8000);
}
function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
}

// UTILITY FUNCTIONS
function updateNotificationBadges() {
    const badge = document.getElementById('notification-badge');
    const mobileBadge = document.getElementById('mobile-notification-badge');
    const count = document.getElementById('notification-count');
    const pulse = document.getElementById('notification-pulse');

    if (notificationCount > 0) {
        if (badge) {
            badge.textContent = notificationCount;
            badge.classList.remove('hidden');
        }

        if (mobileBadge) {
            mobileBadge.textContent = notificationCount;
            mobileBadge.classList.remove('hidden');
        }

        if (count) count.textContent = `${notificationCount} new`;
        if (pulse) pulse.classList.remove('hidden');
    } else {
        if (badge) badge.classList.add('hidden');
        if (mobileBadge) mobileBadge.classList.add('hidden');
        if (count) count.textContent = '0 new';
        if (pulse) pulse.classList.add('hidden');
    }
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    return `${Math.floor(diffInSeconds / 86400)}d ago`;
}

function showNoNotifications() {
    const container = document.getElementById('notifications-list');
    if (container) {
        container.innerHTML = `
        <div class="px-4 py-8 text-center text-gray-500" id="no-notifications">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5V3h5v14z"></path>
            </svg>
            <p class="text-sm">No notifications yet</p>
            <p class="text-xs text-gray-400 mt-1">You're all caught up!</p>
        </div>
    `;
    }
}

function showNotificationError() {
    const container = document.getElementById('notifications-list');
    if (container) {
        container.innerHTML = `
        <div class="px-4 py-8 text-center text-red-500">
            <svg class="w-12 h-12 mx-auto mb-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <p class="text-sm">Error loading notifications</p>
            <button onclick="loadNotifications()" class="text-xs text-red-600 hover:text-red-800 mt-2">Try Again</button>
        </div>
    `;
    }
}

function playNotificationSound() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
        oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
        oscillator.type = 'sine';

        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
    } catch (e) {
        console.log('Could not play notification sound:', e);
    }
}

function viewMaintenanceRequest(requestId) {
    window.location.href = '/maintenance-requests/' + requestId;
}

function closeOnClickOutside(event) {
    const dropdown = document.getElementById('notifications-dropdown');
    const button = event.target.closest('button[onclick*="toggleNotifications"]');

    if (dropdown &&
        !dropdown.contains(event.target) &&
        !button) {
        dropdown.style.display = 'none';
        document.removeEventListener('click', closeOnClickOutside);
    }
}

// INITIALIZATION
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Enhanced notification system initialized');

    loadNotifications();

    updateNotificationBadges();

    setTimeout(() => {
        console.log('🧪 Running notification system health check...');
        const dropdown = document.getElementById('notifications-dropdown');
        const toastContainer = document.getElementById('toast-container');

        console.log('✅ Dropdown exists:', !!dropdown);
        console.log('✅ Toast container exists:', !!toastContainer);
        console.log('📊 Current notification count:', notificationCount);
    }, 3000);
});
