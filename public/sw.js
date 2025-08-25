const CACHE_NAME = 'pne-clockin-v3';
const STATIC_CACHE_NAME = 'pne-static-v3';
const DYNAMIC_CACHE_NAME = 'pne-dynamic-v3';

// Assets to cache immediately
const STATIC_ASSETS = [
  '/',
  '/clocking',
  '/attendance',
  '/dashboard',
  '/manifest.json',
  '/build/assets/forms.css',
  'https://cdn.tailwindcss.com',
  'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'
];

// API endpoints to cache with network-first strategy
const API_ENDPOINTS = [
  '/api/clockings',
  '/api/attendance',
  '/api/user'
];

// Install event - cache static assets
self.addEventListener('install', event => {
  console.log('Service Worker: Installing...');
  // Force immediate activation
  self.skipWaiting();
  event.waitUntil(
    caches.open(STATIC_CACHE_NAME)
      .then(cache => {
        console.log('Service Worker: Caching static assets');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => {
        console.log('Service Worker: Static assets cached');
        return self.skipWaiting();
      })
      .catch(error => {
        console.error('Service Worker: Error caching static assets:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('Service Worker: Activating...');
  // Take control of all clients immediately
  event.waitUntil(
    Promise.all([
      self.clients.claim(),
      caches.keys()
        .then(cacheNames => {
          return Promise.all(
            cacheNames.map(cacheName => {
              if (cacheName !== STATIC_CACHE_NAME && cacheName !== DYNAMIC_CACHE_NAME) {
                console.log('Service Worker: Deleting old cache:', cacheName);
                return caches.delete(cacheName);
              }
            })
          );
        })
        .then(() => {
          console.log('Service Worker: Activated');
        })
    ])
  );
});

// Fetch event - implement caching strategies
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }

  // Skip unsupported URL schemes
  if (!isSupportedScheme(url.protocol)) {
    console.log('Service Worker: Skipping unsupported URL scheme:', url.protocol, 'for URL:', request.url);
    return;
  }

  // Handle API requests with network-first strategy
  if (isApiRequest(url.pathname)) {
    event.respondWith(networkFirstStrategy(request));
    return;
  }

  // Handle static assets with cache-first strategy
  if (isStaticAsset(request.url)) {
    event.respondWith(cacheFirstStrategy(request));
    return;
  }

  // Handle navigation requests with network-first, fallback to cache
  if (request.mode === 'navigate') {
    event.respondWith(navigationStrategy(request));
    return;
  }

  // Default: network-first strategy
  event.respondWith(networkFirstStrategy(request));
});

// Check if URL scheme is supported for caching
function isSupportedScheme(protocol) {
  return protocol === 'http:' || protocol === 'https:';
}

// Check if request is for API endpoint
function isApiRequest(pathname) {
  return API_ENDPOINTS.some(endpoint => pathname.startsWith(endpoint)) ||
         pathname.startsWith('/api/') ||
         pathname.includes('csrf-token');
}

// Check if request is for static asset
function isStaticAsset(url) {
  return url.includes('.css') ||
         url.includes('.js') ||
         url.includes('.png') ||
         url.includes('.jpg') ||
         url.includes('.jpeg') ||
         url.includes('.svg') ||
         url.includes('.ico') ||
         url.includes('fonts.googleapis.com') ||
         url.includes('cdn.tailwindcss.com');
}

// Cache-first strategy for static assets
async function cacheFirstStrategy(request) {
  try {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }

    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const url = new URL(request.url);
      // Only cache requests with supported schemes
      if (isSupportedScheme(url.protocol)) {
        try {
          const cache = await caches.open(STATIC_CACHE_NAME);
          await cache.put(request, networkResponse.clone());
        } catch (cacheError) {
          console.error('Service Worker: Failed to cache in cacheFirstStrategy:', cacheError, 'URL:', request.url);
        }
      } else {
        console.log('Service Worker: Skipping cache for unsupported scheme in cacheFirstStrategy:', url.protocol);
      }
    }
    return networkResponse;
  } catch (error) {
    console.error('Cache-first strategy failed:', error);
    return new Response('Offline - Asset not available', { status: 503 });
  }
}

// Network-first strategy for dynamic content
async function networkFirstStrategy(request) {
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const url = new URL(request.url);
      // Only cache requests with supported schemes
      if (isSupportedScheme(url.protocol)) {
        try {
          const cache = await caches.open(DYNAMIC_CACHE_NAME);
          await cache.put(request, networkResponse.clone());
        } catch (cacheError) {
          console.error('Service Worker: Failed to cache in networkFirstStrategy:', cacheError, 'URL:', request.url);
        }
      } else {
        console.log('Service Worker: Skipping cache for unsupported scheme in networkFirstStrategy:', url.protocol);
      }
    }
    return networkResponse;
  } catch (error) {
    console.log('Network failed, trying cache:', error);
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    return new Response('Offline - Content not available', { status: 503 });
  }
}

// Navigation strategy for page requests
async function navigationStrategy(request) {
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const url = new URL(request.url);
      // Only cache requests with supported schemes
      if (isSupportedScheme(url.protocol)) {
        try {
          const cache = await caches.open(DYNAMIC_CACHE_NAME);
          await cache.put(request, networkResponse.clone());
        } catch (cacheError) {
          console.error('Service Worker: Failed to cache in navigationStrategy:', cacheError, 'URL:', request.url);
        }
      } else {
        console.log('Service Worker: Skipping cache for unsupported scheme in navigationStrategy:', url.protocol);
      }
    }
    return networkResponse;
  } catch (error) {
    console.log('Navigation network failed, trying cache:', error);
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Fallback to cached homepage
    const fallbackResponse = await caches.match('/');
    if (fallbackResponse) {
      return fallbackResponse;
    }
    
    return new Response(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>PNE ClockIn - Offline</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
          body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #fef7f0; }
          .offline-message { max-width: 400px; margin: 0 auto; }
          .icon { font-size: 64px; margin-bottom: 20px; }
          h1 { color: #ff671b; }
          p { color: #666; }
        </style>
      </head>
      <body>
        <div class="offline-message">
          <div class="icon">📱</div>
          <h1>You're Offline</h1>
          <p>PNE ClockIn is not available right now. Please check your internet connection and try again.</p>
          <button onclick="window.location.reload()" style="background: #ff671b; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Retry</button>
        </div>
      </body>
      </html>
    `, {
      status: 200,
      headers: { 'Content-Type': 'text/html' }
    });
  }
}

// Background sync for offline actions
self.addEventListener('sync', event => {
  console.log('Service Worker: Background sync triggered:', event.tag);
  
  if (event.tag === 'clock-sync') {
    event.waitUntil(syncClockData());
  }
});

// Sync clock data when back online
async function syncClockData() {
  try {
    // Get pending clock actions from IndexedDB or localStorage
    const pendingActions = await getPendingClockActions();
    
    for (const action of pendingActions) {
      try {
        const response = await fetch('/api/clockings', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': await getCSRFToken()
          },
          body: JSON.stringify(action)
        });
        
        if (response.ok) {
          await removePendingAction(action.id);
          console.log('Synced clock action:', action);
        }
      } catch (error) {
        console.error('Failed to sync clock action:', error);
      }
    }
  } catch (error) {
    console.error('Background sync failed:', error);
  }
}

// Helper functions for offline data management
async function getPendingClockActions() {
  // Implementation would depend on your offline storage strategy
  return [];
}

async function removePendingAction(actionId) {
  // Implementation would depend on your offline storage strategy
}

async function getCSRFToken() {
  try {
    const response = await fetch('/api/csrf-token');
    const data = await response.json();
    return data.token;
  } catch (error) {
    console.error('Failed to get CSRF token:', error);
    return '';
  }
}

// Push notification handling
self.addEventListener('push', event => {
  console.log('Service Worker: Push notification received');
  
  const options = {
    body: event.data ? event.data.text() : 'New notification from PNE ClockIn',
    icon: '/icons/icon-192x192.png',
    badge: '/icons/icon-72x72.png',
    vibrate: [200, 100, 200],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Open App',
        icon: '/icons/icon-96x96.png'
      },
      {
        action: 'close',
        title: 'Close',
        icon: '/icons/icon-96x96.png'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('PNE ClockIn', options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
  console.log('Service Worker: Notification clicked');
  
  event.notification.close();
  
  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});

console.log('Service Worker: Script loaded');