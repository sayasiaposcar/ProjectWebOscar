@extends('layouts.app')
@section('title','Buat Post | HobiSpace')

@section('content')
<section x-data="HS_Submit()" x-init="init()" class="max-w-xl mx-auto">
  <div class="glass rounded-2xl p-4 sm:p-6 space-y-4">
    <h1 class="text-2xl font-bold">Unggah (dummy) karya</h1>

    <!-- Judul (wajib) -->
    <input x-model.trim="title"
           class="w-full rounded-xl px-3 py-2 border"
           placeholder="Judul (wajib)">

    <!-- Tag (wajib) -->
    <input x-model.trim="tagsRaw"
           class="w-full rounded-xl px-3 py-2 border"
           placeholder="Tag (pisah koma) – wajib">

    <!-- Dropzone -->
    <div
      data-dropzone
      class="rounded-2xl border-2 border-dashed border-slate-300 dark:border-white/20 p-5 text-center cursor-pointer select-none"
      :class="dragOver ? 'bg-black/5 dark:bg-white/5 ring-2 ring-cyan-500/40' : ''"
      title="Klik untuk pilih file, atau tarik & letakkan"
      @click="$refs.file.click()"
      @dragenter.prevent="onDragEnter"
      @dragover.prevent="onDragOver"
      @dragleave.prevent="onDragLeave"
      @drop.prevent="onDrop"
    >
      <p class="muted mb-2">Tarik & letakkan gambar ke sini, klik untuk memilih, atau paste dari clipboard.</p>
      <input x-ref="file" type="file" accept="image/*" class="sr-only" @change="onFile">
      <button type="button" class="px-3 py-1.5 rounded-xl btn-accent text-white" @click="$refs.file.click()">Pilih Gambar</button>

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

    <!-- Status validasi -->
    <div class="text-xs"
         :class="isValid ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
      <span x-show="!isValid">Lengkapi judul, tag, dan gambar untuk mengunggah.</span>
      <span x-show="isValid">Siap unggah.</span>
    </div>

    <!-- Tombol aksi -->
    <div class="flex items-center gap-2">
      <button type="button"
        @click="save()"
        class="px-4 py-2 rounded-2xl btn-accent text-white"
        :disabled="!isValid || saving"
        :class="(!isValid || saving) ? 'opacity-60 cursor-not-allowed' : ''">
        <span x-text="saving ? 'Menyimpan…' : 'Tambahkan ke feed (lokal)'"></span>
      </button>

      <button type="button" class="px-4 py-2 rounded-2xl btn-accent-ghost" @click="resetForm()" :disabled="saving">Reset</button>
    </div>

    <p class="muted text-sm">Catatan: disimpan di browser (localStorage). Untuk demo tanpa backend.</p>
  </div>
</section>
@endsection

@push('scripts')
<script>
/* ========= URL absolut Explore (AMAN untuk subfolder/public) ========= */
const EXPLORE_URL = @json(url('/explore'));

/* ========= Utilities ========= */
// hitung bytes dari base64 dataURL
const base64Bytes = s => Math.ceil((s.length - (s.split('=').pop()?.length || 0)) * 0.75);

// kompres adaptif → cobalah mencapai targetKB (fallback turunkan dimensi/quality)
async function compressAdaptive(file, {maxWStart=1280, minW=640, qStart=0.82, qMin=0.45, targetKB=180}={}){
  const url = URL.createObjectURL(file);
  const img = await new Promise((res,rej)=>{ const im=new Image(); im.onload=()=>res(im); im.onerror=rej; im.src=url; });

  let w = Math.min(maxWStart, img.naturalWidth);
  let q = qStart;
  let out;

  const c = document.createElement('canvas'); const ctx = c.getContext('2d');

  for(let i=0;i<14;i++){
    const sc = w / img.naturalWidth;
    const h = Math.round(img.naturalHeight * sc);
    c.width = Math.max(1, Math.round(w));
    c.height = Math.max(1, h);
    ctx.clearRect(0,0,c.width,c.height);
    ctx.drawImage(img, 0, 0, c.width, c.height);

    out = c.toDataURL('image/webp', q);
    if(!out.startsWith('data:image/webp')) out = c.toDataURL('image/jpeg', q);

    const kb = Math.ceil(base64Bytes(out) / 1024);
    if (kb <= targetKB || (w <= minW && q <= qMin)) break;

    if (q > qMin) q = Math.max(qMin, q - 0.12);
    else w = Math.max(minW, Math.round(w * 0.82));
  }

  URL.revokeObjectURL(url);
  return out;
}

// tulis JSON dengan purge LRU kalau quota penuh
function setJSONWithLRU(key, list, {maxPurge=80}={}){
  for (let i=0;i<=maxPurge;i++){
    try{ localStorage.setItem(key, JSON.stringify(list)); return true; }
    catch{ if(!list.length) return false; list.pop(); }
  }
  return false;
}

/* ========= Alpine component (DIBERI NAMA UNIK agar tidak tabrakan) ========= */
function HS_Submit(){
  const KEY='hs-custom-posts';

  return {
    // state
    title:'', tagsRaw:'', preview:null, fileData:null, fileInfo:{},
    dragOver:false, saving:false,

    // computed
    get isValid(){ return !!(this.title?.trim() && this.tagsRaw?.trim() && this.fileData); },

    /* ---- init: pasang guard DnD + paste ---- */
    init(){
      // Guard global: cegah browser open file saat drop di luar dropzone
      const guard=(e)=>{ const z=e.target.closest?.('[data-dropzone]'); if(!z) e.preventDefault(); };
      window.addEventListener('dragover', guard);
      window.addEventListener('drop', guard);

      // Paste handler: kalau ada image di clipboard → pakai
      window.addEventListener('paste', (e)=>{
        if (this.fileData) return; // sudah ada gambar, abaikan
        const items = e.clipboardData?.items || [];
        for(const it of items){
          if(it.type?.startsWith?.('image/')){
            const f = it.getAsFile();
            if (f) { this.handleFile(f); break; }
          }
        }
      }, { passive:true });
    },

    /* ---- DnD handlers ---- */
    onDragEnter(){ this.dragOver = true; },
    onDragOver(){ this.dragOver = true; },
    onDragLeave(e){ if(!e.currentTarget.contains(e.relatedTarget)) this.dragOver=false; },
    async onDrop(e){
      this.dragOver = false;
      let f=null;

      // item-based (lebih akurat untuk DnD)
      if (e.dataTransfer?.items?.length){
        for(const it of e.dataTransfer.items){
          if(it.kind==='file'){
            const g=it.getAsFile();
            if(g && g.type.startsWith('image/')) { f=g; break; }
          }
        }
      }

      // fallback ke files
      if(!f && e.dataTransfer?.files?.length){
        const g=e.dataTransfer.files[0];
        if(g && g.type.startsWith('image/')) f=g;
      }

      if(!f){ window.toast?.('File bukan gambar.'); return; }
      this.handleFile(f);
    },

    /* ---- File input ---- */
    onFile(e){
      const f = e.target.files?.[0];
      if (f) this.handleFile(f);
      e.target.value=''; // Reset supaya file yang sama bisa dipilih ulang
    },

    /* ---- Proses file (kompres + preview) ---- */
    async handleFile(file){
      try{
        const dataUrl = await compressAdaptive(file,{targetKB:180});
        this.preview = dataUrl;
        this.fileData = dataUrl;
        const dim = await new Promise((res)=>{ const im=new Image(); im.onload=()=>res(`${im.naturalWidth}×${im.naturalHeight}px`); im.src=dataUrl; });
        this.fileInfo = { name:file.name, sizeKB: Math.ceil(base64Bytes(dataUrl)/1024), dim };
      }catch{
        // fallback: tanpa kompres
        const r=new FileReader();
        r.onload=()=>{ this.preview=r.result; this.fileData=r.result; this.fileInfo={ name:file.name, sizeKB: Math.ceil(file.size/1024) }; };
        r.readAsDataURL(file);
      }
    },

    /* ---- Simpan + Redirect pakai URL absolut ---- */
    async save(){
      if(!this.isValid || this.saving) return;
      this.saving = true;

      try{
        let list = [];
        try{ list = JSON.parse(localStorage.getItem(KEY)||'[]'); }catch{}

        const post = {
          id: Date.now(),
          title: (this.title||'').trim(),
          image: this.fileData,
          author: 'guest',
          avatar: '/images/demo/rina.jpg',
          likes: 0,
          tags: (this.tagsRaw||'').split(',').map(s=>s.trim()).filter(Boolean),
          desc: 'Karya pengguna (local)',
          date: new Date().toISOString().slice(0,10)
        };

        list.unshift(post);
        const ok = setJSONWithLRU(KEY, list, {maxPurge:80});
        if(!ok) throw new Error('QuotaExceeded');

        window.toast?.('Karya ditambahkan!');
        // Redirect aman (pakai URL absolut dari Laravel)
        // 1) assign (update history)
        window.location.assign(EXPLORE_URL);
        // 2) fallback hard-replace jika browser menahan assign karena sesuatu
        setTimeout(()=>{ if(location.href !== EXPLORE_URL){ window.location.replace(EXPLORE_URL); } }, 200);
        // 3) safety net terakhir via anchor click (jarang perlu, tapi aman)
        setTimeout(()=>{
          if(location.href !== EXPLORE_URL){
            const a=document.createElement('a');
            a.href=EXPLORE_URL; a.rel='nofollow'; document.body.appendChild(a); a.click();
          }
        }, 400);

      }catch(e){
        console.error(e);
        let s=[]; try{s=JSON.parse(localStorage.getItem(KEY)||'[]');}catch{}
        if(s?.length){
          // kalau sebenarnya tersimpan (setelah purge), tetap redirect
          window.location.assign(EXPLORE_URL);
          setTimeout(()=>{ if(location.href !== EXPLORE_URL){ window.location.replace(EXPLORE_URL); } }, 200);
        }else{
          alert('Gagal menyimpan (kuota localStorage penuh). Coba gambar lebih kecil atau reset data lokal.');
        }
      }finally{
        this.saving = false;
      }
    },

    /* ---- Reset form ---- */
    resetForm(){
      this.title=''; this.tagsRaw=''; this.preview=null; this.fileData=null; this.fileInfo={};
    }
  };
}
</script>
@endpush
