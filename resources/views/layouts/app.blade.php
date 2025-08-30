<!doctype html>
<html lang="id" x-data="{menu:false}" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','HobiSpace')</title>

  <!-- Tailwind + Dark Mode -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script> tailwind.config = { darkMode: 'class' } </script>

  <!-- AlpineJS -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- THEME -->
  <style>

    .blur-up{ filter:blur(12px) saturate(.9); transform:scale(1.02); transition:filter .25s ease, transform .25s ease, opacity .25s ease }
.blur-up.is-loaded{ filter:blur(0); transform:none; opacity:1 }

/* CmdK */
.cmdk-backdrop{ background:rgba(0,0,0,.5); }
.cmdk{ width: min(680px, 92vw); border-radius:1rem; }
.cmdk input{ outline:none }
.cmdk .item{ padding:.55rem .75rem; border-radius:.6rem; }
.cmdk .item[aria-selected="true"]{ background:rgba(34,211,238,.14); }
  :root{
    --bg:linear-gradient(180deg,#0b1323 0%,#0a0f1c 100%);
    --surface:rgba(255,255,255,.88);
    --text:#0b1220;
    --muted:#657189;
    --border:rgba(16,24,40,.08);
    --chip:#eef7fb;
    --ring:#22d3ee;
    --accent:#06b6d4;
  }
  .dark{
    --bg:linear-gradient(180deg,#090b10 0%, #0e121a 100%);
    --surface:rgba(17,24,39,.9);
    --text:#e6e7eb;
    --muted:#a2a9b8;
    --border:rgba(255,255,255,.08);
    --chip:#0f1c24;
  }

  body{ background:var(--bg); color:var(--text); }
  .glass{ background:var(--surface); backdrop-filter:saturate(180%) blur(14px); border:1px solid var(--border); }
  .chip{ background:var(--chip); }
  .muted{ color:var(--muted); }
  .btn{ transition:.15s } .btn:hover{ transform:translateY(-1px) }
  .btn-accent{ background:var(--accent); color:#fff }
  .btn-accent-ghost{ border:1px solid var(--border) }

  .pill{ display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .7rem; border-radius:999px; border:1px solid var(--border); background:color-mix(in oklab, var(--surface) 70%, transparent) }
  .badge{ font-size:.72rem; padding:.15rem .45rem; border-radius:999px; background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; font-weight:700 }
  .dark .badge{ background:color-mix(in oklab, var(--accent) 36%, transparent); color:#c9f3ff; border-color:rgba(34,211,238,.45) }

  .ring-app:focus{ outline:none; box-shadow:0 0 0 3px color-mix(in oklab, var(--ring) 30%, transparent) }
  @media (min-width:768px){ .bottom-nav{ display:none } }
  .no-scrollbar::-webkit-scrollbar{ display:none } .no-scrollbar{ scrollbar-width:none }

  .date-badge{ display:inline-block; padding:.25rem .6rem; border-radius:.75rem; font-size:.75rem; line-height:1.2; background:rgba(148,163,184,.15) }
  .dark .date-badge{ background:rgba(255,255,255,.1) }

  .icon-row{ display:flex; gap:.5rem; align-items:center }
  .icon-btn{ position:relative; width:38px; height:38px; border-radius:9999px; display:grid; place-items:center; background:var(--chip); border:1px solid var(--border); transition:.15s }
  .icon-btn:hover{ transform:translateY(-1px) }
  .icon-btn .icon{ width:18px; height:18px; fill:currentColor; color:#64748b }
  .mini-badge{ position:absolute; top:-6px; right:-6px; font-size:10px; line-height:1; padding:.15rem .35rem; border-radius:999px; background:#e2e8f0; color:#0f172a; border:1px solid #cbd5e1; font-weight:700 }
  .dark .mini-badge{ background:rgba(255,255,255,.12); color:#e5e7eb; border-color:rgba(255,255,255,.18) }
  .icon-btn.is-active.like{ border-color:#f43f5e40; box-shadow:0 0 0 2px #f43f5e30 inset; color:#f43f5e }
  .icon-btn.is-active.save{ border-color:#f59e0b40; box-shadow:0 0 0 2px #f59e0b30 inset; color:#f59e0b }

  .bn-wrapper{ position:relative } .bn-ind{ pointer-events:none }
  .bn-ind{ position:absolute; left:0; top:.25rem; bottom:.25rem; width:25%; border-radius:.75rem; background:var(--accent); color:#fff; box-shadow:0 6px 18px rgba(0,0,0,.28); transform:translate3d(calc(var(--step,0)*100%),0,0); opacity:.95 }
  .bn-wrapper.is-ready .bn-ind{ transition:transform .26s ease-out, opacity .18s ease-out }
  .dark .bn-ind{ background:color-mix(in oklab, var(--accent) 88%, #06b6d4) }
  .bn-item{ position:relative; z-index:1 }

  .dark .glass{ background:rgba(13,18,28,.9); border-color:rgba(255,255,255,.06) }
  .dark .muted{ color:#9aa6b2 } .dark ::placeholder{ color:#93a4c1 }
  .dark input,.dark select,.dark textarea{ background:#0f172a; color:#e5e7eb; border-color:rgba(255,255,255,.08) }
  .dark body{ color-scheme:dark }
  .dark .chip{ background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.06) }

  [x-cloak]{display:none!important}
  </style>

   <!-- ⬇️ Tambahan PWA-lite -->
  <link rel="manifest" href="/manifest.webmanifest">
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw-hobispace.js').catch(()=>{});
      });
    }
  </script>
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
      <a href="/group"   class="hidden md:inline-block px-3 py-1.5 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">Group</a>
      <a href="/messages" class="hidden md:inline-block px-3 py-1.5 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">Pesan</a>
      <a href="/submit"  class="hidden md:inline-block px-3 py-1.5 rounded-xl btn-accent text-white">Buat</a>

      <div class="ml-auto flex items-center gap-2">
        <div class="hidden sm:flex items-center gap-2 glass px-2 py-1.5 rounded-xl">
          <svg class="w-4 h-4 opacity-60" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a8 8 0 1 1 5.292-14.03l4.869 4.869-1.414 1.414-4.869-4.869A8 8 0 0 1 10 18z"/></svg>
          <input class="bg-transparent outline-none text-sm w-64" placeholder="Cari di HobiSpace…">
        </div>

        <button data-theme-toggle class="p-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5" title="Toggle theme">🌓</button>

        <a href="/u/rina" class="flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">
          <img id="topbarAvatar" src="/images/demo/rina.jpg" class="w-8 h-8 rounded-full object-cover" alt=""><span class="hidden sm:block">Profil</span>
        </a>
      </div>
    </nav>
  </header>

  <!-- Drawer -->
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
        <a href="/group"   class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">Group</a>
        <a href="/messages" class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">Pesan</a>
        <a href="/submit"  class="px-3 py-2 rounded-xl btn-accent text-white">Buat Post</a>
        <a href="/u/rina"  class="px-3 py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">Profil</a>
      </nav>
      <div class="mt-3">
        <button data-theme-toggle class="w-full px-3 py-2 rounded-xl btn-accent-ghost">🌓 Toggle tema</button>
      </div>
      <p class="muted text-xs mt-6">Demo front-end only (JSON + localStorage).</p>
    </aside>
  </div>

  <!-- Content -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6">@yield('content')</main>

  @hasSection('hideBottomNav') @else
  <nav class="bottom-nav fixed bottom-0 inset-x-0 z-40 glass px-3 py-2">
    <div x-data="BottomNav()" x-init="init()" class="bn-wrapper grid grid-cols-4 gap-2 items-center">
      <div x-ref="ind" class="bn-ind"></div>

      <a href="/explore" class="bn-item text-center py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5"
         :class="active===0 ? 'font-semibold text-white' : ''"
         @click.prevent="go(0,'/explore')">🏠<div class="text-[11px]">Beranda</div></a>

      <a href="/group" class="bn-item text-center py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5"
         :class="active===1 ? 'font-semibold text-white' : ''"
         @click.prevent="go(1,'/group')">👥<div class="text-[11px]">Group</div></a>

      <a href="/submit" class="bn-item text-center py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5"
         :class="active===2 ? 'font-semibold text-white' : ''"
         @click.prevent="go(2,'/submit')">➕<div class="text-[11px]">Buat</div></a>

      <a href="/u/rina" class="bn-item text-center py-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5"
         :class="active===3 ? 'font-semibold text-white' : ''"
         @click.prevent="go(3,'/u/rina')">👤<div class="text-[11px]">Profil</div></a>
    </div>
  </nav>
  @endif

  <!-- Toast -->
  <div x-data="{show:false,msg:''}" x-show="show" x-cloak x-transition class="fixed bottom-20 right-4 sm:right-6 z-[75]">
    <div class="px-4 py-2 rounded-xl bg-black/85 text-white text-sm shadow-xl" x-text="msg"></div>
    <script> window.toast=(m)=>{const r=document.currentScript.parentElement.__x.$data; r.msg=m; r.show=true; setTimeout(()=>r.show=false,1700);} </script>
  </div>

  <!-- Theme + BottomNav + UserMeta -->
  <script>
    // THEME
    (function initTheme(){
      const KEY='hs-theme', root=document.documentElement, saved=localStorage.getItem(KEY);
      if (saved==='dark' || (!saved && matchMedia('(prefers-color-scheme: dark)').matches)) root.classList.add('dark');
      const toggle=()=>{const d=root.classList.toggle('dark'); localStorage.setItem(KEY,d?'dark':'light')};
      const setup=()=>document.querySelectorAll('[data-theme-toggle]').forEach(b=>b.onclick=toggle);
      (document.readyState==='loading')?document.addEventListener('DOMContentLoaded',setup):setup();
    })();

    // BOTTOM NAV
    function BottomNav(){
      return {
        active:0,
        init(){
          const w=this.$root, ind=this.$refs?.ind; if(!w||!ind) return;
          const p=location.pathname;
          this.active = p.startsWith('/group')?1 : p.startsWith('/submit')?2 : (p.startsWith('/u/')||p==='/profile')?3 : 0;
          ind.style.width=(100/(w.querySelectorAll('.bn-item').length||4))+'%';
          ind.style.setProperty('--step',this.active);
          requestAnimationFrame(()=>w.classList.add('is-ready'));
        },
        go(i,href){ this.$refs.ind.style.setProperty('--step',i); this.active=i; setTimeout(()=>location.assign(href),280); }
      }
    }

    // Dummy login
    window.CURRENT_USER={ name:'Rina Putri', username:'rina', avatar:'/images/demo/rina.jpg' };

    // === User meta global (avatar/nama override) ===
    (function(){
      const KEY='hs-user-overrides';
      const listeners = new Set();
      const read = ()=>{ try{ return JSON.parse(localStorage.getItem(KEY))||{} }catch{return{}} };
      const write = (obj)=>{ localStorage.setItem(KEY, JSON.stringify(obj)); window.dispatchEvent(new CustomEvent('user:meta-updated')); listeners.forEach(fn=>{ try{fn()}catch{} }); };

      window.UserMeta = {
        get(username){ return (read()[username]||{}); },
        upsert(username, patch){ const all=read(); all[username] = {...(all[username]||{}), ...patch}; write(all); },
        on(cb){ listeners.add(cb); return ()=>listeners.delete(cb); }
      };

      // Sync header avatar dengan override CURRENT_USER
      function applyHeaderAvatar(){
        try{
          const me = window.CURRENT_USER?.username;
          const av = (window.UserMeta?.get(me)?.avatar) || window.CURRENT_USER?.avatar || '/images/demo/rina.jpg';
          const el = document.getElementById('topbarAvatar');
          if(el && av) el.src = av;
        }catch{}
      }
      applyHeaderAvatar();
      window.addEventListener('user:meta-updated', applyHeaderAvatar);
      window.addEventListener('storage', e=>{ if(e.key===KEY) applyHeaderAvatar(); });
    })();
  </script>
<!-- Command Palette (⌘/Ctrl+K) -->
<div x-data="CmdK()" x-init="init()" x-show="open"
     x-cloak class="fixed inset-0 z-[80] cmdk-backdrop grid place-items-center"
     @keydown.window.prevent.cmd.k="toggle()" @keydown.window.prevent.ctrl.k="toggle()" @keydown.escape.window="close()">
  <div class="glass cmdk p-3" @click.stop>
    <div class="flex items-center gap-2 px-2 py-1.5 rounded-xl border">
      <svg class="w-4 h-4 opacity-60" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a8 8 0 1 1 5.292-14.03l4.869 4.869-1.414 1.414-4.869-4.869A8 8 0 0 1 10 18z"/></svg>
      <input x-model="q" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="go()"
             class="bg-transparent flex-1 text-sm" placeholder="Cari grup atau #tag…">
      <span class="text-[11px] muted">ESC</span>
    </div>
    <div class="max-h-72 overflow-auto mt-2">
      <template x-for="(it,i) in results" :key="it.key">
        <div class="item cursor-pointer hover:bg-black/5 dark:hover:bg-white/5"
             :aria-selected="i===idx" @mouseenter="idx=i" @click="go()">
          <div class="px-2 py-1.5 flex items-center gap-2">
            <span x-text="it.type==='tag'?'#':'👥'"></span>
            <div class="min-w-0">
              <div class="text-sm truncate" x-text="it.label"></div>
              <div class="text-[11px] muted truncate" x-text="it.sub"></div>
            </div>
          </div>
        </div>
      </template>
      <div class="muted text-sm p-3" x-show="!results.length">Tidak ada hasil.</div>
    </div>
  </div>
</div>

<script>
function CmdK(){
  const slugify = s => (s||'').toLowerCase().trim().replace(/[^\w\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-');
  return {
    open:false,q:'',idx:0,items:[],results:[],
    toggle(){ this.open=!this.open; if(this.open) setTimeout(()=>document.querySelector('.cmdk input')?.focus(), 10); },
    close(){ this.open=false; },
    async init(){
      // ambil grup dari JSON + lokal
      let base=[]; try{ base = await (await fetch('/data/groups.json',{cache:'force-cache'})).json() }catch{ base=[] }
      let local=[]; try{
        const keys=['hs-custom-groups','hs-user-groups','hs-groups-user'];
        local = keys.flatMap(k=>{ try{ return JSON.parse(localStorage.getItem(k)||'[]') }catch{ return [] } });
      }catch{ local=[] }
      const all=[...local, ...base];
      const tags = new Set();
      all.forEach(g=>(Array.isArray(g.tags)?g.tags:[]).forEach(t=>tags.add(t)));

      this.items = [
        ...all.map(g=>({type:'group', key:'g:'+ (g.slug||slugify(g.name||'')), label:g.name, sub:'@'+(g.handle||g.owner||''), to:'/group/'+(g.slug||slugify(g.name||'')) })),
        ...[...tags].map(t=>({type:'tag', key:'t:'+t, label:'#'+t, sub:'Cari grup dgn tag ini', to:'/group?tag='+encodeURIComponent(t)}))
      ];
      this.$watch('q', ()=>this.search());
      this.search();
    },
    search(){
      const qq=(this.q||'').toLowerCase();
      let arr = !qq ? this.items.slice(0,12)
        : this.items.filter(it=>it.label.toLowerCase().includes(qq)||it.sub.toLowerCase().includes(qq)).slice(0,20);
      this.results=arr; this.idx=0;
    },
    move(d){ if(!this.results.length) return; this.idx=(this.idx+d+this.results.length)%this.results.length; },
    go(){ const it=this.results[this.idx]; if(!it) return; this.close(); location.assign(it.to); }
  }
}
</script>

  @stack('scripts')
</body>
</html>
