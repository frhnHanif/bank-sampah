// Ubah nama versi cache agar memicu pembaruan Service Worker
const CACHE_NAME = 'bank-sampah-cache-v2';

const urlsToCache = [
    '/manifest.json',
];

self.addEventListener('install', event => {
    self.skipWaiting(); // Memaksa SW baru untuk segera aktif
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(urlsToCache);
        })
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    // Hapus cache versi lama
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

self.addEventListener('fetch', event => {
    // Hanya tangani request GET (jangan cache POST/PUT/DELETE)
    if (event.request.method !== 'GET') return;

    event.respondWith(
        // STRATEGI BARU: NETWORK FIRST
        fetch(event.request)
            .then(networkResponse => {
                // Jika jaringan sukses, simpan salinan terbaru ke cache
                return caches.open(CACHE_NAME).then(cache => {
                    cache.put(event.request, networkResponse.clone());
                    return networkResponse;
                });
            })
            .catch(() => {
                // Jika offline (jaringan gagal), baru ambil dari cache
                return caches.match(event.request);
            })
    );
});