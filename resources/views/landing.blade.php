@extends('layouts.app')
@section('title','HobiSpace – Komunitas Hobi')

@section('content')
<section class="grid lg:grid-cols-[1.1fr,1fr] gap-8 items-center">
  <div class="space-y-4">
    <div class="inline-flex items-center gap-2 px-3 py-1 chip rounded-full text-sm">✨ Versi demo front‑end only</div>
    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">
      Temukan & bagikan karya <span class="text-indigo-600">komunitas hobi</span>
    </h1>
    <p class="muted">Fotografi, gaming art, musik, dan banyak lagi. Tanpa login – cocok untuk demo lomba.</p>
    <div class="flex gap-3">
      <a href="/explore" class="px-5 py-3 rounded-2xl bg-indigo-600 text-white hover:bg-indigo-700 shadow">Jelajahi Karya</a>
      <a href="/submit" class="px-5 py-3 rounded-2xl glass">Submit (dummy)</a>
    </div>
    <p class="muted text-sm">Data dummy dari JSON + karya lokal di browser (localStorage).</p>
  </div>
  <div class="glass rounded-3xl p-2">
    <img src="/images/demo/hero-grid.jpg" onerror="this.style.display='none'" class="rounded-2xl w-full h-auto object-cover" alt="Kolase karya">
  </div>
</section>
@endsection
