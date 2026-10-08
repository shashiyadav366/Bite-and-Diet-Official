const CACHE_NAME = 'my-cache-v1';
const CACHE_FILES = [
  '/',
  '/no_internet',
  '/favicon.ico',
//   '/css/animate.css',
  '/css/shortcodes.css',
  '/css/main.css',
  '/css/responsive.css',
  //'/js/numinate.min6959.js?ver=4.9.3',
  '/js/main.js',
  '/fonts/Flaticon.eot',
  '/fonts/Flaticon.svg',
  '/fonts/Flaticon.ttf',
  '/fonts/Flaticon.woff',
  '/fonts/Flaticon.woff2',
  '/fonts/Flaticond41d.eot',
  '/fonts/fontawesome-webfont3e6e.eot',
  '/fonts/fontawesome-webfont3e6e.svg',
  '/fonts/fontawesome-webfont3e6e.ttf',
  '/fonts/fontawesome-webfont3e6e.woff',
  '/fonts/fontawesome-webfontd41d.eot',
  '/fonts/themify9f24.eot',
  '/fonts/themify9f24.svg',
  '/fonts/themify9f24.ttf',
  '/fonts/themify9f24.woff',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(CACHE_FILES);
    })
  );
});

self.addEventListener('fetch', (event) => {
  //console.log('Fetching:', event.request.url);
  event.respondWith(
    caches.match(event.request).then((response) => {
      // Check if there's no network connectivity
      if (!navigator.onLine) {
      //  console.log('Offline, responding with /no_internet');
        return caches.match('/no_internet');
      }

   //   console.log('Online, trying to fetch:', event.request.url);
      return response || fetch(event.request);
    })
  );
});



// Listen for the 'activate' event to handle cache cleanup and updates
self.addEventListener('activate', (event) => {
  event.waitUntil(
    // Delete old caches, if any, to free up space and ensure a clean update
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name !== CACHE_NAME) // Keep only the latest cache
          .map((name) => caches.delete(name))
      );
    })
  );
});

// Notify clients (your app) when the service worker is updated
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting(); // Activate the new service worker immediately
  }
});
