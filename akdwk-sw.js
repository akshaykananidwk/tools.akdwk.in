/* Krishna Tools service worker: push notifications (notification.akdwk.in) + installable app with an offline page.
 * Network first for pages (always fresh), cache first for versioned /assets/ files. Cross-origin requests are not touched. */
try { importScripts('https://notification.akdwk.in/sdk/push-sw.js'); } catch (e) { /* push stays off if that host is unreachable; the app still works */ }

const KT_CACHE = 'kt-app-v1';
const OFFLINE_URL = '/offline.html';
const MAX_ASSETS = 80;

self.addEventListener('install', event => {
  event.waitUntil(caches.open(KT_CACHE).then(c => c.addAll([OFFLINE_URL, '/assets/img/logo/logo.svg', '/assets/img/logo/icon-192.png'])).catch(() => {}));
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(caches.keys()
    .then(keys => Promise.all(keys.filter(k => k.startsWith('kt-app-') && k !== KT_CACHE).map(k => caches.delete(k))))
    .then(() => self.clients.claim()));
});

async function trim(cache) {
  const keys = await cache.keys();
  for (let i = 0; i < keys.length - MAX_ASSETS; i++) await cache.delete(keys[i]);
}

self.addEventListener('fetch', event => {
  const req = event.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;
  if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api/')) return;
  if (req.mode === 'navigate') {
    event.respondWith(fetch(req).catch(() => caches.match(OFFLINE_URL)));
    return;
  }
  if (url.pathname.startsWith('/assets/')) {
    event.respondWith(caches.open(KT_CACHE).then(async cache => {
      const hit = await cache.match(req);
      if (hit) return hit;
      const res = await fetch(req);
      if (res.ok && res.type === 'basic') { cache.put(req, res.clone()); trim(cache); }
      return res;
    }));
  }
});
