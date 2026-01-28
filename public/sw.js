const CACHE_NAME = 'ministrify-v4';
const OFFLINE_URL = '/offline.html';

// Only static assets - NEVER cache HTML pages
const PRECACHE_ASSETS = [
    '/offline.html',
    '/manifest.json',
    '/js/pwa-db.js',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png'
];

// Install event - precache assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
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
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    if (url.origin !== location.origin) return;

    // Skip auth & API routes entirely
    if (url.pathname.startsWith('/login') ||
        url.pathname.startsWith('/logout') ||
        url.pathname.startsWith('/register') ||
        url.pathname.startsWith('/sanctum') ||
        url.pathname.startsWith('/api/')) {
        return;
    }

    // Page navigations - NEVER cache, only offline fallback
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Static assets only - cache-first with background update
    if (event.request.url.match(/\.(js|css|png|jpg|jpeg|svg|gif|woff|woff2|ico|webp)$/)) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                const fetchPromise = fetch(event.request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                    }
                    return response;
                }).catch(() => cachedResponse);

                return cachedResponse || fetchPromise;
            })
        );
        return;
    }

    // Everything else - network only, no caching
});

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-schedule-actions') {
        event.waitUntil(syncScheduleActions());
    }
});

async function syncScheduleActions() {
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
        client.postMessage({ type: 'SYNC_OFFLINE_ACTIONS' });
    });
}

// Push notifications
self.addEventListener('push', (event) => {
    if (!event.data) return;

    let data;
    try {
        data = event.data.json();
    } catch (e) {
        data = {
            title: 'Ministrify',
            body: event.data.text()
        };
    }

    const options = {
        body: data.body,
        icon: '/icons/icon-192x192.png',
        badge: '/icons/badge-72x72.png',
        vibrate: [100, 50, 100],
        tag: data.tag || 'default',
        renotify: true,
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

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'close') return;

    const urlToOpen = event.notification.data?.url || '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                for (const client of clientList) {
                    if (client.url.includes(location.origin) && 'focus' in client) {
                        client.navigate(urlToOpen);
                        return client.focus();
                    }
                }
                return clients.openWindow(urlToOpen);
            })
    );
});

// Message handler
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
