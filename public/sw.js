// Service Worker for Personal Secure Notes System
const CACHE_NAME = 'secure-notes-v1';
const urlsToCache = [
  '/',
  '/dashboard',
  '/notes',
  '/tasks',
  '/tags',
  '/settings',
  '/offline',
  'https://cdn.tailwindcss.com',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Install event
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        // Cache resources individually to handle failures gracefully
        return Promise.allSettled(
          urlsToCache.map(url => {
            return cache.add(url).catch(err => {
              console.warn(`Failed to cache ${url}:`, err);
              return null;
            });
          })
        );
      })
      .then(() => {
        console.log('Cache installation completed');
        return self.skipWaiting();
      })
  );
});

// Fetch event
self.addEventListener('fetch', event => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Return cached version if available
        if (response) {
          return response;
        }
        
        // Try to fetch from network
        return fetch(event.request)
          .then(response => {
            // Check if we received a valid response
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // Clone the response
            const responseToCache = response.clone();

            // Add to cache for future use
            caches.open(CACHE_NAME)
              .then(cache => {
                cache.put(event.request, responseToCache);
              });

            return response;
          })
          .catch(() => {
            // If network fails, return offline page for navigation requests
            if (event.request.destination === 'document') {
              return caches.match('/offline');
            }
            // For other requests, return a basic response
            return new Response('Offline', {
              status: 503,
              statusText: 'Service Unavailable'
            });
          });
      })
  );
});

// Activate event
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});
