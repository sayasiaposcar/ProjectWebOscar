@extends('layouts.app')
@section('title','Group | HobiSpace')

@section('content')
<section x-data="GroupPage()" x-init="init()" 
         class="grid grid-cols-1 md:grid-cols-[260px,minmax(0,1fr),300px] gap-4 lg:gap-6">

  <!-- Left Sidebar -->
  <aside class="hidden md:block glass rounded-2xl p-3 h-fit sticky top-24">
    <div class="flex items-center gap-2 p-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">
      <img src="/images/demo/rina.jpg" class="w-10 h-10 rounded-full object-cover" alt="">
      <div>
        <div class="font-semibold">Rina Putri</div>
        <div class="text-xs muted">@rina</div>
      </div>
    </div>
    <nav class="mt-2 grid">
      <a href="/explore" class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">🏠 Beranda</a>
      <a href="/groups"  class="px-3 py-2 rounded-xl btn-accent text-white text-center mt-1">👥 Group</a>
      <a href="/submit"  class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5 mt-1">➕ Buat Post</a>
      <a href="/u/rina"  class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5 mt-1">👤 Profil</a>
      <button @click="resetLocal()" class="px-3 py-2 rounded-xl btn-accent-ghost text-left mt-1">♻️ Reset Data Lokal</button>
    </nav>
  </aside>

  <!-- Group List -->
  <div>
    <h1 class="text-xl font-bold mb-3">Daftar Grup</h1>

    <!-- Search -->
    <div class="flex gap-2 mb-3">
      <input x-model="q" class="flex-1 rounded-xl px-3 py-2 border" placeholder="Cari grup...">
      <button class="px-4 py-2 btn-accent rounded-xl text-white">➕ Buat Grup</button>
    </div>

    <!-- Grid Groups -->
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
      <template x-for="g in filtered" :key="g.id">
        <article class="glass rounded-2xl p-4 flex gap-3 hover:shadow transition cursor-pointer"
                 @click="openGroup(g)">
          <img :src="g.avatar" class="w-16 h-16 rounded-xl object-cover" alt="">
          <div class="flex-1 min-w-0">
            <h2 class="font-semibold leading-tight" x-text="g.name"></h2>
            <p class="text-xs muted line-clamp-2" x-text="g.desc"></p>
            <div class="flex gap-2 text-xs mt-1 muted">
              <span>#<span x-text="g.tag"></span></span>
              <span x-text="formatDate(g.created_at)"></span>
              <span x-text="g.members+' anggota'"></span>
            </div>
          </div>
        </article>
      </template>
    </div>
  </div>

  <!-- Right Sidebar -->
  <aside class="hidden lg:block glass rounded-2xl p-3 h-fit sticky top-24">
    <div>
      <div class="font-semibold mb-2">Trending Tags</div>
      <div class="trend-cloud">
        <template x-for="t in trendingTags" :key="t[0]">
          <button @click="q=t[0]" class="pill">
            <span>#<span x-text="t[0]"></span></span>
            <span class="badge" x-text="t[1]"></span>
          </button>
        </template>
      </div>
    </div>
  </aside>

</section>

<!-- Modal detail grup -->
<div x-data="{open:false, group:null}"
     x-on:show-group.window="group=$event.detail;open=true"
     x-show="open" x-cloak
     x-transition.opacity.scale
     class="fixed inset-0 z-50 flex items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/60" @click="open=false"></div>
  <div class="relative glass rounded-2xl max-w-2xl w-full p-6">
    <div class="flex gap-3">
      <img :src="group?.avatar" class="w-20 h-20 rounded-xl object-cover">
      <div>
        <h2 class="font-bold text-xl" x-text="group?.name"></h2>
        <p class="muted text-sm" x-text="group?.desc"></p>
        <div class="text-xs muted mt-1">
          Dibuat: <span x-text="formatDate(group?.created_at)"></span> • 
          <span x-text="group?.members+' anggota'"></span>
        </div>
      </div>
    </div>
    <div class="mt-4">
      <button class="px-4 py-2 btn-accent rounded-xl text-white">Gabung Grup</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function GroupPage(){
  return {
    all:[], q:'',
    async init(){
      this.all = await (await fetch('/data/groups.json')).json();
    },
    get filtered(){
      const q=(this.q||'').toLowerCase();
      return this.all.filter(g =>
        g.name.toLowerCase().includes(q) || g.tag.toLowerCase().includes(q));
    },
    get trendingTags(){
      const m=new Map();
      this.all.forEach(g=>m.set(g.tag,(m.get(g.tag)||0)+1));
      return Array.from(m.entries()).sort((a,b)=>b[1]-a[1]).slice(0,10);
    },
    formatDate(d){
      return new Date(d).toLocaleDateString('id-ID',
        {day:'2-digit',month:'short',year:'numeric'});
    },
    openGroup(g){ window.dispatchEvent(new CustomEvent('show-group',{detail:g})); },
    resetLocal(){ if(confirm('Reset semua data lokal?')){ localStorage.clear(); location.reload(); } }
  }
}
</script>
@endpush
