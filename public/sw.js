const CACHE_NAME = 'famly-wealth-pwa-v1';
const ASSETS_TO_CACHE = [
  '/',
  '/manifest.json',
  '/logo-192.png',
  '/logo-512.png',
  '/offline.html',
  // Normally, we'd cache css/js files from build, but Vite handles them dynamically with hashes.
  // We can just rely on network-first for typical assets.
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        // Suppress errors for offline caching in case some assets are missing
        return cache.addAll(ASSETS_TO_CACHE).catch(err => {
            console.warn('Partial cache fill: ', err);
        });
      })
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  // Hanya intercept GET requests
  if (event.request.method !== 'GET') return;

  // Untuk request HTML (navigasi), gunakan pola Network-First
  if (event.request.headers.get('accept').includes('text/html')) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
           // Simpan salinan terbaru di cache
           const clone = response.clone();
           caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
           return response;
        })
        .catch(() => {
          // Jika offline dan tidak ada cache, return cache offline.html (opsional) atau root cache
          return caches.match(event.request).then(response => {
             return response || caches.match('/');
          });
        })
    );
  } else {
    // Untuk resource static lainnya (Pola Stale-While-Revalidate)
    event.respondWith(
      caches.match(event.request).then(cachedResponse => {
        const fetchPromise = fetch(event.request).then(networkResponse => {
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, networkResponse.clone()));
          return networkResponse;
        }).catch(() => {
            // Do nothing on failure to fetch static resource
        });
        return cachedResponse || fetchPromise;
      })
    );
  }
});
