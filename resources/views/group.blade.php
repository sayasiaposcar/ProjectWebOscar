@extends('layouts.app')
@section('title','Group | HobiSpace')

@section('content')
<section x-data="GroupPage()" x-init="init()" class="grid grid-cols-1 md:grid-cols-[260px,minmax(0,1fr),300px] gap-4 lg:gap-6">

  <!-- Left Sidebar -->
  <aside class="hidden md:block glass rounded-2xl p-3 h-fit sticky top-24">
    <div class="flex items-center gap-2 p-2 rounded-xl">
      <img src="/images/demo/rina.jpg" class="w-10 h-10 rounded-full object-cover" alt="Rina" loading="lazy">
      <div>
        <div class="font-semibold">Rina Putri</div>
        <div class="text-xs muted">@rina</div>
      </div>
    </div>
    <nav class="mt-2 grid">
      <a href="/explore" class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg:white/5">🏠 Beranda</a>
      <a href="/group"   class="px-3 py-2 rounded-xl btn-accent text-white text-center mt-1">👥 Group</a>
      <a href="/submit"  class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg:white/5 mt-1">➕ Buat Post</a>
      <a href="/u/rina"  class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg:white/5 mt-1">👤 Profil</a>
      <button @click="resetLocal()" class="px-3 py-2 rounded-xl btn-accent-ghost text-left mt-1">♻️ Reset Data Lokal</button>
    </nav>
  </aside>

  <!-- Group List -->
  <div>
    <h1 class="text-xl font-bold mb-3">Daftar Grup</h1>

    <!-- Search + Sort + CTA -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-4">
      <div class="flex-1 flex gap-2">
        <input x-model.trim="q" class="flex-1 rounded-xl px-3 py-2 border" placeholder="Cari grup atau #tag…">
        <select x-model="sortBy" class="rounded-xl px-3 py-2 border w-48">
          <option value="favpin">Favorit/Pin dulu</option>
          <option value="latest">Terbaru</option>
          <option value="members">Member terbanyak</option>
        </select>
      </div>
      <a href="{{ route('group.create') }}" class="px-4 py-2 btn-accent rounded-xl text-white">➕ Buat Grup</a>
    </div>

    <!-- Skeleton saat loading -->
    <div class="grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-6 mb-6" x-show="loading">
      <template x-for="i in 6" :key="i">
        <div class="glass rounded-2xl p-4 animate-pulse">
          <div class="h-4 w-1/2 bg-white/30 rounded mb-3"></div>
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-white/20"></div>
            <div class="flex-1">
              <div class="h-3 w-1/3 bg-white/20 rounded mb-1.5"></div>
              <div class="h-3 w-1/4 bg-white/10 rounded"></div>
            </div>
            <div class="h-6 w-20 bg-white/20 rounded"></div>
          </div>
          <div class="h-6 w-2/3 bg-white/10 rounded"></div>
        </div>
      </template>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-6">
      <template x-for="g in filteredPaged" :key="g.slug || g.id">
        <article
          class="relative glass rounded-2xl p-4 flex flex-col justify-between transition-shadow will-change-transform cursor-pointer card"
          :class="g.joined ? 'hover:shadow-lg' : 'opacity-90'"
          @click="openOrJoin(g)"
          @keyup.enter.prevent="openOrJoin(g)"
          @keyup.space.prevent="openOrJoin(g)"
          tabindex="0"
          role="button"
          :aria-label="'Buka grup '+g.name">

          <!-- Actions kanan-atas -->
          <div class="absolute top-2 right-2 flex items-center gap-1 act-bar">
            <button x-show="g.isLocal"
                    @click.stop="confirmDelete(g)"
                    class="act-btn delete" title="Hapus">🗑️</button>

            <button class="act-btn pin" :class="isPinned(g)?'is-on':''"
                    @click.stop="togglePin(g)" title="Pin">📌</button>

            <button class="act-btn star" :class="isFav(g)?'is-on':''"
                    @click.stop="toggleFav(g)" title="Favorit">★</button>

            <button class="act-btn share"
                    @click.stop="shareGroup(g)" title="Bagikan">⤴︎</button>
          </div>

          <!-- Judul -->
          <h2 class="font-semibold text-lg leading-tight mb-2 line-clamp-2" x-text="g.name"></h2>

          <!-- Owner + Join -->
          <div class="flex items-center gap-3 mb-3">
            <img :src="g.avatar" loading="lazy"
                 class="w-10 h-10 rounded-full object-cover blur-up" @load="$el.classList.add('is-loaded')" alt="">
            <div class="min-w-0 flex-1">
              <div class="text-sm font-medium truncate" x-text="ownerName(g)"></div>
              <template x-if="ownerHandle(g)">
                <div class="text-xs muted truncate">
                  @<span x-text="ownerHandle(g)"></span>
                  <span class="ml-1 align-middle" x-show="g.verified" title="Verified">✅</span>
                </div>
              </template>
            </div>

            <!-- Tombol join -->
            <button type="button"
              @click.stop="joinAndGo(g)"
              class="px-3 py-1 text-xs rounded-lg font-medium transition-colors duration-200 border"
              :class="g.joined
                ? 'bg-emerald-600/15 text-emerald-400 border-emerald-400/30'
                : 'btn-accent text-white border-transparent hover:opacity-90'">
              <span x-text="g.joined ? 'Bergabung' : 'Gabung'"></span>
            </button>
          </div>

          <!-- Badges kecil -->
          <div class="flex items-center gap-2 mb-2 text-[11px]">
            <span class="px-2 py-0.5 rounded-full bg-white/10 border border-white/10"
                  x-text="g.is_public===false ? 'Private' : 'Public'"></span>
            <span class="px-2 py-0.5 rounded-full bg-white/10 border border-white/10" x-show="g.official">Official</span>
          </div>

          <!-- Tags (toggle) -->
          <div class="flex flex-wrap gap-2 mb-3">
            <template x-for="t in tagsOf(g)" :key="t">
              <button
                type="button"
                @click.stop="toggleTagFilter(t)"
                :class="{'ring-2 ring-cyan-400/60': isTagActive(t)}"
                class="chip hover:brightness-105">
                #<span x-text="t"></span>
              </button>
            </template>
          </div>

          <!-- Meta -->
          <div class="mt-auto inline-flex items-center gap-2 text-xs px-3 py-1 rounded-full bg-white/5 border border-white/10">
            <span x-text="formatDate(g.created_at)"></span>
            <span>·</span>
            <span x-text="(g.members||0)+' anggota'"></span>
          </div>
        </article>
      </template>
    </div>

    <!-- Pagination ringan -->
    <div class="text-center mt-4" x-show="filteredPaged.length < filtered.length && !loading">
      <button @click="page++" class="px-4 py-2 rounded-xl btn-accent-ghost">Muat lagi</button>
    </div>

    <!-- Kosong -->
    <div class="glass p-4 mt-4 rounded-2xl text-sm muted" x-show="!loading && !filtered.length">
      Grup tidak ditemukan
    </div>
  </div>

  <!-- Right Sidebar -->
<aside class="hidden lg:block glass rounded-2xl p-3 h-fit sticky top-24">
  <div class="font-semibold mb-2">Trending Tags</div>
  <div class="flex flex-wrap gap-2">
    <template x-for="t in trendingTags" :key="t[0]">
      <button
        type="button"
        @click="toggleTagFilter(t[0])"
        :class="{'ring-2 ring-cyan-400/60': isTagActive(t[0])}"
        class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-sm">
        #<span x-text="t[0]"></span>
        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-white/10" x-text="t[1]"></span>
      </button>
    </template>
  </div>
</aside>

@endsection

@push('scripts')
<script>
function GroupPage(){
  // ===== kunci localStorage =====
  const JOIN_KEY   = 'hs-joined-groups';
  const LOCAL_KEYS = ['hs-custom-groups','hs-user-groups','hs-groups-user'];
  const PRIMARY_LOCAL_KEY = 'hs-custom-groups';
  const FAV_KEY = 'hs-fav-groups';
  const PIN_KEY = 'hs-pin-groups';

  const loadJoined = () => new Set(JSON.parse(localStorage.getItem(JOIN_KEY) || '[]'));
  const saveJoined = (s) => localStorage.setItem(JOIN_KEY, JSON.stringify([...s]));
  const loadSet = (k) => new Set(JSON.parse(localStorage.getItem(k)||'[]'));
  const saveSet = (k,s) => localStorage.setItem(k, JSON.stringify([...s]));

  function loadLocalGroups(){
    let merged = [];
    for (const k of LOCAL_KEYS){
      try { merged = merged.concat(JSON.parse(localStorage.getItem(k) || '[]')); } catch {}
    }
    return merged.map(g => normalize(g, true));
  }
  function saveLocalGroups(groups){ localStorage.setItem(PRIMARY_LOCAL_KEY, JSON.stringify(groups)); }
  function removeLocalGroupBySlug(slug){
    let keep = [];
    for (const k of LOCAL_KEYS){
      try{
        const arr  = JSON.parse(localStorage.getItem(k) || '[]');
        const next = arr.filter(g => (g.slug || g.id) !== slug);
        localStorage.setItem(k, JSON.stringify(next));
        keep = keep.concat(next);
      }catch{}
    }
    saveLocalGroups(keep);
  }

  const slugify = (s='') => (s||'').toString().trim().toLowerCase()
      .replace(/[^\w\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-');

  const normalize = (g, isLocal=false) => ({
    id: g.id || ('g-'+Date.now()+Math.random().toString(36).slice(2,7)),
    slug: g.slug || slugify(g.name || 'group'),
    name: g.name || 'Tanpa Nama',
    avatar: g.avatar || '/images/demo/groups/default.png',
    cover: g.cover || '',
    owner: g.owner || 'Anonim',
    handle: g.handle || '',
    tags: Array.isArray(g.tags) ? g.tags : (g.tags ? String(g.tags).split(',').map(s=>s.trim()).filter(Boolean) : []),
    desc: g.desc || '',
    created_at: g.created_at || new Date().toISOString().slice(0,10),
    members: Number(g.members || 0),
    is_public: ('is_public' in g) ? !!g.is_public : true,
    verified: !!g.verified,
    official: !!g.official,
    isLocal
  });

  return {
    // UI state
    q: '', sortBy:'favpin',
    loading:true, page:1, per:12,

    // data
    groups: [],
    _joined: loadJoined(),
    favs: loadSet(FAV_KEY),
    pins: loadSet(PIN_KEY),

    async init(){
      this.loading = true;

      // base (JSON)
      let base = [];
      try { base = await (await fetch('/data/groups.json',{cache:'force-cache'})).json(); } catch { base = []; }
      base = base.map(g => normalize(g, false));

      // lokal
      const mine = loadLocalGroups();

      this.groups = [...mine, ...base];
      this.syncJoined();
      this.loading = false;

      // watchers → reset halaman saat filter/sort berubah
      this.$watch('q',      () => { this.page = 1; });
      this.$watch('sortBy', () => { this.page = 1; });

      // listeners
      window.addEventListener('pageshow', () => this.syncJoined());
      window.addEventListener('storage', (e) => {
        if (e.key === JOIN_KEY || LOCAL_KEYS.includes(e.key)) this.init();
        if (e.key === FAV_KEY) this.favs = loadSet(FAV_KEY);
        if (e.key === PIN_KEY) this.pins = loadSet(PIN_KEY);
      });
    },

    syncJoined(){
      this._joined = loadJoined();
      this.groups = this.groups.map(g => ({...g, joined: this._joined.has(g.slug || g.id)}));
    },

    // Favorit & Pin
    isFav(g){ return this.favs.has(g.slug || g.id); },
    toggleFav(g){
      const k = g.slug || g.id;
      this.favs.has(k) ? this.favs.delete(k) : this.favs.add(k);
      saveSet(FAV_KEY, this.favs);
      window.toast?.(this.isFav(g) ? 'Ditambahkan ke Favorit' : 'Dihapus dari Favorit');
    },
    isPinned(g){ return this.pins.has(g.slug || g.id); },
    togglePin(g){
      const k = g.slug || g.id;
      this.pins.has(k) ? this.pins.delete(k) : this.pins.add(k);
      saveSet(PIN_KEY, this.pins);
      window.toast?.(this.isPinned(g) ? 'Disematkan' : 'Lepas semat');
    },

    // Tag filter toggle (Trending & kartu grup)
   toggleTagFilter(tag){
  const cur = (this.q || '').toLowerCase().trim();
  const t   = String(tag).toLowerCase();
  // jika q sudah sama (dengan atau tanpa #), kosongkan → reset
  this.q = (cur === t || cur === '#'+t) ? '' : ('#' + tag);
  this.page = 1; // reset pagination
},
isTagActive(tag){
  const cur = (this.q || '').toLowerCase().trim();
  const t   = String(tag).toLowerCase();
  return cur === t || cur === '#'+t;
},


    // Share
    shareGroup(g){
      const url = `${location.origin}/group/${g.slug}`;
      const data = { title: g.name, text: `Gabung ke grup ${g.name} di HobiSpace`, url };
      if (navigator.share) navigator.share(data).catch(()=>{});
      else { navigator.clipboard.writeText(url).then(()=>window.toast?.('Link disalin')); }
    },

    // computed
    get filtered(){
      const qRaw = (this.q || '').trim();
      const q    = qRaw.toLowerCase();

      let arr = this.groups;

      if (q){
        if (q.startsWith('#')){
          // strict tag filter (#travel → hanya tag == travel)
          const tag = q.slice(1);
          arr = arr.filter(g => (g.tags || []).some(t => t.toLowerCase() === tag));
        } else {
          // full-text
          arr = arr.filter(g =>
            (g.name   || '').toLowerCase().includes(q) ||
            (g.handle || '').toLowerCase().includes(q) ||
            (g.tags   || []).some(t => t.toLowerCase().includes(q))
          );
        }
      }

      // sort
      if(this.sortBy==='members'){
        arr.sort((a,b)=>(b.members||0)-(a.members||0));
      }else if(this.sortBy==='latest'){
        arr.sort((a,b)=> new Date(b.created_at) - new Date(a.created_at));
      }else{ // favpin
        arr.sort((a,b)=>{
          const pa=this.isPinned(a), pb=this.isPinned(b);
          if(pa!==pb) return pa? -1:1;
          const fa=this.isFav(a), fb=this.isFav(b);
          if(fa!==fb) return fa? -1:1;
          return new Date(b.created_at) - new Date(a.created_at);
        });
      }
      return arr;
    },
    get filteredPaged(){ return this.filtered.slice(0, this.page*this.per); },

    get trendingTags(){
      const map = new Map();
      this.groups.forEach(g => (g.tags||[]).forEach(t => map.set(t, (map.get(t)||0)+1)));
      return [...map.entries()].sort((a,b)=>b[1]-a[1]).slice(0,12);
    },

    ownerName(g){ return g.owner || '—'; },
    ownerHandle(g){ return g.handle || ''; },
    tagsOf(g){ return Array.isArray(g.tags) ? g.tags : []; },

    formatDate(d){
      const dt = new Date(d || Date.now());
      return dt.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
    },

    openOrJoin(g){
      const latest = loadJoined();
      if (latest.has(g.slug || g.id)) {
        location.href = '/group/' + g.slug;
      } else {
        window.toast?.('Gabung dulu untuk membuka grup');
      }
    },

    joinAndGo(g){
      const key = g.slug || g.id;
      if (!g.joined) {
        this._joined.add(key);
        saveJoined(this._joined);
        g.joined = true;
        window.toast?.(`Bergabung ke "${g.name}"`);
      }
      setTimeout(() => location.href = '/group/' + g.slug, 180);
    },

    // HAPUS GRUP LOKAL
    confirmDelete(g){
      if (!g?.isLocal) return;
      if (confirm(`Hapus grup "${g.name}"?\nPost lokal & status gabung juga akan dibersihkan.`)){
        this.deleteGroup(g);
      }
    },
    deleteGroup(g){
      const slug = g.slug || g.id;
      removeLocalGroupBySlug(slug);
      const j = loadJoined(); j.delete(slug); saveJoined(j);
      localStorage.removeItem('hs-gposts-' + slug);
      this.groups = this.groups.filter(x => (x.slug || x.id) !== slug);
      window.toast?.('Grup dihapus dari perangkat ini.');
    },

    // RESET
    resetLocal(){
      if (!confirm('Hapus SEMUA data lokal (grup yang kamu buat, status join, dan posting lokal)?')) return;
      for (const k of LOCAL_KEYS) localStorage.removeItem(k);
      Object.keys(localStorage).forEach(key => {
        if (key.startsWith('hs-gposts-') || key.startsWith('hs-gcomments-')) localStorage.removeItem(key);
      });
      localStorage.removeItem(JOIN_KEY);
      localStorage.removeItem(FAV_KEY);
      localStorage.removeItem(PIN_KEY);
      this.init();
      window.toast?.('Data lokal direset.');
    }
  }
}
</script>

<style>
.card { transition: box-shadow .18s ease, transform .18s ease; }
.card:hover { box-shadow: 0 8px 22px rgba(0,0,0,.18); transform: translateY(-2px); }
.card button { will-change: background-color, color, border-color, opacity; }
.line-clamp-2{ display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:2; overflow:hidden; }

/* action buttons (warna) */
.act-bar{ display:flex; gap:.35rem; padding:.15rem; border-radius:999px;
  background:rgba(2,6,23,.24); backdrop-filter:blur(8px) saturate(160%); }
.dark .act-bar{ background:rgba(255,255,255,.08) }
.act-btn{ width:28px;height:28px;display:grid;place-items:center;border-radius:9999px;
  border:1px solid rgba(0,0,0,.08); color:#fff; font-weight:700; font-size:12px; line-height:1;
  box-shadow:0 4px 16px rgba(2,6,23,.25); transition:transform .15s ease; }
.dark .act-btn{ border-color:rgba(255,255,255,.15) }
.act-btn:hover{ transform:translateY(-1px) }
.act-btn.star   { background:linear-gradient(135deg,#fbbf24,#f59e0b); color:#111 }
.act-btn.share  { background:linear-gradient(135deg,#06b6d4,#0ea5e9) }
.act-btn.pin    { background:linear-gradient(135deg,#a78bfa,#6366f1) }
.act-btn.delete { background:linear-gradient(135deg,#f87171,#ef4444) }
.act-btn.is-on{ box-shadow:0 0 0 2px rgba(250,204,21,.45) inset }

/* blur-up lazy images */
.blur-up{ filter:blur(12px) saturate(.9); transform:scale(1.02); transition:filter .25s ease, transform .25s ease, opacity .25s ease }
.blur-up.is-loaded{ filter:blur(0); transform:none; opacity:1 }
</style>
@endpush
