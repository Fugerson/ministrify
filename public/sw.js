const CACHE_NAME = 'ministrify-v1';
const OFFLINE_URL = '/offline.html';

// Only cache static assets that don't require auth
const PRECACHE_ASSETS = [
    '/offline.html',
    '/manifest.json'
];

// Install event - precache assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Opened cache');
                // Use addAll with error handling
                return Promise.allSettled(
                    PRECACHE_ASSETS.map(url =>
                        cache.add(url).catch(err => {
                            console.warn('Failed to cache:', url, err);
                        })
                    )
                );
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - network first, cache fallback
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip API requests and auth routes
    const url = new URL(event.request.url);
    if (url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/login') ||
        url.pathname.startsWith('/logout') ||
        url.pathname.startsWith('/register')) {
        return;
    }

    // Skip external requests
    if (url.origin !== location.origin) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Check if valid response
                if (!response || response.status !== 200) {
                    return response;
                }

                // Clone the response for caching static assets
                const responseToCache = response.clone();

                // Cache static assets only
                if (event.request.url.match(/\.(js|css|png|jpg|jpeg|svg|gif|woff|woff2|ico)$/)) {
                    caches.open(CACHE_NAME)
                        .then((cache) => {
                            cache.put(event.request, responseToCache);
                        });
                }

                return response;
            })
            .catch(() => {
                // If offline and requesting a page, show offline page
                if (event.request.mode === 'navigate') {
                    return caches.match(OFFLINE_URL);
                }
                // Try to return cached version
                return caches.match(event.request);
            })
    );
});

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-data') {
        event.waitUntil(syncData());
    }
});

// Push notifications
self.addEventListener('push', (event) => {
    if (!event.data) return;

    const data = event.data.json();
    const options = {
        body: data.body,
        icon: '/icons/icon-192x192.png',
        badge: '/icons/badge-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            url: data.url || '/dashboard'
        },
        actions: [
            { action: 'open', title: 'Відкрити' },
            { action: 'close', title: 'Закрити' }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'Ministrify', options)
    );
});

// Notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'close') return;

    event.waitUntil(
        clients.openWindow(event.notification.data.url)
    );
});

async function syncData() {
    // Sync any pending offline data
    console.log('Syncing data...');
}
