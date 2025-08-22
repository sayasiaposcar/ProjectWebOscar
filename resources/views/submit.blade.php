@extends('layouts.app')
@section('title','Buat Post | HobiSpace')

@section('content')
<section x-data="SubmitPage()" class="max-w-xl mx-auto">
  <div class="glass rounded-2xl p-4 sm:p-6 space-y-3">
    <h1 class="text-2xl font-bold">Unggah (dummy) karya</h1>
    <input x-model="title" class="w-full rounded-xl px-3 py-2 border" placeholder="Judul">
    <input x-model="tagsRaw" class="w-full rounded-xl px-3 py-2 border" placeholder="Tag (pisah koma)">
    <input type="file" @change="onFile" accept="image/*" class="w-full">
    <button @click="save()" class="px-4 py-2 rounded-xl btn-accent text-white">Tambahkan ke feed (lokal)</button>
    <p class="muted text-sm">Catatan: disimpan di browser (localStorage). Untuk demo tanpa backend.</p>
    <img x-show="preview" :src="preview" class="rounded-2xl mt-2">
  </div>
</section>
@endsection

@push('scripts')
<script>
  async function compressImageToDataURL(file, maxW=1280, quality=0.82){
    return new Promise((resolve, reject)=>{
      const img = new Image(); const r = new FileReader();
      r.onload = () => img.src = r.result; r.onerror = reject;
      img.onload = () => { const s=Math.min(1,maxW/img.width); const w=img.width*s, h=img.height*s;
        const c=document.createElement('canvas'); c.width=w; c.height=h; c.getContext('2d').drawImage(img,0,0,w,h);
        resolve(c.toDataURL('image/jpeg',quality)); };
      img.onerror = reject; r.readAsDataURL(file);
    });
  }
  function SubmitPage(){
    return {
      title:'', tagsRaw:'', preview:null, fileData:null,
      onFile(e){
        const f=e.target.files[0]; if(!f) return;
        compressImageToDataURL(f).then(d=>{this.preview=d; this.fileData=d;})
          .catch(()=>{ const r=new FileReader(); r.onload=()=>{this.preview=r.result; this.fileData=r.result}; r.readAsDataURL(f); });
      },
      async save(){
        const post = { id:Date.now(), title:this.title||'Tanpa judul', image:this.fileData,
          author:'guest', avatar:'/images/demo/rina.jpg', likes:0,
          tags:this.tagsRaw.split(',').map(s=>s.trim()).filter(Boolean),
          desc:'Karya pengguna (local)', date:new Date().toISOString().slice(0,10) };
        try{
          const key='hs-custom-posts'; const list=JSON.parse(localStorage.getItem(key)||'[]'); list.unshift(post);
          localStorage.setItem(key, JSON.stringify(list)); window.toast('Karya ditambahkan!'); setTimeout(()=>location.href='/explore',300);
        }catch(err){ alert('Gagal menyimpan (kuota localStorage penuh). Coba gambar lebih kecil.'); console.error(err); }
      }
    }
  }
</script>
@endpush
