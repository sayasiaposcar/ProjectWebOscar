
const VERSION = 'hs-v1';
const CORE = [
  '/', '/explore', '/group', '/messages',
  '/data/groups.json', '/data/group_posts.json'
];

self.addEventListener('install', e=>{
  e.waitUntil(caches.open(VERSION).then(c=>c.addAll(CORE)));
});
self.addEventListener('activate', e=>{
  e.waitUntil(
    caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==VERSION).map(k=>caches.delete(k))))
  );
});
self.addEventListener('fetch', e=>{
  const {request} = e;
  if (request.method!=='GET') return;
  e.respondWith(
    caches.match(request).then(cached=>{
      const fetcher = fetch(request).then(res=>{
        const copy = res.clone();
        caches.open(VERSION).then(c=>c.put(request, copy)).catch(()=>{});
        return res;
      }).catch(()=>cached);
      return cached || fetcher;
    })
  );
});
