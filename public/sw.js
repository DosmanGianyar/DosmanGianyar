const CACHE = 'sims-v2';
const OFFLINE_URL = '/offline';

self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE).then(c => c.addAll([
            OFFLINE_URL,
            '/img/logo_sekolah.png',
            '/img/icon-192.png',
            '/img/icon-512.png',
            '/img/icon-maskable-192.png',
            '/img/icon-maskable-512.png',
        ]))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (e) => {
    const req = e.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    // Navigation: network-first, fallback to offline page
    if (req.mode === 'navigate') {
        e.respondWith(fetch(req).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    // Static assets: cache-first with background revalidation
    if (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/img/') ||
        url.pathname.startsWith('/favicon')
    ) {
        e.respondWith(
            caches.open(CACHE).then(async c => {
                const cached = await c.match(req);
                const fetchPromise = fetch(req)
                    .then(res => { if (res.ok) c.put(req, res.clone()); return res; })
                    .catch(() => null);
                return cached || fetchPromise;
            })
        );
    }
});
