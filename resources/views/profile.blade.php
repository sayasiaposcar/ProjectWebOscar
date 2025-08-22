@extends('layouts.app')
@section('title','Profil | HobiSpace')

@section('content')
<section x-data="ProfilePage()" x-init="init()" class="grid grid-cols-1 lg:grid-cols-[1fr,320px] gap-4">
  <div>
    <!-- Header profil aman -->
    <div class="glass rounded-2xl overflow-hidden">
      <div class="h-32 sm:h-40 bg-gradient-to-r from-cyan-600/25 to-transparent"></div>
      <div class="p-4 sm:p-6 -mt-10 sm:-mt-12 flex items-end gap-4">
        <img :src="user?.avatar" alt=""
             class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover ring-4 ring-white/80 dark:ring-black/40">
        <div class="min-w-0">
          <h1 class="text-2xl font-bold truncate" x-text="user?.name || 'Pengguna'"></h1>
          <p class="muted truncate" x-text="user ? '@'+user.username : ''"></p>
          <p class="mt-1 line-clamp-2" x-text="user?.bio"></p>
        </div>
        <button class="ml-auto px-3 py-1.5 rounded-xl btn-accent-ghost" @click="toggleFollowUser(user?.username)" x-show="user">
          <span x-text="isFollowingUser(user?.username)?'Mengikuti':'Ikuti'"></span>
        </button>
      </div>
    </div>

    <!-- Tab karya/likes/saved -->
    <div x-data="{tab:'works'}" class="mt-4 sm:mt-6">
      <div class="glass px-2 py-2 rounded-2xl inline-flex gap-1">
        <button @click="tab='works'" :class="tab==='works'?'btn-accent text-white':''" class="px-4 py-1.5 rounded-xl">Karya</button>
        <button @click="tab='likes'" :class="tab==='likes'?'btn-accent text-white':''" class="px-4 py-1.5 rounded-xl">Disukai</button>
        <button @click="tab='saved'" :class="tab==='saved'?'btn-accent text-white':''" class="px-4 py-1.5 rounded-xl">Disimpan</button>
      </div>

      <div class="mt-4 grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
        <template x-for="p in (tab==='works'?works:(tab==='likes'?likes:savedPosts))" :key="p.id">
          <figure class="glass rounded-2xl overflow-hidden cursor-pointer" @click="window.dispatchEvent(new CustomEvent('show-post',{detail:p}))">
            <img :src="p.image" :alt="p.title" class="w-full h-52 object-cover">
            <figcaption class="p-3 text-sm">
              <div class="font-semibold truncate" x-text="p.title"></div>
              <div class="text-xs muted" x-text="new Date(p.date+'T00:00:00').toLocaleDateString('id-ID')"></div>
            </figcaption>
          </figure>
        </template>
      </div>
    </div>
  </div>

  <!-- Sidebar kanan -->
  <aside class="glass rounded-2xl p-3 h-fit">
    <div class="font-semibold mb-2">Tautan</div>
    <div class="flex flex-wrap gap-2">
      <template x-for="(v,k) in (user?.links||{})">
        <a :href="v" class="chip px-3 py-1.5 rounded-xl text-sm" x-text="k"></a>
      </template>
      <div class="text-xs muted" x-show="!(user?.links) || !Object.keys(user?.links||{}).length">—</div>
    </div>

    <div class="mt-4">
      <div class="font-semibold mb-2">Ikuti juga</div>
      <template x-for="u in recommend" :key="u.username">
        <div class="flex items-center gap-2 p-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">
          <img :src="u.avatar" class="w-8 h-8 rounded-full object-cover" alt="">
          <div class="min-w-0">
            <div class="text-sm font-medium truncate" x-text="u.name"></div>
            <div class="text-xs muted truncate" x-text="'@'+u.username"></div>
          </div>
          <button class="ml-auto text-xs px-2 py-1 rounded-lg btn-accent-ghost"
                  @click="toggleFollowUser(u.username)">
            <span x-text="isFollowingUser(u.username)?'Mengikuti':'Ikuti'"></span>
          </button>
        </div>
      </template>
    </div>
  </aside>
</section>
@endsection

@push('scripts')
<script>
  function ProfilePage(){
    const LS={get(k,d){try{return JSON.parse(localStorage.getItem(k))??d}catch{return d}},set(k,v){localStorage.setItem(k,JSON.stringify(v))}};
    return {
      user:null, works:[], likes:[], recommend:[],
      followUsers:new Set(LS.get('hs-follow-users',[])),
      savedIds:new Set(LS.get('hs-saved',[])), allPosts:[],

      async init(){
        try{
          const users = await (await fetch('/data/users.json')).json();
          const posts = await (await fetch('/data/posts.json')).json();
          const uname = decodeURIComponent(window.location.pathname.split('/').pop());
          this.user = users.find(u => u.username===uname) || users[0] || null;
          if(!this.user){ return; }
          this.allPosts = posts;
          this.works = posts.filter(p => (this.user.works||[]).includes(p.id));
          this.likes = posts.filter(p => (this.user.likes||[]).includes(p.id));
          this.recommend = users.filter(u=>u.username!==this.user.username).slice(0,5);

          // sync jika ada perubahan di tab lain
          window.addEventListener('storage',(e)=>{
            if(e.key==='hs-saved') this.savedIds=new Set(LS.get('hs-saved',[]));
            if(e.key==='hs-follow-users') this.followUsers=new Set(LS.get('hs-follow-users',[]));
          });
        }catch(e){ console.error(e); }
      },

      get savedPosts(){ const s=new Set(this.savedIds); return this.allPosts.filter(p=>s.has(p.id)); },

      toggleFollowUser(u){ if(!u) return; this.followUsers.has(u)?this.followUsers.delete(u):this.followUsers.add(u); LS.set('hs-follow-users',[...this.followUsers]); },
      isFollowingUser(u){ return this.followUsers.has(u); }
    }
  }
</script>
@endpush
