@extends('layouts.app')
@section('hideBottomNav', true) {{-- HILANGKAN bottom-nav di landing --}}
@section('title','HobiSpace – Komunitas Hobi')

@section('content')
<section x-data="LandingPage()" x-init="init()" class="space-y-10">

  <!-- HERO -->
  <div class="text-center space-y-5">
    <h1 class="text-4xl md:text-6xl font-extrabold leading-tight tracking-tight">
      Jelajahi & bagikan karya
      <span class="bg-clip-text text-transparent"
            style="background-image: linear-gradient(90deg,#06b6d4 0%, #0ea5e9 30%, #8b5cf6 90%);">
        komunitas hobi
      </span>
    </h1>

    <p class="muted max-w-2xl mx-auto">
      Fotografi, ilustrasi, kitbash, musik, hingga DIY. Semuanya ringan & cepat — cukup browser-mu saja.
    </p>

    <div class="flex items-center justify-center gap-3">
      <a href="/explore" class="px-5 py-3 rounded-2xl btn-accent text-white shadow hover:opacity-95 ring-app">🚀 Mulai Jelajah</a>
      <a href="/submit" class="px-5 py-3 rounded-2xl glass hover:opacity-95 ring-app">➕ Buat Post</a>
    </div>

    <!-- Mini stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-3xl mx-auto mt-4">
      <div class="glass rounded-2xl p-3"><div class="text-xs muted">Post</div><div class="text-2xl font-extrabold" x-text="stats.posts"></div></div>
      <div class="glass rounded-2xl p-3"><div class="text-xs muted">Creator</div><div class="text-2xl font-extrabold" x-text="stats.creators"></div></div>
      <div class="glass rounded-2xl p-3"><div class="text-xs muted">Tag Aktif</div><div class="text-2xl font-extrabold" x-text="stats.tags"></div></div>
      <div class="glass rounded-2xl p-3"><div class="text-xs muted">Disukai</div><div class="text-2xl font-extrabold" x-text="stats.likes"></div></div>
    </div>
  </div>

  <!-- MARQUEE -->
  <div class="glass rounded-2xl overflow-hidden">
    <div class="py-2 text-sm font-medium flex gap-8 whitespace-nowrap animate-[marquee_18s_linear_infinite] px-4" x-ref="marquee">
      <template x-for="(t, i) in marquee" :key="i">
        <span class="pill">#<span x-text="t"></span></span>
      </template>
      <style>@keyframes marquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}</style>
    </div>
  </div>

  <!-- FITUR -->
  <section>
    <h2 class="text-xl font-bold mb-3 text-center">Kenapa HobiSpace?</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="glass rounded-2xl p-4">
        <div class="text-2xl mb-1">⚡</div>
        <div class="font-semibold">Cepat & Ringan</div>
        <p class="muted text-sm">Semua berjalan di browser: JSON + localStorage. Cocok buat demo lomba.</p>
      </div>

      <div class="glass rounded-2xl p-4">
        <div class="text-2xl mb-1">📱</div>
        <div class="font-semibold">Responsif</div>
        <p class="muted text-sm">Tampilan rapi di HP, tablet, hingga desktop — otomatis menyesuaikan.</p>
      </div>

      <div class="glass rounded-2xl p-4">
        <div class="text-2xl mb-1">🌙</div>
        <div class="font-semibold">Dark/Light</div>
        <p class="muted text-sm">Tema gelap & terang dengan toggle instan dan persist.</p>
      </div>
      <div class="glass rounded-2xl p-4">
        <div class="text-2xl mb-1">🏷️</div>
        <div class="font-semibold">Trending Tag</div>
        <p class="muted text-sm">Ikuti tag favorit dan lihat mana yang sedang ramai.</p>
      </div>
    </div>
  </section>

  <!-- TRENDING TAGS -->
  <section class="glass rounded-2xl p-4">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-semibold">Sedang Ramai</h2>
      <a href="/explore" class="text-sm underline hover:opacity-90">Lihat semua</a>
    </div>
    <div class="trend-cloud mt-3">
      <template x-for="t in trendingTags.slice(0,18)" :key="t[0]">
        <a :href="'/explore#'+t[0]" class="pill">
          <span>#<span x-text="t[0]"></span></span>
          <span class="badge" x-text="t[1]"></span>
        </a>
      </template>
      <div class="muted text-sm" x-show="!trendingTags.length">Belum ada data.</div>
    </div>
  </section>

  <!-- TESTIMONI -->
  <section>
    <h2 class="text-xl font-bold mb-3 text-center">Apa kata kreator?</h2>
    <div class="grid md:grid-cols-3 gap-3">
      <figure class="glass rounded-2xl p-4"><blockquote class="text-sm">“Buat pamer kitbash kilat tanpa ribet. Suka UI-nya.”</blockquote><figcaption class="mt-2 text-xs muted">— Ari, Modeler Miniatur</figcaption></figure>
      <figure class="glass rounded-2xl p-4"><blockquote class="text-sm">“Filter tag & save lokalnya enak. Tinggal fokus bikin karya.”</blockquote><figcaption class="mt-2 text-xs muted">— Naya, Ilustrator</figcaption></figure>
      <figure class="glass rounded-2xl p-4"><blockquote class="text-sm">“Berguna buat showcase musik demo sebelum rilis.”</blockquote><figcaption class="mt-2 text-xs muted">— Rafi, Produser</figcaption></figure>
    </div>
  </section>

</section>
@endsection

@push('scripts')
<script>
function LandingPage(){
  return {
    stats:{posts:0, creators:0, tags:0, likes:0},
    trendingTags:[],
    marquee:[],
    async init(){
      try{
        const posts = await (await fetch('/data/posts.json')).json();
        const custom = JSON.parse(localStorage.getItem('hs-custom-posts')||'[]');
        const all = [...custom, ...posts];

        const authors = new Set(); const tagMap = new Map(); let likeSum = 0;
        all.forEach(p=>{
          if(p.author) authors.add(p.author);
          (p.tags||[]).forEach(t=> tagMap.set(t,(tagMap.get(t)||0)+1));
          likeSum += Number(p.likes||0);
        });

        this.stats.posts = all.length;
        this.stats.creators = authors.size;
        this.stats.tags = tagMap.size;
        this.stats.likes = likeSum;

        this.trendingTags = [...tagMap.entries()].sort((a,b)=>b[1]-a[1]);
        const base = this.trendingTags.slice(0,20).map(x=>x[0]);
        this.marquee = [...base, ...base];
      }catch(e){ console.error(e); }
    }
  }
}
</script>
@endpush
