const CACHE_NAME = 'eltrack-v1';
const STATIC_ASSETS = [
  '/icons/icon-192.png',
  '/icons/icon-512.png',
  '/manifest.json'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Network-first strategy for dynamic content (never cache sensitive finance API calls)
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  // Exclude dashboard data, transactions, dynamic APIs from cache
  if (url.pathname.startsWith('/dashboard/data') ||
      url.pathname.startsWith('/transactions') ||
      url.pathname.startsWith('/voice') ||
      event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request);
    })
  );
});
