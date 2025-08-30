@extends('layouts.app')
@section('title', ($__env->yieldContent('profile_title') ?: 'Profil') . ' | HobiSpace')

@section('content')
<section x-data="ProfilePage()" x-init="init()" class="grid grid-cols-1 lg:grid-cols-[300px,minmax(0,1fr),360px] gap-4 lg:gap-6">

  <!-- LEFT: user card -->
  <aside class="hidden lg:block glass rounded-2xl p-4 h-fit sticky top-24" x-show="user">
    <div class="flex items-center gap-3">
      <div class="relative">
        <img :src="user?.avatar || '/images/demo/rina.jpg'" class="w-14 h-14 rounded-xl object-cover" alt="avatar">
        <button x-show="isSelf" class="absolute -bottom-1 -right-1 text-[11px] px-1.5 py-0.5 rounded bg-black/70 text-white" title="Ganti avatar" @click="$refs.avatarInput.click()">✏️</button>
      </div>
      <div class="min-w-0">
        <div class="font-semibold truncate" x-text="user?.name"></div>
        <div class="text-xs muted truncate">@<span x-text="user?.username"></span></div>
      </div>
    </div>

    <div class="mt-3 text-sm grid grid-cols-3 gap-2 text-center">
      <div class="glass rounded-xl p-2"><div class="text-xs muted">Karya</div><div class="font-bold" x-text="works.length"></div></div>
      <div class="glass rounded-xl p-2"><div class="text-xs muted">Suka</div><div class="font-bold" x-text="likes.length"></div></div>
      <div class="glass rounded-xl p-2"><div class="text-xs muted">Tersimpan</div><div class="font-bold" x-text="savedPosts.length"></div></div>
    </div>

    <div class="mt-3" x-show="user?.bio">
      <div class="font-semibold mb-1">Tentang</div>
      <p class="muted text-sm" x-text="user.bio"></p>
    </div>

    <input type="file" accept="image/*" class="sr-only" x-ref="avatarInput" @change="onAvatarFile">
  </aside>

  <!-- MAIN -->
  <div>
    <!-- HERO -->
    <div class="glass rounded-2xl overflow-hidden">
      <div class="relative h-36 sm:h-44 bg-gradient-to-r from-cyan-600/30 to-blue-600/30">
        <img x-show="user?.cover" :src="user?.cover" class="absolute inset-0 w-full h-full object-cover" alt="cover">
        <div class="absolute inset-0 bg-black/20" x-show="user?.cover"></div>
      </div>

      <div class="p-4 sm:p-5">
        <div class="flex items-center gap-3">
          <img :src="user?.avatar || '/images/demo/rina.jpg'" class="w-16 h-16 rounded-xl object-cover -mt-12 border-4 border-white dark:border-slate-900" alt="avatar">
          <div class="min-w-0">
            <h1 class="font-bold text-xl leading-tight truncate" x-text="user?.name || 'Pengguna'"></h1>
            <div class="text-sm muted truncate">@<span x-text="user?.username"></span></div>
          </div>
          <div class="ml-auto flex items-center gap-2">
            <button type="button" class="px-3 py-1.5 rounded-xl btn-accent-ghost" @click="shareProfile()">Bagikan</button>

            <!-- EDIT: hanya untuk diri sendiri -->
            <template x-if="isSelf">
              <button type="button" class="px-3 py-1.5 rounded-xl btn-accent text-white" @click="openEdit()">Edit Profil</button>
            </template>

            <!-- Follow: sembunyikan saat self -->
            <template x-if="user && !isSelf">
              <button type="button" class="px-3 py-1.5 rounded-xl btn-accent text-white" @click="toggleFollowUser(user.username)">
                <span x-text="isFollowingUser(user.username)?'Mengikuti':'Ikuti'"></span>
              </button>
            </template>
          </div>
        </div>

        <p class="text-sm mt-2" x-show="user?.bio" x-text="user?.bio"></p>

        <div class="mt-3 flex flex-wrap gap-3 text-sm">
          <div class="date-badge">Bergabung <span x-text="formatDate(user?.joined_at)"></span></div>
          <template x-for="t in (user?.skills||[])" :key="t"><span class="chip px-2 py-1 text-xs rounded-full">#<span x-text="t"></span></span></template>
        </div>
      </div>
    </div>

    <!-- TABS -->
    <div class="mt-4 flex items-center gap-2 overflow-x-auto no-scrollbar">
      <button class="px-3 py-1.5 rounded-xl" :class="tab==='works'?'btn-accent text-white':'glass'" @click="tab='works'">Karya</button>
      <button class="px-3 py-1.5 rounded-xl" :class="tab==='likes'?'btn-accent text-white':'glass'" @click="tab='likes'">Disukai</button>
      <button class="px-3 py-1.5 rounded-xl" :class="tab==='saved'?'btn-accent text-white':'glass'" @click="tab='saved'">Tersimpan</button>
      <button class="px-3 py-1.5 rounded-xl" :class="tab==='about'?'btn-accent text-white':'glass'" @click="tab='about'">Tentang</button>
    </div>

    <!-- GRID POSTS (3 tabs) -->
    <div class="mt-3 grid sm:grid-cols-2 xl:grid-cols-3 gap-3" x-show="tab!=='about'">
      <template x-if="tab==='works'">
        <template x-for="p in works" :key="p.id">
          <article class="glass rounded-2xl overflow-hidden hover:shadow transition">
            <img :src="p.image" :alt="p.title" class="w-full h-56 object-cover">
            <div class="p-3">
              <div class="font-semibold leading-tight line-clamp-2" x-text="p.title"></div>
              <div class="flex flex-wrap gap-1 my-2">
                <template x-for="t in (p.tags||[])" :key="t"><span class="chip text-[11px] px-2 py-0.5 rounded-full">#<span x-text="t"></span></span></template>
              </div>
              <div class="icon-row">
                <button class="icon-btn" :class="liked.has(p.id)?'is-active like':''" :title="likeCount(p)+' suka'" @click="toggleLike(p.id)">
                  <svg viewBox="0 0 24 24" class="icon"><path d="M12 21s-7-4.35-9.33-8.12A5.5 5.5 0 0 1 12 6.09a5.5 5.5 0 0 1 9.33 6.79C19 16.65 12 21 12 21z"/></svg>
                  <span class="mini-badge" x-text="likeCount(p)"></span>
                </button>
                <button class="icon-btn" :class="saved.has(p.id)?'is-active save':''" :title="saved.has(p.id)?'Tersimpan':'Simpan'" @click="toggleSave(p.id)">
                  <svg viewBox="0 0 24 24" class="icon"><path d="M14 2l-1 7 3 3-4 1-6 7 1-6-4-4 7-1z"/></svg>
                </button>
                <button class="icon-btn" title="Bagikan" @click="share(p)">
                  <svg viewBox="0 0 24 24" class="icon"><path d="M14 9l-4 2 4 2m5-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM5 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm13 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
                </button>
              </div>
              <div class="card-date mt-1"><span class="date-badge" x-text="formatDate(p.date)"></span></div>
            </div>
          </article>
        </template>
      </template>

      <template x-if="tab==='likes'">
        <template x-for="p in likes" :key="p.id">
          <article class="glass rounded-2xl overflow-hidden hover:shadow transition">
            <img :src="p.image" :alt="p.title" class="w-full h-56 object-cover">
            <div class="p-3">
              <div class="font-semibold leading-tight line-clamp-2" x-text="p.title"></div>
              <div class="flex flex-wrap gap-1 my-2">
                <template x-for="t in (p.tags||[])" :key="t"><span class="chip text-[11px] px-2 py-0.5 rounded-full">#<span x-text="t"></span></span></template>
              </div>
              <div class="icon-row">
                <button class="icon-btn" :class="liked.has(p.id)?'is-active like':''" :title="likeCount(p)+' suka'" @click="toggleLike(p.id)">
                  <svg viewBox="0 0 24 24" class="icon"><path d="M12 21s-7-4.35-9.33-8.12A5.5 5.5 0 0 1 12 6.09a5.5 5.5 0 0 1 9.33 6.79C19 16.65 12 21 12 21z"/></svg>
                  <span class="mini-badge" x-text="likeCount(p)"></span>
                </button>
                <button class="icon-btn" :class="saved.has(p.id)?'is-active save':''" :title="saved.has(p.id)?'Tersimpan':'Simpan'" @click="toggleSave(p.id)">
                  <svg viewBox="0 0 24 24" class="icon"><path d="M14 2l-1 7 3 3-4 1-6 7 1-6-4-4 7-1z"/></svg>
                </button>
                <button class="icon-btn" title="Bagikan" @click="share(p)">
                  <svg viewBox="0 0 24 24" class="icon"><path d="M14 9l-4 2 4 2m5-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM5 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm13 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
                </button>
              </div>
              <div class="card-date mt-1"><span class="date-badge" x-text="formatDate(p.date)"></span></div>
            </div>
          </article>
        </template>
      </template>

      <template x-if="tab==='saved'">
        <template x-for="p in savedPosts" :key="p.id">
          <article class="glass rounded-2xl overflow-hidden hover:shadow transition">
            <img :src="p.image" :alt="p.title" class="w-full h-56 object-cover">
            <div class="p-3">
              <div class="font-semibold leading-tight line-clamp-2" x-text="p.title"></div>
              <div class="flex flex-wrap gap-1 my-2">
                <template x-for="t in (p.tags||[])" :key="t"><span class="chip text-[11px] px-2 py-0.5 rounded-full">#<span x-text="t"></span></span></template>
              </div>
              <div class="icon-row">
                <button class="icon-btn" :class="liked.has(p.id)?'is-active like':''" :title="likeCount(p)+' suka'" @click="toggleLike(p.id)">
                  <svg viewBox="0 0 24 24" class="icon"><path d="M12 21s-7-4.35-9.33-8.12A5.5 5.5 0 0 1 12 6.09a5.5 5.5 0 0 1 9.33 6.79C19 16.65 12 21 12 21z"/></svg>
                  <span class="mini-badge" x-text="likeCount(p)"></span>
                </button>
                <button class="icon-btn" :class="saved.has(p.id)?'is-active save':''" :title="saved.has(p.id)?'Tersimpan':'Simpan'" @click="toggleSave(p.id)">
                  <svg viewBox="0 0 24 24" class="icon"><path d="M14 2l-1 7 3 3-4 1-6 7 1-6-4-4 7-1z"/></svg>
                </button>
                <button class="icon-btn" title="Bagikan" @click="share(p)">
                  <svg viewBox="0 0 24 24" class="icon"><path d="M14 9l-4 2 4 2m5-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM5 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm13 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
                </button>
              </div>
              <div class="card-date mt-1"><span class="date-badge" x-text="formatDate(p.date)"></span></div>
            </div>
          </article>
        </template>
      </template>
    </div>

    <!-- ABOUT -->
    <div class="mt-3 glass rounded-2xl p-4" x-show="tab==='about'">
      <div class="grid md:grid-cols-2 gap-3">
        <div><div class="font-semibold mb-1">Bio</div><p class="muted" x-text="user?.bio || 'Belum ada bio.'"></p></div>
        <div>
          <div class="font-semibold mb-1">Info</div>
          <ul class="text-sm space-y-1">
            <li x-show="user?.website">🔗 <a :href="user.website" class="underline hover:opacity-80" x-text="user.website" target="_blank" rel="noreferrer"></a></li>
            <li x-show="user?.location">📍 <span x-text="user.location"></span></li>
            <li>🗓️ Bergabung <span x-text="formatDate(user?.joined_at)"></span></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="glass rounded-2xl p-4 text-sm muted mt-3" x-show="tab==='works' && !works.length">Belum ada karya.</div>
    <div class="glass rounded-2xl p-4 text-sm muted mt-3" x-show="tab==='likes' && !likes.length">Belum ada yang disukai.</div>
    <div class="glass rounded-2xl p-4 text-sm muted mt-3" x-show="tab==='saved' && !savedPosts.length">Belum ada yang disimpan.</div>
    <div class="glass rounded-2xl p-6 text-center" x-show="!user">Profil tidak ditemukan.</div>
  </div>

  <!-- RIGHT: DM Sidebar (list) + rekomendasi -->
  <aside class="glass rounded-2xl h-fit sticky top-24 overflow-hidden">
    <div class="px-4 py-3 border-b border-white/10 flex items-center gap-2">
      <div class="font-semibold">Pesan</div>
      <input class="ml-auto bg-transparent border rounded-lg px-3 py-1.5 text-sm w-40" placeholder="Cari…" x-model.trim="dm.q">
      <button class="px-2.5 py-1.5 rounded-lg btn-accent-ghost text-sm" @click="dm.showArchived=!dm.showArchived">Arsip (<span x-text="dm.showArchived?'on':'off'"></span>)</button>
    </div>
    <div class="max-h-[44vh] overflow-auto divide-y divide-white/5">
      <template x-for="t in dm.filtered()" :key="t.id">
        <a href="/messages" class="block px-4 py-3 hover:bg-black/5 dark:hover:bg:white/5">
          <div class="flex items-center gap-3">
            <img :src="dmAvatar(t)" class="w-9 h-9 rounded-full object-cover" alt="">
            <div class="min-w-0">
              <div class="text-sm font-medium truncate" x-text="dmName(t)"></div>
              <div class="text-xs muted truncate" x-text="t.lastMsg||'Mulai obrolan'"></div>
            </div>
            <div class="ml-auto text-right">
              <div class="text-[11px]" x-text="t.lastAt?new Date(t.lastAt).toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}):''"></div>
              <span class="badge" x-show="t.unread>0" x-text="t.unread"></span>
            </div>
          </div>
        </a>
      </template>
      <div x-show="dm.filtered().length===0" class="p-6 text-sm muted text-center">Belum ada percakapan.</div>
    </div>

    <!-- tombol IKUTI & PESAN (hanya jika bukan diri sendiri) -->
    <div class="px-4 py-3 border-t border-white/10 flex items-center gap-2" x-show="!isSelf">
      <button type="button" class="px-3 py-1.5 rounded-xl btn-accent text-white"
              @click="toggleFollowUser(user.username)">
        <span x-text="isFollowingUser(user.username)?'Mengikuti':'Ikuti'"></span>
      </button>
      <a :href="'/messages?to='+encodeURIComponent(user.username)" class="px-3 py-1.5 rounded-xl btn-accent-ghost">✉️ Pesan</a>
    </div>

    <!-- Rekomendasi follow di area pesan -->
    <div class="border-t border-white/10 p-3">
      <div class="text-sm font-semibold mb-2">Orang untuk diikuti</div>
      <div class="space-y-2 max-h-52 overflow-auto">
        <template x-for="u in recommend.slice(0,5)" :key="u.username">
          <div class="flex items-center gap-2">
            <img :src="u.avatar" class="w-9 h-9 rounded-full object-cover" alt="">
            <div class="min-w-0">
              <div class="text-sm font-medium truncate" x-text="u.name"></div>
              <div class="text-xs muted truncate">@<span x-text="u.username"></span></div>
            </div>
            <button class="ml-auto text-xs px-2 py-1 rounded-lg btn-accent-ghost" @click="toggleFollowUser(u.username)">
              <span x-text="isFollowingUser(u.username)?'Mengikuti':'Ikuti'"></span>
            </button>
          </div>
        </template>
      </div>
      <div class="mt-2 text-right">
        <a href="/messages" class="text-xs underline hover:opacity-80">Buka semua pesan →</a>
      </div>
    </div>
  </aside>

  <!-- ======= EDIT PROFILE MODAL (LOCAL) ======= -->
  <div x-show="editOpen" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60" @click="closeEdit()"></div>
    <div class="relative w-full max-w-lg glass rounded-2xl p-4">
      <div class="flex items-center gap-2 mb-3">
        <div class="font-semibold">Edit Profil</div>
        <button class="ml-auto px-2 py-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5" @click="closeEdit()">✕</button>
      </div>

      <div class="space-y-3">
        <!-- Foto profil -->
        <div class="flex items-center gap-3">
          <img :src="form.avatar || user?.avatar || '/images/demo/rina.jpg'" class="w-16 h-16 rounded-xl object-cover border" alt="avatar preview">
          <div>
            <div class="text-sm font-medium mb-1">Ganti Foto Profil</div>
            <input type="file" accept="image/*" @change="onAvatarFileModal" class="text-sm">
          </div>
        </div>

        <!-- Nama -->
        <label class="block text-sm">Nama
          <input x-model.trim="form.name" class="mt-1 w-full rounded-xl px-3 py-2 border">
        </label>

        <!-- Bio -->
        <label class="block text-sm">Bio
          <textarea x-model.trim="form.bio" class="mt-1 w-full rounded-xl px-3 py-2 border min-h-24"></textarea>
        </label>

        <div class="flex items-center gap-2 pt-1">
          <button class="px-4 py-2 rounded-xl btn-accent text-white" @click="saveEdit()" :disabled="savingEdit">
            <span x-text="savingEdit ? 'Menyimpan…' : 'Simpan'"></span>
          </button>
          <button class="px-4 py-2 rounded-xl btn-accent-ghost" @click="closeEdit()" :disabled="savingEdit">Batal</button>
        </div>
      </div>
    </div>
  </div>
  <!-- ======= /EDIT PROFILE MODAL ======= -->

</section>
@endsection

@push('scripts')
<style>.line-clamp-2{display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}</style>
<script>
function ProfilePage(){
  const LS={get(k,d){try{return JSON.parse(localStorage.getItem(k))??d}catch{return d}},set(k,v){localStorage.setItem(k,JSON.stringify(v))}};
  const fmtID = (d)=> d ? new Date(d).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}) : '-';
  const DEFAULT_AV='/images/demo/rina.jpg';
  const TKEY='hs-dm-threads';

  const toDataUrl = (f)=>new Promise((res,rej)=>{ const r=new FileReader(); r.onload=()=>res(r.result); r.onerror=rej; r.readAsDataURL(f); });

  return {
    tab:'works',
    users:[], user:null, allPosts:[], works:[], likes:[], recommend:[],
    liked:new Set(LS.get('hs-liked',[])), saved:new Set(LS.get('hs-saved',[])),
    followUsers:new Set(LS.get('hs-follow-users',[])),
    me:(window.CURRENT_USER && window.CURRENT_USER.username) || 'rina',
    get isSelf(){ return this.user && this.user.username===this.me },

    // === Edit Profile (local only) ===
    editOpen:false,
    savingEdit:false,
    form:{ name:'', bio:'', avatar:'' },

    // mini DM list
    dm:{ threads:[], q:'', showArchived:false,
      load(){ this.threads = Array.isArray(LS.get(TKEY,[]))?LS.get(TKEY,[]):[]; },
      filtered(){
        let a = Array.isArray(this.threads)?this.threads:[];
        if(!this.showArchived) a = a.filter(t=>!t.archived);
        const q=(this.q||'').toLowerCase();
        if(q) a = a.filter(t=> (this.$data.dmName(t)||'').toLowerCase().includes(q) || (this.$data.dmOther(t)||'').toLowerCase().includes(q));
        return a.sort((x,y)=>(y.lastAt||0)-(x.lastAt||0));
      }
    },

    async init(){
      try{
        const [users, posts] = await Promise.all([
          fetch('/data/users.json').then(r=>r.json()),
          fetch('/data/posts.json').then(r=>r.json())
        ]);
        this.users = Array.isArray(users)?users:[];
        const uname = decodeURIComponent((window.location.pathname.split('/').filter(Boolean).pop())||'');
        const baseUser = this.users.find(u => u.username===uname) || null;

        // terapkan override global dari UserMeta
        const ov = window.UserMeta?.get(uname) || {};
        this.user = baseUser ? {...baseUser, ...ov} : null;

        this.allPosts = posts;
        if(this.user){
          this.works = posts.filter(p => (this.user.works||[]).includes(p.id));
          this.likes = posts.filter(p => (this.user.likes||[]).includes(p.id));
          this.recommend = this.users.filter(u=>u.username!==this.user.username).slice(0,6);
          document.title = `${this.user.name} (@${this.user.username}) | HobiSpace`;
        }

        this.dm.load();

        // sinkron jika ada perubahan meta user (avatar/bio/cover) dari halaman lain
        window.addEventListener('user:meta-updated', ()=>{
          if(!this.user) return;
          const ovNow = window.UserMeta?.get(this.user.username) || {};
          this.user = {...this.user, ...ovNow};
          this.dm.load(); // refresh list agar avatar ikut baru
        });

        // cross-tab sync
        window.addEventListener('storage',(e)=>{
          if(e.key==='hs-saved') this.saved=new Set(LS.get('hs-saved',[]));
          if(e.key==='hs-liked') this.liked=new Set(LS.get('hs-liked',[]));
          if(e.key==='hs-follow-users') this.followUsers=new Set(LS.get('hs-follow-users',[]));
          if(e.key===TKEY || (e.key||'').startsWith('hs-dm-msgs-')) this.dm.load();
          if(e.key==='hs-user-overrides' && this.user){
            const ovChange = window.UserMeta?.get(this.user.username) || {};
            this.user = {...this.user, ...ovChange};
          }
        });
      }catch(e){ console.error(e) }
    },

    // helpers
    formatDate(d){ return fmtID(d) },
    get savedPosts(){ const s=new Set(this.saved); return this.allPosts.filter(p=>s.has(p.id)) },
    toggleFollowUser(u){ if(!u || u===this.me) return; this.followUsers.has(u)?this.followUsers.delete(u):this.followUsers.add(u); LS.set('hs-follow-users',[...this.followUsers]) },
    isFollowingUser(u){ return this.followUsers.has(u) },
    likeCount(p){ return (p?.likes||0)+(this.liked.has(p.id)?1:0) },
    toggleLike(id){ this.liked.has(id)?this.liked.delete(id):this.liked.add(id); LS.set('hs-liked',[...this.liked]) },
    toggleSave(id){ this.saved.has(id)?this.saved.delete(id):this.saved.add(id); LS.set('hs-saved',[...this.saved]) },
    share(p){ navigator.clipboard.writeText(location.origin+'/explore#'+(p?.id||'')); window.toast?.('Tautan disalin') },
    shareProfile(){ navigator.clipboard.writeText(location.origin + window.location.pathname); window.toast?.('Tautan profil disalin') },

    // DM list helpers (pakai users + UserMeta)
    dmOther(t){ return t.a===this.me ? t.b : t.a },
    dmUser(uname){
      const base = this.users.find(u=>u.username===uname) || {username:uname,name:uname,avatar:DEFAULT_AV};
      const ov = window.UserMeta?.get(uname) || {};
      return {...base, ...ov};
    },
    dmName(t){ const other=this.dmOther(t); return this.dmUser(other).name },
    dmAvatar(t){ const other=this.dmOther(t); return this.dmUser(other).avatar || DEFAULT_AV },

    // Upload avatar langsung dari kartu kiri
    async onAvatarFile(e){
      const f=e.target.files?.[0]; e.target.value='';
      if(!f || !this.isSelf) return;
      try{
        const dataUrl = await toDataUrl(f);
        window.UserMeta?.upsert(this.user.username, { avatar: dataUrl });
        this.user = {...this.user, avatar: dataUrl};
        window.toast?.('Avatar diperbarui');
      }catch{ window.toast?.('Gagal memuat gambar') }
    },

    // ===== Edit modal logic =====
    openEdit(){
      if(!this.isSelf || !this.user) return;
      this.form = {
        name: this.user.name || '',
        bio: this.user.bio || '',
        avatar: this.user.avatar || ''
      };
      this.editOpen = true;
    },
    closeEdit(){ this.editOpen=false; },

    async onAvatarFileModal(e){
      const f = e.target.files?.[0]; e.target.value='';
      if(!f) return;
      try{
        const dataUrl = await toDataUrl(f);
        this.form.avatar = dataUrl;
      }catch{ window.toast?.('Gagal memuat foto profil'); }
    },

    saveEdit(){
      if(!this.isSelf || !this.user || this.savingEdit) return;
      this.savingEdit = true;

      const patch = {
        name: (this.form.name||'').trim(),
        bio: (this.form.bio||'').trim(),
        avatar: this.form.avatar || this.user.avatar || ''
      };

      try{
        if(window.UserMeta?.upsert){
          window.UserMeta.upsert(this.user.username, patch);
        }else{
          let map={}; try{ map=JSON.parse(localStorage.getItem('hs-user-overrides')||'{}'); }catch{}
          map[this.user.username] = { ...(map[this.user.username]||{}), ...patch };
          localStorage.setItem('hs-user-overrides', JSON.stringify(map));
          window.dispatchEvent(new Event('user:meta-updated'));
        }

        this.user = { ...this.user, ...patch };
        window.toast?.('Profil diperbarui');
        this.editOpen = false;
      }finally{
        this.savingEdit = false;
      }
    }
  }
}
</script>
@endpush
