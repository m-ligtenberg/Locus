/**
 * TravelBot v3.0 Service Worker
 * PWA caching en offline functionaliteit
 */

const CACHE_NAME = 'travelbot-v3.0.0';
const RUNTIME_CACHE = 'travelbot-runtime-v3';

// Files to cache for offline functionality
const PRECACHE_URLS = [
    '/',
    '/index.html',
    '/style.css',
    '/main.js',
    '/translations.js',
    '/speech.js',
    '/tts.js', 
    '/location.js',
    '/chat.js',
    '/share.js',
    '/mock.js',
    '/manifest.json',
    '/icon-192.png',
    '/icon-512.png'
];

// Runtime cache patterns
const RUNTIME_CACHE_PATTERNS = [
    /^https:\/\/nominatim\.openstreetmap\.org\//,
    /^\/api\//
];

self.addEventListener('install', event => {
    console.log('TravelBot SW: Installing service worker');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('TravelBot SW: Caching app shell');
                return cache.addAll(PRECACHE_URLS);
            })
            .then(() => {
                console.log('TravelBot SW: Skip waiting');
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('TravelBot SW: Install failed:', error);
            })
    );
});

self.addEventListener('activate', event => {
    console.log('TravelBot SW: Activating service worker');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE) {
                            console.log('TravelBot SW: Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('TravelBot SW: Taking control');
                return self.clients.claim();
            })
    );
});

self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);
    
    // Handle different types of requests
    if (event.request.destination === 'document') {
        // HTML documents - cache first, then network
        event.respondWith(cacheFirst(event.request));
    } else if (RUNTIME_CACHE_PATTERNS.some(pattern => pattern.test(event.request.url))) {
        // API calls - network first, then cache
        event.respondWith(networkFirst(event.request));
    } else if (event.request.destination === 'image') {
        // Images - cache first
        event.respondWith(cacheFirst(event.request));
    } else if (PRECACHE_URLS.includes(url.pathname)) {
        // App shell - cache first
        event.respondWith(cacheFirst(event.request));
    } else {
        // Everything else - network first
        event.respondWith(networkFirst(event.request));
    }
});

// Cache-first strategy
async function cacheFirst(request) {
    try {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        const networkResponse = await fetch(request);
        if (networkResponse && networkResponse.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.error('TravelBot SW: Cache-first failed:', error);
        
        // Return offline fallback if available
        if (request.destination === 'document') {
            const fallback = await caches.match('/');
            return fallback || new Response('Offline - TravelBot niet beschikbaar', {
                status: 503,
                headers: { 'Content-Type': 'text/plain' }
            });
        }
        
        throw error;
    }
}

// Network-first strategy
async function networkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse && networkResponse.status === 200) {
            // Cache successful responses
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.warn('TravelBot SW: Network failed, trying cache:', error);
        
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline response for API calls
        if (request.url.includes('/api/')) {
            return new Response(JSON.stringify({
                error: 'offline',
                message: 'Request failed - device is offline'
            }), {
                status: 503,
                headers: { 'Content-Type': 'application/json' }
            });
        }
        
        throw error;
    }
}

// Handle background sync for offline actions
self.addEventListener('sync', event => {
    console.log('TravelBot SW: Background sync:', event.tag);
    
    if (event.tag === 'chat-message') {
        event.waitUntil(syncChatMessages());
    } else if (event.tag === 'location-update') {
        event.waitUntil(syncLocationUpdates());
    }
});

async function syncChatMessages() {
    try {
        // Get pending messages from IndexedDB or localStorage
        const pendingMessages = JSON.parse(localStorage.getItem('travelbot-pending-messages') || '[]');
        
        for (const message of pendingMessages) {
            try {
                const response = await fetch('/api/chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(message)
                });
                
                if (response.ok) {
                    // Remove from pending messages
                    const index = pendingMessages.indexOf(message);
                    if (index > -1) {
                        pendingMessages.splice(index, 1);
                    }
                }
            } catch (error) {
                console.warn('TravelBot SW: Failed to sync message:', error);
            }
        }
        
        localStorage.setItem('travelbot-pending-messages', JSON.stringify(pendingMessages));
        console.log('TravelBot SW: Chat messages synced');
    } catch (error) {
        console.error('TravelBot SW: Chat sync failed:', error);
    }
}

async function syncLocationUpdates() {
    try {
        // Sync any pending location-based requests
        console.log('TravelBot SW: Location updates synced');
    } catch (error) {
        console.error('TravelBot SW: Location sync failed:', error);
    }
}

// Handle push notifications (if implemented)
self.addEventListener('push', event => {
    const options = {
        body: event.data ? event.data.text() : 'TravelBot has an update',
        icon: '/icon-192.png',
        badge: '/icon-192.png',
        tag: 'travelbot-notification',
        requireInteraction: false,
        actions: [
            {
                action: 'open',
                title: 'Open TravelBot'
            },
            {
                action: 'dismiss',
                title: 'Dismiss'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification('TravelBot', options)
    );
});

// Handle notification clicks
self.addEventListener('notificationclick', event => {
    event.notification.close();
    
    if (event.action === 'open') {
        event.waitUntil(
            clients.matchAll().then(clientList => {
                // Focus existing window if available
                for (const client of clientList) {
                    if (client.url === '/' && 'focus' in client) {
                        return client.focus();
                    }
                }
                
                // Open new window if no existing window
                if (clients.openWindow) {
                    return clients.openWindow('/');
                }
            })
        );
    }
});

// Share target handler
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);
    
    if (url.pathname === '/share' && event.request.method === 'GET') {
        // Handle shared content
        const title = url.searchParams.get('title') || '';
        const text = url.searchParams.get('text') || '';
        const sharedUrl = url.searchParams.get('url') || '';
        
        event.respondWith(
            Response.redirect(`/?share=true&title=${encodeURIComponent(title)}&text=${encodeURIComponent(text)}&url=${encodeURIComponent(sharedUrl)}`, 302)
        );
    }
});

// Periodic background sync (experimental)
self.addEventListener('periodicsync', event => {
    if (event.tag === 'location-update') {
        event.waitUntil(handlePeriodicLocationUpdate());
    }
});

async function handlePeriodicLocationUpdate() {
    try {
        // Could update location in background for better UX
        console.log('TravelBot SW: Periodic location update');
    } catch (error) {
        console.error('TravelBot SW: Periodic sync failed:', error);
    }
}

// Handle app updates
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        console.log('TravelBot SW: Skipping waiting');
        self.skipWaiting();
    }
});

// Cache cleanup on storage pressure
self.addEventListener('storage', event => {
    if (event.type === 'storage-pressure') {
        console.log('TravelBot SW: Storage pressure detected, cleaning cache');
        cleanupOldCache();
    }
});

async function cleanupOldCache() {
    try {
        const cache = await caches.open(RUNTIME_CACHE);
        const requests = await cache.keys();
        
        // Remove oldest entries if cache is too large
        if (requests.length > 100) {
            const toDelete = requests.slice(0, requests.length - 50);
            await Promise.all(toDelete.map(request => cache.delete(request)));
            console.log(`TravelBot SW: Cleaned ${toDelete.length} cache entries`);
        }
    } catch (error) {
        console.error('TravelBot SW: Cache cleanup failed:', error);
    }
}

console.log('TravelBot SW: Service Worker loaded');
