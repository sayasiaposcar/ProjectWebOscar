@extends('layouts.app')
@section('title','Posting di Grup | HobiSpace')

@section('content')
<section x-data="GroupPostCreate()" x-init="init()" class="max-w-xl mx-auto">
  <div class="glass rounded-2xl p-4 sm:p-6 space-y-4">

    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Posting ke Grup</h1>
      <a :href="backUrl"
         class="px-3 py-1.5 rounded-xl btn-accent-ghost"
         @click.prevent="backUrl ? (location.href=backUrl) : history.back()">← Kembali</a>
    </div>

    <!-- Info grup -->
    <template x-if="group">
      <div class="p-3 rounded-xl bg-black/5 dark:bg-white/5">
        <div class="font-semibold" x-text="group.name"></div>
        <div class="text-xs muted">#<span x-text="group.slug"></span></div>
      </div>
    </template>
{{--  --}}
    <!-- Judul (opsional untuk post teks) -->
    <input x-model.trim="title" class="w-full rounded-xl px-3 py-2 border" placeholder="Judul (opsional untuk teks)">

    <!-- Teks post (opsional kalau pakai gambar) -->
    <textarea x-model.trim="textRaw"
              class="w-full rounded-xl px-3 py-2 border min-h-28"
              placeholder="Tulis sesuatu… (boleh kosong jika mengunggah gambar)"></textarea>

    <!-- Tag (wajib) -->
    <input x-model.trim="tagsRaw" class="w-full rounded-xl px-3 py-2 border" placeholder="Tag (pisah koma) – wajib">

    <!-- DROPZONE: drag & drop + paste + klik area (gambar opsional) -->
    <div
      data-dropzone
      class="rounded-2xl border-2 border-dashed border-slate-300 dark:border-white/20 p-6 text-center select-none cursor-pointer"
      :class="dragOver ? 'bg-black/5 dark:bg-white/5 ring-2 ring-cyan-500/40' : ''"
      title="Tarik & letakkan gambar, klik area, atau paste dari clipboard"
      @click="$refs.file.click()"
      @dragenter.prevent="onDragEnter"
      @dragover.prevent="onDragOver"
      @dragleave.prevent="onDragLeave"
      @drop.prevent="onDrop"
    >
      <p class="muted mb-1">Tarik & letakkan gambar ke sini (opsional)</p>
      <p class="text-xs muted">atau klik area ini / tempel (Ctrl/⌘+V)</p>

      <input x-ref="file" type="file" accept="image/*" class="sr-only" @change="onFile">

      <!-- Preview -->
      <template x-if="preview">
        <div class="mt-4 grid grid-cols-1 gap-3">
          <img :src="preview" class="rounded-2xl max-h-72 object-contain mx-auto" alt="preview">
          <div class="text-xs muted">
            <b x-text="fileInfo.name || 'gambar'"></b> •
            <span x-text="(fileInfo.sizeKB||0).toFixed(1)+' KB'"></span>
            <template x-if="fileInfo.dim">
              <span> • <span x-text="fileInfo.dim"></span></span>
            </template>
          </div>
        </div>
      </template>
    </div>

    <!-- Status -->
    <div class="text-xs"
         :class="isValid ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
      <span x-show="!isValid">Minimal isi <b>judul atau teks</b> + <b>tag</b>. Gambar opsional.</span>
      <span x-show="isValid">Siap posting.</span>
    </div>

    <!-- Tombol -->
    <div class="flex items-center gap-2">
      <button type="button"
              @click="save()"
              class="px-4 py-2 rounded-2xl btn-accent text-white"
              :disabled="!isValid || saving"
              :class="(!isValid || saving) ? 'opacity-60 cursor-not-allowed' : ''">
        <span x-text="saving ? 'Menyimpan…' : 'Posting'"></span>
      </button>

      <button type="button"
              class="px-4 py-2 rounded-2xl btn-accent-ghost"
              @click="backUrl ? (location.href=backUrl) : history.back()">Batal</button>
    </div>

    <p class="muted text-sm">Catatan: disimpan lokal (localStorage) untuk demo.</p>
  </div>
</section>
@endsection

@push('scripts')
<script>
// helper hitung base64 size
const base64Bytes = s => Math.ceil((s.length - (s.split('=').pop()?.length || 0)) * 0.75);

// kompres opsional (punyamu tetap dipakai)
async function compressAdaptive(file,{maxWStart=1280,minW=640,qStart=0.82,qMin=0.45,targetKB=180}={}){
  const url = URL.createObjectURL(file);
  const img = await new Promise((res,rej)=>{ const im=new Image(); im.onload=()=>res(im); im.onerror=rej; im.src=url; });
  let w=Math.min(maxWStart,img.naturalWidth), q=qStart, out;
  const c=document.createElement('canvas'), ctx=c.getContext('2d');
  for(let i=0;i<14;i++){
    const sc=w/img.naturalWidth, h=Math.round(img.naturalHeight*sc);
    c.width=Math.max(1,Math.round(w)); c.height=Math.max(1,h);
    ctx.clearRect(0,0,c.width,c.height); ctx.drawImage(img,0,0,c.width,c.height);
    out=c.toDataURL('image/webp',q); if(!out.startsWith('data:image/webp')) out=c.toDataURL('image/jpeg',q);
    const kb=Math.ceil(base64Bytes(out)/1024);
    if(kb<=targetKB || (w<=minW && q<=qMin)) break;
    if(q>qMin) q=Math.max(qMin,q-0.12); else w=Math.max(minW,Math.round(w*0.82));
  }
  URL.revokeObjectURL(url); return out;
}

function GroupPostCreate(){
  const JOIN_KEY = 'hs-joined-groups';
  const CUSTOM_KEYS = ['hs-custom-groups','hs-user-groups','hs-groups-user']; // semua kemungkinan key lokal

  const slugify = s => (s||'').toString().trim().toLowerCase()
    .replace(/[^\w\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-');

  const normalize = g => ({
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
    let all=[]; for(const k of CUSTOM_KEYS){ try{ all=all.concat(JSON.parse(localStorage.getItem(k)||'[]')); }catch{} }
    return all.map(normalize);
  };

  return {
    group:null,
    backUrl:'/group',

    title:'', textRaw:'', tagsRaw:'',
    preview:null, fileData:null, fileInfo:{},
    dragOver:false, saving:false,

    get isValid(){
      const hasContent = (this.title?.trim() || this.textRaw?.trim());
      const hasTags = !!this.tagsRaw?.trim();
      return !!(hasContent && hasTags);
    },

    async init(){
      // slug = /group/{slug}/post  → ambil index 1
      const parts = location.pathname.split('/').filter(Boolean); // ['group','{slug}','post']
      const slug  = parts[1]; 
      this.backUrl = '/group/'+slug;

      // 1) ambil grup JSON
      let base=[]; try{ base = await (await fetch('/data/groups.json')).json(); }catch{}
      base = (base||[]).map(normalize);

      // 2) ambil grup lokal
      const mine = loadLocalGroups();

      // 3) gabung & cari
      const merged = [...mine, ...base];
      const g = merged.find(x => (x.slug||'') === slug);

      if(!g){
        window.toast?.('Grup tidak ditemukan'); 
        location.replace('/group'); 
        return;
      }
      this.group = g;

      // 4) wajib sudah join
      const joined = new Set(JSON.parse(localStorage.getItem(JOIN_KEY) || '[]'));
      if(!joined.has(slug)){
        window.toast?.('Gabung dulu untuk posting');
        location.replace(this.backUrl);
        return;
      }

      // guard DnD global (biar gak buka file di tab)
      const guard=(e)=>{ const z=e.target.closest?.('[data-dropzone]'); if(!z) e.preventDefault(); };
      window.addEventListener('dragover', guard); 
      window.addEventListener('drop', guard);

      // paste image
      window.addEventListener('paste',(e)=>{
        if(this.fileData) return;
        const items = e.clipboardData?.items || [];
        for(const it of items){
          if(it.type?.startsWith?.('image/')){
            const f=it.getAsFile(); if(f){ this.handleFile(f); break; }
          }
        }
      },{passive:true});
    },

    onDragEnter(){ this.dragOver=true; },
    onDragOver(){ this.dragOver=true; },
    onDragLeave(e){ if(!e.currentTarget.contains(e.relatedTarget)) this.dragOver=false; },
    async onDrop(e){
      this.dragOver=false; const dt=e.dataTransfer; let f=null;
      if(dt?.items?.length){ for(const it of dt.items){ if(it.kind==='file'){ const g=it.getAsFile(); if(g && g.type.startsWith('image/')){ f=g; break; } } } }
      if(!f && dt?.files?.length){ const g=dt.files[0]; if(g && g.type.startsWith('image/')) f=g; }
      if(!f){ window.toast?.('File bukan gambar'); return; }
      this.handleFile(f);
    },
    onFile(e){ const f=e.target.files?.[0]; if(f) this.handleFile(f); e.target.value=''; },

    async handleFile(file){
      try{
        const dataUrl = await compressAdaptive(file,{targetKB:180});
        this.preview=dataUrl; this.fileData=dataUrl;
        const dim=await new Promise(res=>{ const im=new Image(); im.onload=()=>res(`${im.naturalWidth}×${im.naturalHeight}px`); im.src=dataUrl; });
        this.fileInfo={name:file.name,sizeKB:Math.ceil(base64Bytes(dataUrl)/1024),dim};
      }catch{
        const r=new FileReader(); r.onload=()=>{ this.preview=r.result; this.fileData=r.result; this.fileInfo={name:file.name,sizeKB:Math.ceil(file.size/1024)}; };
        r.readAsDataURL(file);
      }
    },

    save(){
      if(!this.isValid || !this.group || this.saving) return;
      this.saving=true;

      const key='hs-gposts-'+this.group.slug;
      let list=[]; try{ list=JSON.parse(localStorage.getItem(key)||'[]'); }catch{}

      const hasImage = !!this.fileData;
      const post = {
        id:'gpost-'+Date.now(),
        group:this.group.slug,
        type: hasImage ? 'image' : 'text',
        title:(this.title||'').trim(),
        text:(this.textRaw||'').trim(),
        image: this.fileData || null,
        author: window.CURRENT_USER?.username || 'guest',
        avatar: window.CURRENT_USER?.avatar || '/images/demo/rina.jpg',
        tags:(this.tagsRaw||'').split(',').map(s=>s.trim()).filter(Boolean),
        date:new Date().toISOString().slice(0,10),
        likes:0
      };

      list.unshift(post);
      localStorage.setItem(key, JSON.stringify(list));
      window.toast?.('Post berhasil ditambahkan');
      location.assign(this.backUrl);
    }
  }
}
</script>
@endpush
