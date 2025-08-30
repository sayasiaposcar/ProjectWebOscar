@extends('layouts.app')
@section('title','Buat Grup | HobiSpace')

@section('content')
<section x-data="GroupCreate()" x-init="init()" class="max-w-3xl mx-auto">
  <div class="glass rounded-2xl p-5 sm:p-6 space-y-5">
    <h1 class="text-2xl font-bold">Buat Grup (dummy)</h1>

    <!-- Nama + Slug -->
    <div class="grid sm:grid-cols-2 gap-3">
      <div class="space-y-1.5">
        <label class="text-sm muted">Nama Grup</label>
        <input x-model.trim="form.name" @input="autoSlug()" class="w-full rounded-xl px-3 py-2 border" placeholder="Nama grup">
      </div>
      <div class="space-y-1.5">
        <label class="text-sm muted">Slug</label>
        <input x-model.trim="form.slug" class="w-full rounded-xl px-3 py-2 border" placeholder="contoh: fotografi-kreatif">
      </div>
    </div>

    <!-- Owner + Handle -->
    <div class="grid sm:grid-cols-2 gap-3">
      <div class="space-y-1.5">
        <label class="text-sm muted">Owner</label>
        <input x-model.trim="form.owner" class="w-full rounded-xl px-3 py-2 border" placeholder="Nama owner">
      </div>
      <div class="space-y-1.5">
        <label class="text-sm muted">Handle</label>
        <input x-model.trim="form.handle" class="w-full rounded-xl px-3 py-2 border" placeholder="username owner">
      </div>
    </div>

    <!-- Deskripsi -->
    <div class="space-y-1.5">
      <label class="text-sm muted">Deskripsi</label>
      <textarea x-model.trim="form.desc" class="w-full rounded-xl px-3 py-2 border min-h-28" placeholder="Tentang grup…"></textarea>
    </div>

    <!-- Tags -->
    <div class="space-y-1.5">
      <label class="text-sm muted">Tag (pisah koma)</label>
      <input x-model.trim="form.tags" class="w-full rounded-xl px-3 py-2 border" placeholder="mis. fotografi, kota, sunset">
    </div>

    <!-- Avatar & Cover - DnD -->
    <div class="grid sm:grid-cols-2 gap-4">
      <!-- AVATAR -->
      <div>
        <label class="text-sm muted block mb-1.5">Avatar</label>
        <div
          class="rounded-2xl border-2 border-dashed p-4 text-center select-none cursor-pointer"
          :class="dz.avatar.over ? 'ring-2 ring-cyan-500/40 bg-black/5 dark:bg-white/5' : 'border-slate-300 dark:border-white/20'"
          @click="$refs.avatarInput.click()"
          @dragenter.prevent="dz.avatar.over = true"
          @dragover.prevent="dz.avatar.over = true"
          @dragleave.prevent="dz.avatar.over = false"
          @drop.prevent="onDrop($event,'avatar')">
          <p class="muted">Tarik & lepas gambar, klik area, atau pilih</p>
          <input x-ref="avatarInput" type="file" accept="image/*" class="sr-only" @change="onFile($event, 'avatar')">
          <template x-if="form.avatar">
            <img :src="form.avatar" class="mt-3 w-28 h-28 object-cover rounded-xl mx-auto" alt="avatar">
          </template>
        </div>
      </div>

      <!-- COVER -->
      <div>
        <label class="text-sm muted block mb-1.5">Cover</label>
        <div
          class="rounded-2xl border-2 border-dashed p-4 text-center select-none cursor-pointer"
          :class="dz.cover.over ? 'ring-2 ring-cyan-500/40 bg-black/5 dark:bg-white/5' : 'border-slate-300 dark:border-white/20'"
          @click="$refs.coverInput.click()"
          @dragenter.prevent="dz.cover.over = true"
          @dragover.prevent="dz.cover.over = true"
          @dragleave.prevent="dz.cover.over = false"
          @drop.prevent="onDrop($event,'cover')">
          <p class="muted">Tarik & lepas gambar, klik area, atau pilih</p>
          <input x-ref="coverInput" type="file" accept="image/*" class="sr-only" @change="onFile($event, 'cover')">
          <template x-if="form.cover">
            <img :src="form.cover" class="mt-3 w-full h-28 object-cover rounded-xl mx-auto" alt="cover">
          </template>
        </div>
      </div>
    </div>

    

    <!-- Aksi -->
    <div class="flex items-center gap-2 pt-1">
      <button type="button"
              class="px-4 py-2 rounded-2xl btn-accent text-white"
              :disabled="!isValid || saving"
              :class="(!isValid || saving) ? 'opacity-60 cursor-not-allowed' : ''"
              @click="save()">
        <span x-text="saving ? 'Membuat…' : 'Simpan'"></span>
      </button>
      <button type="button" class="px-4 py-2 rounded-2xl btn-accent-ghost" @click="history.back()">Batal</button>
    </div>

    <p class="muted text-sm">Catatan: disimpan lokal (localStorage) untuk demo.</p>
  </div>
</section>
@endsection

@push('scripts')
<script>
function GroupCreate(){
  const STORAGE_KEY = 'hs-custom-groups';

  const slugify = (s='') =>
    (s||'').toString().trim().toLowerCase()
      .replace(/[^\w\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-');

  const toDataUrl = (file) =>
    new Promise((res, rej) => {
      const r = new FileReader();
      r.onload = () => res(r.result);
      r.onerror = rej;
      r.readAsDataURL(file);
    });

  return {
    saving:false,
    dz:{ avatar:{over:false}, cover:{over:false} },
    form:{
      name:'', slug:'', owner:'', handle:'',
      desc:'', tags:'', avatar:'', cover:'',
      is_public:true, created_at: new Date().toISOString().slice(0,10),
      members:1
    },

    get isValid(){
      return !!(this.form.name?.trim() && this.form.slug?.trim());
    },

    init(){ /* no global drag guard → biar DnD di halaman lain gak keikut */ },

    autoSlug(){ if(!this.form.slug || this.form.slug === slugify(this.form.slug)) this.form.slug = slugify(this.form.name); },

    async onFile(e, key){
      const f = e.target.files?.[0];
      if(!f) return;
      this.form[key] = await toDataUrl(f);
      e.target.value='';
    },

    async onDrop(e, key){
      this.dz[key].over = false;
      const dt = e.dataTransfer;
      let f = null;
      if (dt?.items?.length){
        for(const it of dt.items){
          if (it.kind === 'file') { const g = it.getAsFile(); if(g && g.type.startsWith('image/')) { f=g; break; } }
        }
      }
      if (!f && dt?.files?.length) { const g = dt.files[0]; if(g && g.type.startsWith('image/')) f=g; }
      if (!f) { window.toast?.('File bukan gambar'); return; }
      this.form[key] = await toDataUrl(f);
    },

    save(){
      if(!this.isValid || this.saving) return;
      this.saving = true;

      // normalisasi & simpan
      const data = {
        id:'g-'+Date.now(),
        slug: slugify(this.form.slug),
        name: this.form.name.trim(),
        owner: this.form.owner.trim() || 'Creator',
        handle: this.form.handle.trim() || 'user',
        desc: this.form.desc || '',
        tags: (this.form.tags||'').split(',').map(s=>s.trim()).filter(Boolean),
        avatar: this.form.avatar || '',
        cover: this.form.cover || '',
        created_at: this.form.created_at,
        members: Number(this.form.members||1),
        is_public: !!this.form.is_public
      };

      let list = [];
      try { list = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); } catch {}
      list.unshift(data);
      localStorage.setItem(STORAGE_KEY, JSON.stringify(list));

      // auto tandai joined agar bisa langsung masuk
      const JOIN_KEY='hs-joined-groups';
      const joined = new Set(JSON.parse(localStorage.getItem(JOIN_KEY) || '[]'));
      joined.add(data.slug);
      localStorage.setItem(JOIN_KEY, JSON.stringify([...joined]));

      window.toast?.('Grup berhasil dibuat');
      location.assign('/group/' + data.slug);
    }
  }
}
</script>

<style>
/* toggle mini (biar gak mengganggu) */
.toggle { width: 36px; height: 20px; appearance: none; background: #374151; border-radius: 9999px; position: relative; outline: none; cursor: pointer; }
.toggle:checked { background: #06b6d4; }
.toggle::after { content:''; position:absolute; top:2px; left:2px; width:16px; height:16px; background:#fff; border-radius:9999px; transition:left .15s ease; }
.toggle:checked::after { left: 18px; }
</style>
@endpush
