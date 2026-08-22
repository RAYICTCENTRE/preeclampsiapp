const CACHE_NAME = 'mothercare-offline-v1';

const APP_SHELL = [
    './',
    './screen6.html',
    './dashboard.html'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(APP_SHELL))
            .then(() => self.skipWaiting())
            .catch(err => console.warn('MotherCare cache install warning:', err))
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const request = event.request;

    // Only handle GET requests. POST requests to PHP APIs must reach
    // the server when Internet is available; screen6.html handles
    // offline assessment storage separately.
    if (request.method !== 'GET') return;

    event.respondWith(
        fetch(request)
            .then(response => {
                // Cache successful same-origin app files.
                if (response.ok && new URL(request.url).origin === self.location.origin) {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, copy));
                }
                return response;
            })
            .catch(() => caches.match(request).then(cached => {
                if (cached) return cached;

                // If navigation fails, return the cached assessment page.
                if (request.mode === 'navigate') {
                    return caches.match('./screen6.html');
                }

                return new Response('', {
                    status: 503,
                    statusText: 'Offline'
                });
            }))
    );
});
