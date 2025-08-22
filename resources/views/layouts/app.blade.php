<!doctype html>
<html lang="id" x-data="{menu:false}" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','HobiSpace')</title>

  <!-- Tailwind + Dark Mode config -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' }
  </script>

  <!-- AlpineJS -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- THEME TOKENS (Light & Dark via CSS variables) -->
  <style>
  :root{
    --bg:linear-gradient(180deg,#0f172a 0%,#0b1220 100%); /* biar dark makin solid */
    --surface:rgba(255,255,255,.9);
    --text:#0b1220;
    --muted:#667085;
    --border:rgba(16,24,40,.08);
    --chip:#eef7fb;
    --ring:#06b6d4;
    --accent:#0ea5e9;
    --accent-2:#06b6d4;
  }
  .dark{
    --bg:linear-gradient(180deg,#0b0b0e 0%, #0f1117 100%);
    --surface:rgba(21,21,26,.92);
    --text:#e6e7eb;
    --muted:#a1a8b3;
    --border:rgba(255,255,255,.08);
    --chip:#0e1a20; /* lebih gelap biar kontras */
  }

  body{ background:var(--bg); color:var(--text); }
  .glass{ background:var(--surface); backdrop-filter:saturate(180%) blur(14px); border:1px solid var(--border); }
  .chip{ background:var(--chip); }
  .muted{ color:var(--muted); }

  /* 🔹 tambahan untuk trending pills & badges */
  .pill{ display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .7rem; border-radius:999px; border:1px solid var(--border); background:color-mix(in oklab, var(--surface) 70%, transparent); }
  .badge{ font-size:.72rem; padding:.15rem .45rem; border-radius:999px; background:color-mix(in oklab, var(--accent) 18%, transparent); color:color-mix(in oklab, var(--accent) 95%, #fff 0%); }
  .dark .badge{ background:color-mix(in oklab, var(--accent) 30%, transparent); color:#bceaff; }
  .trend-cloud{ display:flex; flex-wrap:wrap; gap:.5rem; }
  .trend-cloud .pill:hover{ filter:brightness(1.05); }
  .btn{ transition:.15s; } .btn:hover{ transform:translateY(-1px); }
  .btn-accent{ background:var(--accent); color:#fff; }
  .btn-accent-ghost{ border:1px solid var(--border); }
  .ring-app:focus{ outline:none; box-shadow:0 0 0 3px color-mix(in oklab, var(--ring) 30%, transparent); }
  @media (min-width:768px){ .bottom-nav{ display:none } }
  .no-scrollbar::-webkit-scrollbar{ display:none } .no-scrollbar{ scrollbar-width:none }
</style>
<style id="trend-badge-light-fix">
  /* Pertebal badge trending khusus light mode */
  .trend-cloud .pill .badge{
    background: #e0f2fe;       /* biru muda solid (cyan-100) */
    color: #0369a1;            /* teks biru gelap (cyan-800) */
    border: 1px solid #bae6fd; /* border biru soft */
    font-weight: 600;
  }

  /* Hover tetap ada aksen */
  .trend-cloud .pill:hover .badge{
    background: #bae6fd;
    color: #075985;
  }
</style>
<style id="trend-badge-dark-fix">
  /* Pertebal kontras Trending di dark mode */
  .dark .trend-cloud .pill{
    background: rgba(255,255,255,.10);                 /* pill sedikit lebih solid */
    border-color: rgba(255,255,255,.14);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
  }

  /* Badge jumlah: fill solid + teks kontras */
  .dark .trend-cloud .pill .badge{
    background: #0ea5e9;                               /* accent penuh (bukan transparan) */
    color: #06121b;                                     /* gelap biar terbaca */
    border-color: transparent;
    box-shadow: 0 1px 0 rgba(0,0,0,.25),
                0 0 0 1px rgba(14,165,233,.55) inset;   /* sedikit depth */
  }

  /* Hover ring lembut biar tetap enak */
  .dark .trend-cloud .pill:hover{
    box-shadow: inset 0 1px 0 rgba(255,255,255,.08),
                0 0 0 2px rgba(14,165,233,.25);
  }
</style>
<style id="date-badge">
  .date-badge{
    padding:.22rem .55rem;           /* ruang kiri‑kanan biar lega */
    border-radius:.75rem;
    background: rgba(148,163,184,.15); /* slate-400/15 untuk light */
    line-height:1;                   /* tinggi pas */
  }
  .dark .date-badge{                 /* lebih kontras di dark */
    background: rgba(255,255,255,.08);
  }
</style>

<style id="toggle-fix">
  /* Tombol toggle (Semua/Diikuti) */
  .toggle {
    background: #ffffff;                    /* solid di light */
    color: var(--text);
    border: 1px solid var(--border);
    border-radius: 0.75rem;                 /* rounded-xl */
    padding: .375rem .75rem;                /* py-1.5 px-3 */
    transition: .15s;
  }
  .toggle:hover { transform: translateY(-1px); }

  .dark .toggle{
    background: rgba(255,255,255,.08);      /* subtle di dark */
    color: #e5e7eb;
    border-color: rgba(255,255,255,.10);
  }

  /* State aktif */
  .toggle.is-active{
    background: var(--accent);
    color: #fff;
    border-color: transparent;
  }
</style>
<style>
  .date-badge{
    display:inline-block;
    padding:.25rem .6rem;
    border-radius:.75rem;
    font-size:.75rem;
    line-height:1.2;
    background:rgba(148,163,184,.15);
    white-space:nowrap;
  }
  .dark .date-badge{
    background:rgba(255,255,255,.10);
  }
</style>
<style id="icon-footer">
  .icon-row{
    display:flex;
    gap:.5rem;
    align-items:center;
    justify-content:flex-start;
  }
  .icon-btn{
    position:relative;
    width:38px; height:38px;
    border-radius:9999px;
    display:grid; place-items:center;
    background:var(--chip);
    border:1px solid var(--border);
    transition:.15s;
  }
  .icon-btn:hover{ transform:translateY(-1px); }
  .icon-btn .icon{ width:18px; height:18px; fill:currentColor; color:#64748b; }

  .mini-badge{
    position:absolute; top:-6px; right:-6px;
    font-size:10px; line-height:1;
    padding:.15rem .35rem;
    border-radius:999px;
    background:#e2e8f0; color:#0f172a;
    border:1px solid #cbd5e1;
    font-weight:600;
  }
  .dark .mini-badge{
    background:rgba(255,255,255,.12);
    color:#e5e7eb;
    border-color:rgba(255,255,255,.18);
  }

  /* State aktif */
  .icon-btn.is-active.like {
    border-color:#f43f5e40;
    box-shadow:0 0 0 2px #f43f5e30 inset;
    color:#f43f5e;
  }
  .icon-btn.is-active.save {
    border-color:#f59e0b40;
    box-shadow:0 0 0 2px #f59e0b30 inset;
    color:#f59e0b;
  }

  /* Date */
  .card-date{
    margin-top:.5rem;
    font-size:.75rem;
    color:var(--muted);
  }
</style>

<style id="bottom-nav-indicator">
  .bn-wrapper{ position:relative; }
  .bn-ind{
    position:absolute; left:0; top:.25rem; bottom:.25rem;
    width:33.3333%;                       /* 3 item */
    border-radius: .75rem;                 /* rounded-xl */
    background: var(--accent);
    color:#fff;
    box-shadow: 0 6px 18px rgba(0,0,0,.28);
    transform: translateX(0%);
    transition: transform .22s ease-out, opacity .18s ease-out;
    will-change: transform;
    opacity:.95;
  }
  .dark .bn-ind{ background: color-mix(in oklab, var(--accent) 88%, #0ea5e9); }
  .bn-item{ position:relative; z-index:1 } /* di atas indikator */
</style>

<style id="dark-polish">
  /* --- Dark polish: kontras & estetika --- */

  /* 1) Kartu/box di dark: lebih legible */
  .dark .glass{
    background: rgba(17,24,39,.86);              /* ~ slate-900/0.86 */
    border-color: rgba(255,255,255,.06);
  }

  /* 2) Teks muted & placeholder di dark */
  .dark .muted{ color:#9aa6b2; }                 /* abu lembut */
  .dark ::placeholder{ color:#94a3b8; }

  /* 3) Input/select/textarea supaya gelap */
  .dark input, .dark select, .dark textarea{
    background:#0f172a;                          /* slate-900 */
    color:#e5e7eb;                               /* slate-200 */
    border-color:rgba(255,255,255,.10);
  }
  /* Biar native control (dropdown, date picker) ikut gelap */
  .dark body{ color-scheme: dark; }

  /* 4) Chip umum */
  .dark .chip{
    background: rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.06);
  }

  /* 5) Trending pills – tampilan “badge” yang lebih mewah */
  .trend-cloud .pill{
    background: linear-gradient(180deg, rgba(255,255,255,.14), rgba(255,255,255,.08));
    border:1px solid rgba(255,255,255,.10);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
  }
  .trend-cloud .pill:hover{
    transform: translateY(-1px);
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,.08),
      0 0 0 2px rgba(59,130,246,.28);            /* ring biru lembut */
  }

  /* 6) Angka di trending (#tag • count) */
  .pill .badge{
    background: rgba(14,165,233,.20);            /* cyan-500/20 */
    color: #bfe9ff;
    border:1px solid rgba(14,165,233,.35);
    padding:.18rem .45rem;
    border-radius:999px;
    font-weight:600;
  }

  /* 7) Kartu gambar feed: beri aksen halus saat hover */
  .dark .glass:hover{
    box-shadow: 0 8px 24px rgba(0,0,0,.35);
  }

  /* 8) Perkecil jarak vertikal badge/tag biar rapih */
  .trend-cloud{ row-gap:.45rem; }
</style>
<style>[x-cloak]{display:none!important}</style>

</head>
<body class="min-h-screen">

  <!-- Topbar -->
  <header class="sticky top-0 z-50 glass">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center gap-2">
      <button class="md:hidden p-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5" @click="menu=true">☰</button>
      <a href="/" class="flex items-center gap-2 font-extrabold text-xl tracking-tight">
        <span class="inline-grid place-items-center w-8 h-8 rounded-xl bg-cyan-600 text-white shadow">H</span>
        Hobi<span class="text-cyan-600">Space</span>
      </a>
      <a href="/explore" class="hidden md:inline-block px-3 py-1.5 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">Beranda</a>
      <a href="/submit"  class="hidden md:inline-block px-3 py-1.5 rounded-xl btn-accent text-white">Buat</a>

      <div class="ml-auto flex items-center gap-2">
        <!-- Global search -->
        <div class="hidden sm:flex items-center gap-2 glass px-2 py-1.5 rounded-xl">
          <svg class="w-4 h-4 opacity-60" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a8 8 0 1 1 5.292-14.03l4.869 4.869-1.414 1.414-4.869-4.869A8 8 0 0 1 10 18z"/></svg>
          <input id="globalSearch" class="bg-transparent outline-none text-sm w-64" placeholder="Cari di HobiSpace…">
        </div>

        <!-- Theme toggle (fungsi di bawah) -->
        <button data-theme-toggle class="p-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5" title="Toggle theme">🌓</button>

        <a href="/u/rina" class="flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">
          <img src="/images/demo/rina.jpg" class="w-8 h-8 rounded-full object-cover" alt=""><span class="hidden sm:block">Profil</span>
        </a>
      </div>
    </nav>
  </header>

  <!-- Drawer (mobile) -->
  <div class="fixed inset-0 z-[60]" x-show="menu" x-cloak x-transition.opacity>
    <div class="absolute inset-0 bg-black/40" @click="menu=false"></div>
    <aside class="absolute left-0 top-0 bottom-0 w-72 p-4 glass">
      <div class="flex items-center justify-between mb-4">
        <span class="font-bold text-lg">Menu</span>
        <button class="p-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5" @click="menu=false">✕</button>
      </div>
      <nav class="grid gap-2">
        <a href="/"        class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">Beranda</a>
        <a href="/explore" class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">Explore</a>
        <a href="/submit"  class="px-3 py-2 rounded-xl btn-accent text-white">Buat Post</a>
        <a href="/u/rina"  class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">Profil</a>
      </nav>
      <div class="mt-3">
        <button data-theme-toggle class="w-full px-3 py-2 rounded-xl btn-accent-ghost">🌓 Toggle tema</button>
      </div>
      <p class="muted text-xs mt-6">Demo front‑end only (JSON + localStorage).</p>
    </aside>
  </div>

  <!-- Content -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6">@yield('content')</main>

  <!-- Bottom nav (mobile) -->
  <!-- Bottom nav (mobile) -->
<nav class="bottom-nav fixed bottom-0 inset-x-0 z-40 glass px-3 py-2">
  <div x-data="BottomNav()" x-init="init()" class="bn-wrapper grid grid-cols-3 gap-2 items-center">
    <!-- indikator meluncur -->
    <div x-ref="ind" class="bn-ind"></div>
    
    <!-- item 0: Beranda -->
    <a href="/explore"
       class="bn-item text-center py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5"
       :class="active===0 ? 'font-semibold text-white' : ''"
       @click.prevent="go(0,'/explore')">
      🏠
      <div class="text-[11px]">Beranda</div>
    </a>

    <!-- item 1: Buat -->
    <a href="/submit"
       class="bn-item text-center py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5"
       :class="active===1 ? 'font-semibold text-white' : ''"
       @click.prevent="go(1,'/submit')">
      ➕
      <div class="text-[11px]">Buat</div>
    </a>

    <!-- item 2: Profil -->
    <a href="/u/rina"
       class="bn-item text-center py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5"
       :class="active===2 ? 'font-semibold text-white' : ''"
       @click.prevent="go(2,'/u/rina')">
      👤
      <div class="text-[11px]">Profil</div>
    </a>
  </div>
</nav>


  <!-- Global Post Modal (dipakai semua halaman) -->
  <div id="postModal" x-data="{open:false, post:null, comments:[]}"
     x-on:show-post.window="post=$event.detail; open=true; comments=JSON.parse(localStorage.getItem('hs-cmt-'+post.id)||'[]')"
     x-show="open" x-cloak
     x-transition.opacity.scale
     class="fixed inset-0 z-[70] flex items-center justify-center p-4">

    <div class="absolute inset-0 bg-black/60" @click="open=false"></div>
    <div class="relative w-full max-w-5xl glass rounded-3xl overflow-hidden shadow-2xl">
      <div class="grid md:grid-cols-2">
        <img :src="post?.image" :alt="post?.title" class="w-full h-full object-cover">
        <div class="p-5 md:p-6 space-y-3">
          <div class="flex items-center gap-3">
            <img :src="post?.avatar" class="w-10 h-10 rounded-full object-cover" alt="">
            <div>
              <h3 class="font-semibold" x-text="post?.title"></h3>
              <p class="text-sm muted" x-text="'by @'+post?.author"></p>
            </div>
            <button class="ml-auto px-3 py-1.5 rounded-xl btn-accent">Ikuti</button>
          </div>
          <p class="text-sm" x-text="post?.desc"></p>
          <div class="flex flex-wrap gap-2">
            <template x-for="t in (post?.tags||[])">
              <span class="chip px-2 py-1 text-xs rounded-full">#<span x-text="t"></span></span>
            </template>
          </div>
          <div class="flex items-center gap-4 text-sm muted">
            <span>❤ <span x-text="post?.likes"></span></span>
            <span x-text="new Date(post?.date).toLocaleDateString()"></span>
            <button @click="navigator.clipboard.writeText(location.origin+'/explore#'+post?.id); window.toast('Tautan disalin')"
                    class="px-2 py-1 rounded-xl btn-accent-ghost">Bagikan</button>
          </div>
          <div class="mt-2">
            <h4 class="font-semibold mb-1">Komentar</h4>
            <div class="space-y-2 max-h-40 overflow-auto no-scrollbar">
              <template x-for="c in comments">
                <div class="text-sm p-2 rounded-xl chip"><b>guest:</b> <span x-text="c"></span></div>
              </template>
            </div>
            <div class="flex gap-2 mt-2">
              <input x-ref="cmt" class="flex-1 rounded-xl px-3 py-2 border" placeholder="Tulis komentar (dummy)">
              <button @click="(()=>{const t=$refs.cmt.value.trim(); if(!t) return; comments=[...comments,t]; localStorage.setItem('hs-cmt-'+post.id,JSON.stringify(comments)); $refs.cmt.value=''; window.toast('Komentar ditambahkan')})()"
                      class="px-3 py-2 rounded-xl btn-accent-ghost">Kirim</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div x-data="{show:false,msg:''}" x-show="show" x-cloak x-transition
       class="fixed bottom-20 right-4 sm:right-6 z-[75]">
    <div class="px-4 py-2 rounded-xl bg-black/85 text-white text-sm shadow-xl" x-text="msg"></div>
    <script>
      window.toast=(m)=>{
        const r=document.currentScript.parentElement.__x.$data;
        r.msg=m; r.show=true; setTimeout(()=>r.show=false,1700);
      };
    </script>
  </div>

  <!-- Theme init + toggle -->
  <script>
  function BottomNav(){
    return {
      active: 0,
      init(){
        // tentukan index aktif dari path saat ini
        const p = location.pathname;
        this.active = p.startsWith('/submit') ? 1 : (p.startsWith('/u/') ? 2 : 0);

        // animasi transisi antar-halaman:
        // baca index sebelumnya agar indikator start dari sana
        const prev = Number(sessionStorage.getItem('hs-bottom-prev'));
        const start = Number.isInteger(prev) ? prev : this.active;

        // set posisi awal, lalu animasikan ke posisi aktif
        this.$nextTick(() => {
          this.$refs.ind.style.transform = `translateX(${start*100}%)`;
          // trigger reflow agar transition ke posisi baru berjalan
          void this.$refs.ind.offsetWidth;
          this.$refs.ind.style.transform = `translateX(${this.active*100}%)`;
        });
      },
      go(i, href){
        // simpan posisi sekarang → dipakai halaman tujuan sebagai start
        sessionStorage.setItem('hs-bottom-prev', this.active);
        // animasikan indikator ke target dulu biar terasa “bergerak”
        this.$refs.ind.style.transform = `translateX(${i*100}%)`;
        // lalu navigasi setelah sedikit delay (sinkron sama durasi CSS)
        setTimeout(() => { location.href = href; }, 160);
      }
    }
  }
</script>

  <script>
    (function(){
      const key='hs-theme';
      const saved = localStorage.getItem(key) || 'light';
      if (saved === 'dark') document.documentElement.classList.add('dark');

      // tombol dengan attribute [data-theme-toggle]
      document.addEventListener('DOMContentLoaded', ()=>{
        document.querySelectorAll('[data-theme-toggle]').forEach(btn=>{
          btn.addEventListener('click', ()=>{
            document.documentElement.classList.toggle('dark');
            localStorage.setItem(key, document.documentElement.classList.contains('dark') ? 'dark' : 'light');
          });
        });
      });
    })();
  </script>
<script>
  // Key untuk simpan follow status
  const FOLLOW_KEY = "hs-follows";

  // ambil data follow dari localStorage
  function getFollows(){
    return JSON.parse(localStorage.getItem(FOLLOW_KEY) || "[]");
  }

  // cek apakah user di-follow
  function isFollowed(username){
    return getFollows().includes(username);
  }

  // toggle follow/unfollow
  function toggleFollow(username){
    let follows = getFollows();
    if(follows.includes(username)){
      follows = follows.filter(u => u !== username);
      window.toast("Berhenti mengikuti @" + username);
    } else {
      follows.push(username);
      window.toast("Mengikuti @" + username);
    }
    localStorage.setItem(FOLLOW_KEY, JSON.stringify(follows));
    document.dispatchEvent(new CustomEvent("follow-changed"));
  }
</script>

  @stack('scripts')
</body>
</html>
