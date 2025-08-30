@extends('layouts.app')
@section('title','Detail Grup | HobiSpace')

@section('content')
<div x-data="GroupShow()" x-init="init()" class="max-w-5xl mx-auto">

  <!-- ====== KETEMU GRUP ====== -->
  <template x-if="group">
    <div class="glass rounded-2xl overflow-hidden">

      <!-- HERO / COVER -->
      <div class="relative h-48 md:h-64">
        <div class="absolute inset-0"
             :style="group?.cover
                      ? `background:url('${group.cover}') center/cover no-repeat`
                      : (group?.avatar
                          ? `background:url('${group.avatar}') center/cover no-repeat; filter:blur(18px); transform:scale(1.1)`
                          : 'background:linear-gradient(90deg,#0ea5e9,#22d3ee)')">
        </div>
        <div class="absolute inset-0 bg-black/30"></div>
      </div>

      <!-- INFO -->
      <div class="p-5">
        <div class="flex flex-wrap items-center gap-3">
          <img :src="group.avatar" class="w-16 h-16 rounded-xl object-cover" alt="">

          <div class="min-w-0 flex-1">
            <h1 class="font-bold text-xl leading-tight truncate" x-text="group.name"></h1>
            <p class="text-sm muted truncate">
              by <span x-text="group.owner"></span>
              <span class="opacity-60">·</span>
              @<span x-text="group.handle"></span>
            </p>
          </div>

          <!-- JOIN -->
          <button
            class="px-4 py-2 rounded-xl font-medium border transition shrink-0"
            :class="group?.joined
              ? 'bg-emerald-600/15 text-emerald-400 border-emerald-400/30 hover:bg-emerald-600/25'
              : 'btn-accent text-white border-transparent hover:opacity-90'"
            @click="toggleJoin(group)">
            <span x-text="group?.joined ? 'Bergabung' : 'Gabung'"></span>
          </button>

          <!-- POSTING -->
          <a
            :href="group?.joined ? ('/group/' + group.slug + '/post') : '#'"
            @click.prevent="group?.joined ? (location.href='/group/'+group.slug+'/post') : toast('Gabung dulu untuk posting')"
            class="px-4 py-2 rounded-xl font-medium btn-accent text-white shrink-0"
            :class="{'opacity-50 cursor-not-allowed': !group?.joined}">
            Posting
          </a>
        </div>

        <div class="mt-3 grid gap-2 sm:grid-cols-[1fr,auto] sm:items-center">
          <p class="muted text-sm" x-text="group.desc"></p>
          <div class="text-xs muted sm:justify-self-end">
            Dibuat: <span x-text="formatDate(group.created_at)"></span>
            <span class="opacity-60">·</span>
            <span x-text="group.members + ' anggota'"></span>
          </div>
        </div>

        <div class="mt-2 flex flex-wrap gap-2">
          <template x-for="t in group.tags" :key="t">
            <span class="chip px-2 py-1 text-xs rounded-full">#<span x-text="t"></span></span>
          </template>
        </div>
      </div>

      <!-- ====== POSTINGAN GRUP ====== -->
      <div class="px-5 pb-5">
        <h2 class="font-semibold mb-3">Postingan Terbaru</h2>

        <template x-if="!groupPosts.length">
          <div class="glass p-4 rounded-2xl text-sm muted">
            Belum ada postingan di grup ini.
          </div>
        </template>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" x-show="groupPosts.length">

          <template x-for="p in paged" :key="p.id">
            <article
              class="relative glass rounded-2xl overflow-hidden hover:shadow transition card-stack cursor-pointer"
              @click="openComments(p)">

              <!-- Tombol Hapus (hanya post lokal) -->
              <button
                x-show="p.isLocal"
                @click.stop="deletePost(p)"
                class="absolute top-2 right-2 z-10 bg-rose-600/90 text-white text-xs px-2 py-1 rounded hover:bg-rose-700">
                Hapus
              </button>

              <!-- gambar -->
              <template x-if="p.type !== 'text' && p.image">
                <div class="h-40 md:h-48 overflow-hidden">
                  <img :src="p.image" :alt="p.title || 'post image'" class="w-full h-full object-cover">
                </div>
              </template>

              <div class="p-3 flex flex-col gap-2">
                <div class="flex items-center gap-2">
                  <img :src="p.avatar" class="w-8 h-8 rounded-full object-cover" alt="">
                  <div class="min-w-0">
                    <div class="font-medium leading-tight truncate" x-text="p.title || p.author"></div>
                    <div class="text-xs muted truncate" x-text="'@'+p.author"></div>
                  </div>
                </div>

                <template x-if="p.type === 'text'">
                  <div class="text-sm whitespace-pre-wrap line-clamp-5" x-text="p.text"></div>
                </template>

                <div class="flex flex-wrap gap-1">
                  <template x-for="t in (p.tags||[])" :key="t">
                    <span class="chip text-[11px] px-2 py-0.5 rounded-full">#<span x-text="t"></span></span>
                  </template>
                </div>

                <div class="mt-auto text-xs muted flex items-center gap-2">
                  <span x-text="formatDate(p.date)"></span>
                  <span class="opacity-60">·</span>
                  ❤ <span x-text="p.likes || 0"></span>
                </div>
              </div>
            </article>
          </template>
        </div>

        <div class="text-center mt-4" x-show="paged.length < groupPosts.length">
          <button @click="page++" class="px-4 py-2 rounded-xl btn-accent-ghost">Muat lagi</button>
        </div>
      </div>

    </div>
  </template>

  <!-- ====== TIDAK KETEMU GRUP ====== -->
  <template x-if="!group">
    <div class="glass p-6 text-center">Grup tidak ditemukan</div>
  </template>


<!-- ========= MODAL KOMENTAR (fixed height + right pane scroll) ========= -->
<div x-show="cm.open" x-cloak x-transition.opacity
     class="fixed inset-0 z-[60] flex items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/70" @click="closeComments()"></div>

  <!-- max tinggi = 80vh; konten tidak mengubah tinggi -->
  <div class="relative glass rounded-2xl max-w-5xl w-full grid md:grid-cols-[1.2fr,1fr] overflow-hidden">

    <!-- KIRI: gambar dengan tinggi tetap -->
    <div class="bg-black flex items-center justify-center h-[60vh] md:h-[80vh]">
      <img :src="cm.post?.image"
           x-show="cm.post?.image"
           class="max-h-full max-w-full object-contain">
    </div>

    <!-- KANAN: detail + komentar (kolom scroll sendiri) -->
    <div class="p-5 flex flex-col h-[60vh] md:h-[80vh]">
      <!-- header -->
      <div class="flex items-center gap-3 shrink-0">
        <img :src="cm.post?.avatar" class="w-10 h-10 rounded-full object-cover">
        <div class="min-w-0">
          <div class="font-semibold truncate" x-text="cm.post?.title || cm.post?.author"></div>
          <div class="text-xs muted truncate">@<span x-text="cm.post?.author"></span></div>
        </div>
        <button class="ml-auto px-3 py-1 rounded-lg btn-accent-ghost" @click="closeComments()">Tutup</button>
      </div>

      <!-- meta & tags -->
      <div class="mt-3 shrink-0">
        <p class="text-sm whitespace-pre-wrap" x-text="cm.post?.text || cm.post?.desc || ''"></p>
        <div class="flex flex-wrap gap-2 mt-2">
          <template x-for="t in (cm.post?.tags||[])" :key="t">
            <span class="chip text-xs">#<span x-text="t"></span></span>
          </template>
        </div>
        <div class="text-xs muted mt-2">
          <span x-text="formatDate(cm.post?.date)"></span> · ❤ <span x-text="cm.post?.likes||0"></span>
        </div>
      </div>

      <hr class="border-white/10 my-3 shrink-0">

      <!-- daftar komentar: FLEX-1 + scroll -->
      <div class="flex-1 overflow-y-auto pr-1 space-y-3">
        <template x-if="!cm.items.length">
          <div class="muted text-sm">Belum ada komentar.</div>
        </template>
        <template x-for="c in cm.items" :key="c.id">
          <div class="flex items-start gap-3">
            <img :src="c.avatar" class="w-8 h-8 rounded-full object-cover">
            <div class="flex-1 min-w-0">
              <div class="text-sm">
                <span class="font-medium" x-text="c.author"></span>
                <span class="text-xs muted ml-2" x-text="timeAgo(c.at)"></span>
              </div>
              <div class="text-sm whitespace-pre-wrap break-words" x-text="c.text"></div>
            </div>
            <button class="text-xs px-2 py-0.5 rounded bg-rose-600/90 text-white hover:bg-rose-700"
                    @click="deleteComment(c)">Hapus</button>
          </div>
        </template>
      </div>

      <!-- form input: tetap di bawah -->
      <div class="mt-3 flex items-center gap-2 shrink-0">
        <input x-model.trim="cm.newText"
               @keydown.enter.prevent="addComment()"
               class="flex-1 rounded-xl px-3 py-2 border"
               placeholder="Tulis komentar...">
        <button class="px-3 py-2 rounded-xl btn-accent text-white"
                @click="addComment()"
                :disabled="!cm.newText.trim()"
                :class="{'opacity-50 cursor-not-allowed': !cm.newText.trim()}">
          Kirim
        </button>
      </div>
    </div>
  </div>
</div>


</div>
@endsection

@push('scripts')
<style>
  .card-stack{ display:flex; flex-direction:column; }
  .card-stack > .p-3{ flex:1 1 auto; display:flex; flex-direction:column; }
  .line-clamp-5{ display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:5; overflow:hidden; }
</style>

<script>
function GroupShow(){
  const JOIN_KEY = 'hs-joined-groups';
  const CUSTOM_KEYS = ['hs-custom-groups','hs-user-groups','hs-groups-user'];

  const slugify = s => (s||'').toLowerCase().trim()
    .replace(/[^\w\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-');

  const normalizeGroup = g => ({
    id: g.id || ('g-'+Date.now()+Math.random().toString(36).slice(2,7)),
    slug: g.slug || slugify(g.name||'group'),
    name: g.name || 'Tanpa Nama',
    avatar: g.avatar || '/images/demo/groups/default.png',
    cover: g.cover || '',
    owner: g.owner || 'Anonim',
    handle: g.handle || '',
    tags: Array.isArray(g.tags) ? g.tags : (g.tags ? String(g.tags).split(',').map(s=>s.trim()) : []),
    desc: g.desc || '',
    created_at: g.created_at || new Date().toISOString().slice(0,10),
    members: Number(g.members || 0),
  });

  const loadLocalGroups = () => {
    let all = [];
    for (const k of CUSTOM_KEYS) {
      try { all = all.concat(JSON.parse(localStorage.getItem(k) || '[]')); } catch {}
    }
    return all.map(normalizeGroup);
  };

  // ===== KOMENTAR (LOCAL) =====
  const keyComments = (postId) => `hs-gcomments-${postId}`;
  const loadComments = (postId) => {
    try { return JSON.parse(localStorage.getItem(keyComments(postId)) || '[]'); } catch { return []; }
  };
  const saveComments = (postId, items) =>
    localStorage.setItem(keyComments(postId), JSON.stringify(items));

  return {
    group: null,
    groupPosts: [],
    page: 1,
    per: 6,

    // modal komentar state
    cm: { open:false, post:null, items:[], newText:'' },

    get paged(){ return this.groupPosts.slice(0, this.page*this.per); },

    async init(){
      const slug = location.pathname.split('/').filter(Boolean).pop();

      let base = [];
      try { base = await (await fetch('/data/groups.json')).json(); } catch {}
      base = (base||[]).map(normalizeGroup);

      const mine = loadLocalGroups();

      const merged = [...mine, ...base];
      const g = merged.find(x => (x.slug||'') === slug);
      if (!g) { this.group = null; return; }

      const joined = new Set(JSON.parse(localStorage.getItem(JOIN_KEY) || '[]'));
      this.group = { ...g, joined: joined.has(g.slug || g.id) };

      // Posts JSON (read-only)
      let basePosts = [];
      try { basePosts = await (await fetch('/data/group_posts.json')).json(); } catch {}
      basePosts = (basePosts||[])
        .filter(p => p.group === slug)
        .map(p => ({ ...p, type: p.type || (p.text ? 'text' : 'image'), isLocal:false }));

      // Posts lokal (bisa hapus)
      let local = [];
      try { local = JSON.parse(localStorage.getItem('hs-gposts-'+slug) || '[]'); } catch {}
      local = (local||[]).map(p => ({ ...p, type: p.type || (p.text ? 'text' : 'image'), isLocal:true }));

      this.groupPosts = [...local, ...basePosts]
        .sort((a,b)=> new Date(b.date) - new Date(a.date));
    },

    // ===== Komentar: open/close/add/del =====
    openComments(p){
      this.cm.post = p;
      this.cm.items = loadComments(p.id);
      this.cm.newText = '';
      this.cm.open = true;
    },
    closeComments(){
      this.cm.open = false;
      this.cm.post = null;
      this.cm.items = [];
      this.cm.newText = '';
    },
    addComment(){
      const t = (this.cm.newText || '').trim();
      if (!t || !this.cm.post) return;
      const c = {
        id: 'c-' + Date.now() + Math.random().toString(36).slice(2,7),
        text: t,
        author: window.CURRENT_USER?.username || 'guest',
        avatar: window.CURRENT_USER?.avatar || '/images/demo/rina.jpg',
        at: Date.now()
      };
      const postId = this.cm.post.id;
      const arr = loadComments(postId);
      arr.push(c);
      saveComments(postId, arr);
      this.cm.items = arr;
      this.cm.newText = '';
    },
    deleteComment(c){
      if (!this.cm.post) return;
      const postId = this.cm.post.id;
      let arr = loadComments(postId).filter(x => x.id !== c.id);
      saveComments(postId, arr);
      this.cm.items = arr;
    },

    timeAgo(ts){
      if (!ts) return '';
      const s = Math.floor((Date.now()-ts)/1000);
      if (s < 60) return `${s}s`;
      const m = Math.floor(s/60); if (m < 60) return `${m}m`;
      const h = Math.floor(m/60); if (h < 24) return `${h}j`;
      const d = Math.floor(h/24); return `${d}h`;
    },

    // ===== Post delete (lokal saja) =====
    deletePost(p){
      if (!p?.isLocal || !this.group) return;
      if (!confirm(`Hapus posting "${p.title || '(tanpa judul)'}"?`)) return;

      const slug = this.group.slug;
      this.groupPosts = this.groupPosts.filter(x => x.id !== p.id);

      let local = [];
      try { local = JSON.parse(localStorage.getItem('hs-gposts-'+slug) || '[]'); } catch {}
      local = local.filter(x => x.id !== p.id);
      localStorage.setItem('hs-gposts-'+slug, JSON.stringify(local));

      // opsional: bersihkan komentar post itu
      localStorage.removeItem(keyComments(p.id));

      window.toast?.('Posting & komentarnya dihapus');
    },

    toggleJoin(gr){
      const key = gr.slug || gr.id;
      const set = new Set(JSON.parse(localStorage.getItem(JOIN_KEY) || '[]'));
      if (gr.joined) { set.delete(key); gr.joined = false; }
      else { set.add(key); gr.joined = true; }
      localStorage.setItem(JOIN_KEY, JSON.stringify([...set]));
    },

    formatDate(d){
      const dt = new Date(d || Date.now());
      return dt.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
    }
  }
}
</script>
@endpush
