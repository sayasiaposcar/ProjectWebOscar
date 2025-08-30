@extends('layouts.app')
@section('title','Beranda | HobiSpace')

@section('content')
<section x-data="FeedPage()" x-init="init()" class="grid grid-cols-1 md:grid-cols-[260px,minmax(0,1fr),300px] gap-4 lg:gap-6">
  <!-- Left Sidebar -->
  <aside class="hidden md:block glass rounded-2xl p-3 h-fit sticky top-24">
    <div class="flex items-center gap-2 p-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">
      <img src="/images/demo/rina.jpg" class="w-10 h-10 rounded-full object-cover" alt="">
      <div><div class="font-semibold">Rina Putri</div><div class="text-xs muted">@rina</div></div>
    </div>
    <nav class="mt-2 grid">
      <a href="/explore" class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">🏠 Beranda</a>
      <a href="/group"   class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5 mt-1">👥 Group</a>
      <a href="/submit"  class="px-3 py-2 rounded-xl btn-accent text-white text-center mt-1">➕ Buat Post</a>
      <a href="/u/rina"  class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5 mt-1">👤 Profil</a>
      <button @click="resetLocal()" class="px-3 py-2 rounded-xl btn-accent-ghost text-left mt-1">♻️ Reset Data Lokal</button>
    </nav>
  </aside>

  <!-- Feed -->
  <div>
    <!-- Composer -->
    <button type="button"
        class="flex-1 rounded-xl px-3 py-2 border text-left muted"
        @click="location.href='/submit'">
  Bagikan karya atau gambar...
</button>

    <!-- Filter bar -->
    <div class="flex flex-wrap items-center gap-2 mb-3">
      <div class="flex gap-2 overflow-x-auto no-scrollbar">
        <template x-for="t in tags" :key="t">
          <button @click="toggleTag(t)"
                  @dblclick.stop="followTag(t); toast(followedTags.has(t)?'Unfollow #'+t:'Follow #'+t)"
                  :class="selectedTags.has(t) ? 'bg-cyan-600 text-white' : (followedTags.has(t)?'border-cyan-500 text-cyan-600 chip':'chip')"
                  class="px-3 py-1.5 border rounded-full whitespace-nowrap hover:opacity-90" x-text="'#'+t"></button>
        </template>
      </div>
      <div class="ml-auto flex items-center gap-2">
        <select x-model="sort" class="rounded-xl px-3 py-1.5 border">
          <option value="trending">Trending</option>
          <option value="new">Terbaru</option>
          <option value="old">Terlama</option>
        </select>
        <button @click="mode = (mode==='all' ? 'following' : 'all')" :class="['toggle', mode==='following' ? 'is-active' : '']">
          <span x-text="mode==='all' ? 'Semua' : 'Diikuti'"></span>
        </button>
      </div>
    </div>

    <!-- Feed cards -->
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
      <template x-for="p in paged" :key="p.id">
        <article class="glass rounded-2xl overflow-hidden hover:shadow transition">
          <img :src="p.image" :alt="p.title" class="w-full h-56 object-cover cursor-pointer" @click="openPost(p)">
          <div class="p-3">
            <div class="flex items-center gap-2 mb-1">
              <img :src="p.avatar" class="w-8 h-8 rounded-full object-cover" alt="">
              <div>
                <h3 class="font-semibold leading-tight" x-text="p.title"></h3>
                <p class="text-xs muted" x-text="'@'+p.author"></p>
              </div>
              <button class="ml-auto text-xs px-2 py-1 rounded-lg btn-accent-ghost"
                      @click.stop="toggleFollowUser(p.author)">
                <span x-text="isFollowingUser(p.author)?'Mengikuti':'Ikuti'"></span>
              </button>
            </div>

            <div class="flex flex-wrap gap-1 mb-2">
              <template x-for="t in p.tags"><span class="chip text-[11px] px-2 py-0.5 rounded-full">#<span x-text="t"></span></span></template>
            </div>

            <!-- Aksi -->
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

            <div class="card-date"><span class="date-badge" x-text="formatDate(p.date)"></span></div>
          </div>
        </article>
      </template>
    </div>

    <!-- Load more -->
    <div class="text-center mt-4" x-show="paged.length<filtered.length">
      <button @click="loadMore()" class="px-4 py-2 rounded-xl btn-accent-ghost">Muat lagi</button>
    </div>
  </div>

  <!-- Right Sidebar -->
  <aside class="hidden lg:block glass rounded-2xl p-3 h-fit sticky top-24 space-y-4">
    <div>
      <div class="font-semibold mb-2">Trending Tags</div>
      <div class="trend-cloud">
        <template x-for="t in trendingTags" :key="t[0]">
          <button @click="toggleTag(t[0])" class="pill">
            <span>#<span x-text="t[0]"></span></span>
            <span class="badge" x-text="t[1]"></span>
          </button>
        </template>
      </div>
    </div>

    <div>
      <div class="font-semibold mb-2">Trending Creators</div>
      <template x-for="u in trendingCreators" :key="u.username">
        <div class="flex items-center gap-2 p-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">
          <img :src="u.avatar" class="w-8 h-8 rounded-full object-cover" alt="">
          <div class="min-w-0">
            <div class="text-sm font-medium truncate" x-text="u.name"></div>
            <div class="text-xs muted truncate" x-text="'@'+u.username"></div>
            <div class="text-xs text-cyan-600 dark:text-cyan-300" x-text="u.count+' post'"></div>
          </div>
          <button class="ml-auto text-xs px-2 py-1 rounded-lg btn-accent-ghost" @click="toggleFollowUser(u.username)">
            <span x-text="isFollowingUser(u.username)?'Mengikuti':'Ikuti'"></span>
          </button>
        </div>
      </template>
    </div>

    <div>
      <div class="font-semibold mb-2">Tersimpan</div>
      <div class="space-y-2 max-h-60 overflow-auto no-scrollbar" x-show="saved.size">
        <template x-for="p in savedPosts" :key="p.id">
          <div class="flex items-center gap-2 p-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer" @click="openPost(p)">
            <img :src="p.image" class="w-10 h-10 rounded-lg object-cover" alt="">
            <div class="text-sm leading-tight line-clamp-2" x-text="p.title"></div>
          </div>
        </template>
      </div>
      <div class="text-xs muted" x-show="!saved.size">Belum ada yang disimpan.</div>
    </div>
  </aside>
</section>
@endsection

@push('scripts')
<script>
  function FeedPage(){
    const LS = { get(k,d){try{return JSON.parse(localStorage.getItem(k))??d}catch{return d}}, set(k,v){localStorage.setItem(k,JSON.stringify(v))} };
    const timeAgo = (d)=>{ const diff=(Date.now()-new Date(d+'T00:00:00').getTime())/86400000; if(diff<1) return 'hari ini'; const n=Math.floor(diff); return `${n} hari lalu`; };
    const fmtID = (d)=> new Date(d+'T00:00:00').toLocaleDateString('id-ID',{day:'2-digit',month:'2-digit',year:'2-digit'});

    return {
      all:[], users:[], q:'', tags:[], selectedTags:new Set(),
      liked:new Set(LS.get('hs-liked',[])), saved:new Set(LS.get('hs-saved',[])),
      followedTags:new Set(LS.get('hs-tags',[])), followUsers:new Set(LS.get('hs-follow-users',[])),
      mode:'all', sort:'trending', page:1, per:9,

      formatDate(d){ return `${fmtID(d)} · ${timeAgo(d)}` },

      get trendingCreators(){
        const m=new Map();
        this.filtered.forEach(p=>m.set(p.author, (m.get(p.author)||0)+1));
        return [...m.entries()]
          .map(([username,count])=>{
            const u=this.users.find(x=>x.username===username);
            return u? {...u, count } : null;
          }).filter(Boolean).sort((a,b)=>b.count-a.count).slice(0,5);
      },

      async init(){
        const posts = await (await fetch('/data/posts.json')).json();
        const users = await (await fetch('/data/users.json')).json();
        const custom = LS.get('hs-custom-posts',[]);
        this.all=[...custom,...posts]; this.users=users;
        this.tags=Array.from(new Set(this.all.flatMap(p=>p.tags)));
        const g=document.getElementById('globalSearch'); if(g){ g.addEventListener('input', e=>this.q=e.target.value) }
        window.addEventListener('storage',(e)=>{
          if(e.key==='hs-liked') this.liked = new Set(LS.get('hs-liked',[]));
          if(e.key==='hs-saved') this.saved = new Set(LS.get('hs-saved',[]));
          if(e.key==='hs-follow-users') this.followUsers = new Set(LS.get('hs-follow-users',[]));
          if(e.key==='hs-tags') this.followedTags = new Set(LS.get('hs-tags',[]));
        });
      },

      get filtered(){
        const q=(this.q||'').toLowerCase();
        const onlyFollowing = this.mode==='following';
        return this.all.filter(p=>{
          const byText=!q || p.title.toLowerCase().includes(q) || p.tags.join(' ').toLowerCase().includes(q) || p.author.toLowerCase().includes(q);
          const bySelected=this.selectedTags.size===0 || p.tags.some(t=>this.selectedTags.has(t));
          const byFollowed=this.followedTags.size===0 || p.tags.some(t=>this.followedTags.has(t));
          const byUser=!onlyFollowing || this.followUsers.has(p.author);
          return byText && bySelected && byFollowed && byUser;
        });
      },

      get paged(){
        let arr=[...this.filtered];
        if(this.sort==='new') arr.sort((a,b)=>new Date(b.date)-new Date(a.date));
        else if(this.sort==='old') arr.sort((a,b)=>new Date(a.date)-new Date(b.date));
        else arr.sort((a,b)=>(b.likes+(this.liked.has(b.id)?1:0))-(a.likes+(this.liked.has(a.id)?1:0)));
        return arr.slice(0, this.page*this.per);
      },

      get trendingTags(){
        const m=new Map(); this.filtered.forEach(p=>p.tags.forEach(t=>m.set(t,(m.get(t)||0)+1)));
        return [...m.entries()].sort((a,b)=>b[1]-a[1]).slice(0,12);
      },

      get savedPosts(){ const ids=new Set(this.saved); return this.all.filter(p=>ids.has(p.id)); },

      toggleTag(t){ this.selectedTags.has(t)?this.selectedTags.delete(t):this.selectedTags.add(t); },
      followTag(t){ this.followedTags.has(t)?this.followedTags.delete(t):this.followedTags.add(t); LS.set('hs-tags',[...this.followedTags]); },
      openPost(p){ window.dispatchEvent(new CustomEvent('show-post',{detail:p})); },
      toggleLike(id){ this.liked.has(id)?this.liked.delete(id):this.liked.add(id); LS.set('hs-liked',[...this.liked]); },
      toggleSave(id){ this.saved.has(id)?this.saved.delete(id):this.saved.add(id); LS.set('hs-saved',[...this.saved]); },
      likeCount(p){ return p.likes + (this.liked.has(p.id)?1:0); },
      share(p){ navigator.clipboard.writeText(location.origin+'/explore#'+p.id); toast('Tautan disalin'); },
      loadMore(){ this.page++; },
      resetLocal(){ if(confirm('Reset semua data lokal?')){ localStorage.clear(); location.reload(); } },

      toggleFollowUser(u){ this.followUsers.has(u)?this.followUsers.delete(u):this.followUsers.add(u); LS.set('hs-follow-users',[...this.followUsers]); },
      isFollowingUser(u){ return this.followUsers.has(u); }
    }
  }
</script>
@endpush
