const CACHE_NAME = 'bank-sampah-cache-v1';

// Daftar URL dasar yang langsung disimpan di cache saat aplikasi diinstal
const urlsToCache = [
    '/',
    '/manifest.json',
    // Anda bisa menambahkan path asset statis lainnya di sini jika diperlukan
];

// Event Install: Menyimpan file statis ke dalam cache
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
    );
});

// Event Fetch: Mengambil data dari cache jika offline
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Jika request ada di cache, kembalikan dari cache
                if (response) {
                    return response;
                }
                
                // Jika tidak ada di cache, ambil dari jaringan (network)
                return fetch(event.request).catch(() => {
                    // Di sini Anda bisa mengembalikan halaman offline custom 
                    // jika request gagal (misal koneksi putus) dan data tidak ada di cache.
                    // return caches.match('/offline.html'); 
                });
            })
    );
});

// Event Activate: Membersihkan cache lama jika ada pembaruan versi (CACHE_NAME berubah)
self.addEventListener('activate', event => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});