{{-- resources/views/messages.blade.php --}}
@extends('layouts.app')
@section('title', 'Pesan | HobiSpace')

@section('content')
<section x-data="MessagesPage()" x-init="init()" class="grid grid-cols-1 md:grid-cols-[340px,minmax(0,1fr),320px] gap-4">

  <!-- ============ LEFT: LIST CHAT ============ -->
  <aside class="glass rounded-2xl overflow-hidden h-[76vh] md:h-[78vh] grid grid-rows-[auto,auto,1fr,auto]">
    <!-- Header kiri -->
    <div class="px-3.5 pt-3 flex items-center gap-2">
      <div class="font-semibold text-lg">Pesan</div>
      <div class="ml-auto flex items-center gap-2">
        <button class="px-2.5 py-1.5 rounded-lg btn-accent-ghost text-sm" @click="showArchived=!showArchived">
          Arsip <span class="opacity-70" x-text="showArchived?'ON':'OFF'"></span>
        </button>
        <!-- menu ⋮ kiri -->
        <div class="relative" x-data="{o:false}">
          <button class="px-2.5 py-1.5 rounded-lg btn-accent-ghost" @click="o=!o" @click.away="o=false" title="Menu">⋮</button>
          <div x-show="o" x-cloak class="absolute right-0 mt-2 w-52 glass rounded-xl border border-white/10 p-1 text-sm z-10">
            <button class="w-full px-3 py-2 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 text-left"
                    @click="markAllRead(); o=false">Tandai semua dibaca</button>
            <button class="w-full px-3 py-2 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 text-left"
                    @click="exportAll(); o=false">Ekspor semua chat (.json)</button>
            <label class="block px-3 py-2 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 text-left cursor-pointer">
              Impor chat (.json)
              <input type="file" class="sr-only" accept="application/json" @change="importAll">
            </label>
            <button class="w-full px-3 py-2 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 text-left"
                    @click="autoReply=!autoReply; persist('hs-dm-autoreply', autoReply); o=false">
              Balas otomatis: <span x-text="autoReply?'ON':'OFF'"></span>
            </button>
            <button class="w-full px-3 py-2 rounded-lg hover:bg-black/5 dark:hover:bg:white/5 text-left"
                    @click="chatFont=chatFont==='base'?'lg':'base'; persist('hs-dm-font', chatFont); o=false">
              Ukuran teks: <span x-text="chatFont==='base'?'Normal':'Besar'"></span>
            </button>
            <button class="w-full px-3 py-2 rounded-lg hover:bg-black/5 dark:hover:bg:white/5 text-left text-rose-600"
                    @click="clearAll(); o=false">Hapus semua</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pencarian list threads -->
    <div class="px-3.5 pb-3">
      <div class="flex items-center gap-2 glass px-2 py-1.5 rounded-xl">
        <svg class="w-4 h-4 opacity-60" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a8 8 0 1 1 5.292-14.03l4.869 4.869-1.414 1.414-4.869-4.869A8 8 0 0 1 10 18z"/></svg>
        <input class="bg-transparent outline-none text-sm w-full" placeholder="Cari orang atau pesan…" x-model.trim="q">
      </div>
    </div>

    <!-- Daftar thread -->
    <div class="overflow-auto divide-y divide-white/5 no-scrollbar">
      <template x-for="t in filtered()" :key="t.id">
        <button class="w-full text-left px-3.5 py-3 hover:bg-black/5 dark:hover:bg-white/5"
                :class="activeId===t.id ? 'bg-black/5 dark:bg-white/5' : ''"
                @click="openThread(t)">
          <div class="flex items-center gap-3">
            <img :src="avatarOf(otherOf(t))" class="w-10 h-10 rounded-full object-cover" alt="">
            <div class="min-w-0">
              <div class="flex items-center gap-1">
                <div class="text-sm font-semibold truncate" x-text="nameOf(otherOf(t))"></div>
                <span class="inline-block w-2 h-2 rounded-full"
                      :class="isOnline(otherOf(t))?'bg-emerald-500/70':'bg-gray-400/40'"
                      :title="isOnline(otherOf(t))?'online':'offline'"></span>
                <span class="ml-1 text-[12px]" x-show="t.pinned" title="Disematkan">📌</span>
              </div>
              <div class="text-xs muted truncate" x-text="t.lastMsg || 'Mulai obrolan'"></div>
            </div>
            <div class="ml-auto text-right">
              <div class="text-[11px]" x-text="t.lastAt?timefmt(t.lastAt):''"></div>
              <span class="badge" x-show="t.unread>0" x-text="t.unread"></span>
            </div>
          </div>
        </button>
      </template>
      <div x-show="filtered().length===0" class="p-6 text-sm muted text-center">Tidak ada percakapan.</div>
    </div>

    <!-- New chat -->
    <div class="px-3.5 py-3 border-t border-white/10">
      <button class="w-full px-3 py-2 rounded-xl btn-accent text-white" @click="openPicker('new')">+ Chat baru</button>
    </div>
  </aside>

  <!-- ============ MIDDLE: PANEL CHAT ============ -->
  <div class="glass rounded-2xl h-[76vh] md:h-[78vh] overflow-hidden grid grid-rows-[auto,1fr,auto]"
       :class="mobileView!=='chat' && isMobile ? 'hidden md:grid' : ''"
       @dragover.prevent
       @drop.prevent="onDrop($event)">

    <!-- Header chat -->
    <div class="px-4 py-3 border-b border-white/10 flex items-center gap-3">
      <button class="md:hidden px-2 py-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5" @click="mobileView='list'">←</button>

      <img :src="activeMeta.avatar" class="w-10 h-10 rounded-full object-cover" alt="">
      <div class="min-w-0">
        <div class="font-semibold truncate" x-text="activeMeta.name || 'Pilih chat'"></div>
        <div class="text-[11px] muted">
          <template x-if="activeId">
            <span>
              <span x-show="typing.remote">sedang mengetik… • </span>
              <span x-text="lastSeenText(activeOther)"></span>
            </span>
          </template>
        </div>
      </div>

      <!-- Cari di chat -->
      <div class="hidden sm:flex items-center gap-2 glass px-2 py-1.5 rounded-xl ml-2" :class="!activeId?'opacity-50 pointer-events-none':''">
        <svg class="w-4 h-4 opacity-60" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a8 8 0 1 1 5.292-14.03l4.869 4.869-1.414 1.414-4.869-4.869A8 8 0 0 1 10 18z"/></svg>
        <input class="bg-transparent outline-none text-sm w-44" placeholder="Cari di chat…" x-model.trim="chatQ" @keydown.enter.prevent="gotoNextMatch()">
        <div class="text-[11px] muted" x-show="activeId && chatQ">(<span x-text="matchIndex+1"></span>/<span x-text="matches.length"></span>)</div>
        <button class="px-2 py-1 rounded-lg btn-accent-ghost" @click="gotoPrevMatch()" :disabled="!matches.length">↑</button>
        <button class="px-2 py-1 rounded-lg btn-accent-ghost" @click="gotoNextMatch()" :disabled="!matches.length">↓</button>
      </div>

      <div class="ml-auto flex items-center gap-2">
        <!-- Selection count -->
        <div class="badge" x-show="selectionMode" x-text="selectedCount() + ' dipilih'"></div>

        <span class="badge" x-show="!selectionMode && unreadActive>0" x-text="unreadActive + ' baru'"></span>

        <!-- menu ⋮ kanan -->
        <div class="relative" x-data="{o:false}">
          <button class="px-2.5 py-1.5 rounded-lg btn-accent-ghost" :disabled="!activeId" @click="o=!o" @click.away="o=false">⋮</button>
          <div x-show="o" x-cloak class="absolute right-0 mt-2 w-56 glass rounded-xl border border-white/10 p-1 text-sm z-10">
            <button class="w-full px-3 py-2 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 text-left"
                    :disabled="!activeId" @click="toggleArchive(activeId); o=false"
                    x-text="isArchived(activeId)?'Keluarkan dari Arsip':'Arsipkan chat'"></button>
            <button class="w-full px-3 py-2 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 text-left"
                    :disabled="!activeId" @click="togglePin(activeId); o=false"
                    x-text="isPinned(activeId)?'Lepas semat':'Sematkan di atas'"></button>
            <button class="w-full px-3 py-2 rounded-lg hover:bg-black/5 dark:hover:bg:white/5 text-left"
                    :disabled="!activeId" @click="markRead(activeId); o=false">Tandai dibaca</button>

            <div class="px-3 py-2">
              <div class="text-[11px] muted mb-1">Mute notifikasi</div>
              <div class="grid grid-cols-4 gap-1">
                <button class="px-2 py-1 rounded btn-accent-ghost text-[11px]" :disabled="!activeId" @click="muteFor(60)">1j</button>
                <button class="px-2 py-1 rounded btn-accent-ghost text-[11px]" :disabled="!activeId" @click="muteFor(8*60)">8j</button>
                <button class="px-2 py-1 rounded btn-accent-ghost text-[11px]" :disabled="!activeId" @click="muteFor(24*60*7)">1mgg</button>
                <button class="px-2 py-1 rounded btn-accent-ghost text-[11px]" :disabled="!activeId" @click="muteFor(0)">Off</button>
              </div>
            </div>

            <button class="w-full px-3 py-2 rounded-lg hover:bg-black/5 dark:hover:bg:white/5 text-left"
                    :disabled="!activeId" @click="exportChat(activeId); o=false">Ekspor chat ini</button>

            <div class="border-t border-white/10 my-1"></div>
            <button class="w-full px-3 py-2 rounded-lg hover:bg-black/5 dark:hover:bg:white/5 text-left"
                    :disabled="!activeId" @click="selectionMode=true; o=false">Pilih pesan…</button>

            <button class="w-full px-3 py-2 rounded-lg hover:bg-black/5 dark:hover:bg:white/5 text-left text-rose-600"
                    :disabled="!activeId" @click="deleteThread(activeId); o=false">Hapus chat</button>
          </div>
        </div>
      </div>
    </div>

    <!-- List pesan -->
    <div class="overflow-auto p-0 bg-chat">
      <div class="bg-fade -m-0 p-3 min-h-full" x-ref="scroll">
        <template x-if="!activeId">
          <div class="muted text-sm text-center mt-10">Pilih percakapan di kiri atau mulai chat baru.</div>
        </template>

        <!-- tombol muat sebelumnya (virtualized) -->
        <div class="text-center my-2" x-show="activeId && items.length>visibleCount">
          <button class="px-3 py-1.5 rounded-lg btn-accent-ghost text-sm" @click="visibleCount+=40">Muat sebelumnya</button>
        </div>

        <template x-for="m in visibleItems()" :key="m.id">
          <div :class="m.from===me ? 'text-right' : 'text-left'">
            <div class="inline-block max-w-[88%] align-top group relative"
                 :class="m.from===me ? 'rounded-br-sm' : 'rounded-bl-sm'"
                 :id="'m-'+m.id"
                 @touchstart.passive="swipeStart($event, m)"
                 @touchmove.passive="swipeMove($event, m)"
                 @touchend.passive="swipeEnd($event, m)"
                 @click="selectionMode ? toggleSelectMsg(m) : null">

              <!-- checkbox multi-select -->
              <input type="checkbox" class="msg-check" x-show="selectionMode" :checked="isSelected(m.id)" @change="toggleSelectMsg(m)">

              <!-- ========== TEXT ========== -->
              <template x-if="m.type==='text'">
                <div class="rounded-2xl px-3 py-2 text-sm relative"
                     :style="swipeStyle(m.id)"
                     :class="m.from===me ? 'bg-cyan-600 text-white' : 'bg-white/90 dark:bg-white/10 border border-white/10'">
                  <!-- reply preview kecil -->
                  <div class="text-[11px] mb-1 opacity-80" x-show="m.replyTo && replyPreview(m.replyTo)">
                    <div class="px-2 py-1 rounded-lg bg-black/10 dark:bg-white/5">
                      ↩︎ <span x-text="replyPreview(m.replyTo)"></span>
                    </div>
                  </div>

                  <!-- isi (dengan linkify + highlight) -->
                  <div class="whitespace-pre-wrap break-words"
                       :class="chatFont==='lg'?'text-[15.5px] leading-6':''"
                       x-html="renderText(m)"></div>

                  <!-- LINK PREVIEW -->
                  <div class="mt-2" x-show="m.preview">
                    <!-- YouTube -->
                    <template x-if="m.preview?.type==='youtube'">
                      <a class="block rounded-xl overflow-hidden border border-white/10" :href="m.preview.url" target="_blank" rel="noreferrer">
                        <img :src="m.preview.thumb" class="w-full max-w-[420px] object-cover block" alt="">
                        <div class="px-3 py-2 text-xs bg-black/5 dark:bg-white/5">
                          <div class="font-semibold line-clamp-1">YouTube</div>
                          <div class="opacity-80 line-clamp-1" x-text="m.preview.title"></div>
                        </div>
                      </a>
                    </template>
                    <!-- Gambar langsung -->
                    <template x-if="m.preview?.type==='image'">
                      <a class="inline-block rounded-xl overflow-hidden border border-white/10" :href="m.preview.url" target="_blank" rel="noreferrer">
                        <img :src="m.preview.url" class="max-h-64 object-cover block" alt="">
                      </a>
                    </template>
                    <!-- Generic -->
                    <template x-if="m.preview?.type==='link'">
                      <a class="block rounded-xl border border-white/10 px-3 py-2 text-xs bg-white/80 dark:bg-white/5 max-w-[420px]" :href="m.preview.url" target="_blank" rel="noreferrer">
                        🔗 <span class="font-semibold" x-text="m.preview.host"></span>
                        <div class="opacity-80 line-clamp-2" x-text="m.preview.path"></div>
                      </a>
                    </template>
                  </div>

                  <!-- meta -->
                  <div class="flex items-center gap-2 justify-end mt-1">
                    <!-- reaksi chips -->
                    <template x-for="(users,emo) in (m.reactions||{})" :key="emo">
                      <span class="px-1.5 py-0.5 rounded-full text-[11px] bg-black/10 dark:bg-white/10"
                            x-text="emo + ' ' + users.length"></span>
                    </template>
                    <!-- starred -->
                    <span class="text-[11px] opacity-70 mr-auto" x-show="m.starred">★</span>
                    <div class="text-[10px] opacity-70" x-text="timefmt(m.at) + (m.from===me ? (m.read?' · ✓✓':' · ✓') : '') + (m.editedAt?' · diedit':'')"></div>
                  </div>

                  <!-- actions -->
                  <div class="absolute -top-2" :class="m.from===me?'-left-2':'-right-2'"></div>
                  <div class="opacity-0 group-hover:opacity-100 transition flex gap-1 absolute"
                       :class="m.from===me ? 'right-1 -top-3' : 'left-1 -top-3'">
                    <button class="msg-btn" @click="beginReply(m)" title="Balas">↩︎</button>
                    <button class="msg-btn" @click="forwardSingle(m)" title="Teruskan">⤴︎</button>
                    <button class="msg-btn" @click="toggleStar(m)" :title="m.starred?'Hapus bintang':'Beri bintang'">★</button>
                    <div class="relative" x-data="{o:false}">
                      <button class="msg-btn" @click="o=!o" @click.away="o=false" title="Reaksi">☺️</button>
                      <div x-show="o" x-cloak class="absolute z-10 -top-10 glass rounded-xl p-1">
                        <template x-for="emo in emojis" :key="emo">
                          <button class="px-1" @click="toggleReaction(m,emo); o=false" x-text="emo"></button>
                        </template>
                      </div>
                    </div>
                    <button class="msg-btn" @click="copyText(m)" x-show="m.type==='text' && !m.deleted">⧉</button>
                    <button class="msg-btn" @click="beginEdit(m)" x-show="m.from===me && m.type==='text' && !m.deleted">✏️</button>
                    <button class="msg-btn" @click="deleteMsg(m)" x-show="m.from===me">🗑️</button>
                  </div>
                </div>
              </template>

              <!-- ========== IMAGE ========== -->
              <template x-if="m.type==='image'">
                <div class="rounded-2xl overflow-hidden border border-white/10 relative" :style="swipeStyle(m.id)">
                  <a :href="m.media?.src" target="_blank" rel="noreferrer">
                    <img :src="m.media?.src" class="max-h-72 object-cover block" alt="img">
                  </a>
                  <div class="px-2 pb-1 text-[10px] opacity-70 text-right" x-text="timefmt(m.at)"></div>
                  <div class="absolute top-1" :class="m.from===me?'right-1':'left-1'">
                    <div class="opacity-0 group-hover:opacity-100 transition flex gap-1">
                      <button class="msg-btn" @click="beginReply(m)">↩︎</button>
                      <button class="msg-btn" @click="forwardSingle(m)">⤴︎</button>
                      <button class="msg-btn" @click="toggleStar(m)">★</button>
                      <div class="relative" x-data="{o:false}">
                        <button class="msg-btn" @click="o=!o" @click.away="o=false">☺️</button>
                        <div x-show="o" x-cloak class="absolute z-10 -top-10 glass rounded-xl p-1">
                          <template x-for="emo in emojis" :key="emo">
                            <button class="px-1" @click="toggleReaction(m,emo); o=false" x-text="emo"></button>
                          </template>
                        </div>
                      </div>
                      <button class="msg-btn" @click="deleteMsg(m)" x-show="m.from===me">🗑️</button>
                    </div>
                  </div>
                </div>
              </template>

              <!-- ========== AUDIO ========== -->
              <template x-if="m.type==='audio'">
                <div class="rounded-2xl px-2 pb-1 pt-2 bg-white/90 dark:bg-white/10 border border-white/10 relative" :style="swipeStyle(m.id)">
                  <audio :src="m.media?.src" controls class="w-60"></audio>
                  <div class="text-[10px] opacity-70 mt-1 text-right" x-text="timefmt(m.at)"></div>
                  <div class="absolute -top-3" :class="m.from===me?'right-1':'left-1'">
                    <div class="opacity-0 group-hover:opacity-100 transition flex gap-1">
                      <button class="msg-btn" @click="beginReply(m)">↩︎</button>
                      <button class="msg-btn" @click="forwardSingle(m)">⤴︎</button>
                      <button class="msg-btn" @click="toggleStar(m)">★</button>
                      <button class="msg-btn" @click="deleteMsg(m)" x-show="m.from===me">🗑️</button>
                    </div>
                  </div>
                </div>
              </template>

              <!-- ========== FILE ========== -->
              <template x-if="m.type==='file'">
                <div class="rounded-2xl px-3 py-2 bg-white/90 dark:bg-white/10 border border-white/10 relative" :style="swipeStyle(m.id)">
                  <a :href="m.media?.src" :download="m.media?.name" class="underline break-all">
                    📄 <span x-text="m.media?.name"></span> <span class="opacity-60 text-[11px]" x-text="humanSize(m.media?.size)"></span>
                  </a>
                  <div class="text-[10px] opacity-70 mt-1 text-right" x-text="timefmt(m.at)"></div>
                  <div class="absolute -top-3" :class="m.from===me?'right-1':'left-1'">
                    <div class="opacity-0 group-hover:opacity-100 transition flex gap-1">
                      <button class="msg-btn" @click="beginReply(m)">↩︎</button>
                      <button class="msg-btn" @click="forwardSingle(m)">⤴︎</button>
                      <button class="msg-btn" @click="toggleStar(m)">★</button>
                      <button class="msg-btn" @click="deleteMsg(m)" x-show="m.from===me">🗑️</button>
                    </div>
                  </div>
                </div>
              </template>

              <!-- Pesan dihapus -->
              <template x-if="m.deleted">
                <div class="text-[12px] muted px-2 mt-1">Pesan dihapus</div>
              </template>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- Composer -->
    <div class="p-3 border-t border-white/10">
      <!-- reply/edit bar -->
      <div class="flex items-center gap-2 mb-2" x-show="replyTo || editId">
        <div class="glass px-2 py-1.5 rounded-xl text-sm flex-1">
          <span x-show="replyTo">Balas: <span class="opacity-80" x-text="replyPreview(replyTo)"></span></span>
          <span x-show="editId">Edit pesan</span>
        </div>
        <button class="px-2 py-1.5 rounded-lg btn-accent-ghost" @click="cancelReplyEdit()">✕</button>
      </div>

      <!-- selection bar -->
      <div class="flex items-center gap-2 mb-2" x-show="selectionMode">
        <div class="glass px-2 py-1.5 rounded-xl text-sm flex-1">
          <span x-text="selectedCount() + ' pesan dipilih'"></span>
        </div>
        <button class="px-2.5 py-1.5 rounded-lg btn-accent-ghost" @click="forwardSelected()">⤴︎ Teruskan</button>
        <button class="px-2.5 py-1.5 rounded-lg btn-accent-ghost" @click="starSelected()">★ Bintangi</button>
        <button class="px-2.5 py-1.5 rounded-lg btn-accent-ghost text-rose-600" @click="deleteSelected()">🗑️ Hapus</button>
        <button class="px-2.5 py-1.5 rounded-lg btn-accent-ghost" @click="selectionMode=false; selectedIds.clear()">Selesai</button>
      </div>

      <!-- bar aksi -->
      <div class="flex items-center gap-2 mb-2">
        <button class="px-2.5 py-1.5 rounded-lg btn-accent-ghost" :disabled="!activeId" title="Kirim gambar" @click="$refs.img.click()">🖼️</button>
        <input type="file" class="sr-only" accept="image/*" multiple x-ref="img" @change="attachImages">
        <button class="px-2.5 py-1.5 rounded-lg btn-accent-ghost" :disabled="!activeId" title="Kirim file" @click="$refs.file.click()">📎</button>
        <input type="file" class="sr-only" multiple
               accept=".pdf,.zip,.rar,.7z,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,application/*,text/plain"
               x-ref="file" @change="attachFiles">
        <button class="px-2.5 py-1.5 rounded-lg btn-accent-ghost"
                :class="rec.state==='rec'?'animate-pulse ring-2 ring-rose-500':''"
                :disabled="!activeId"
                @click="toggleRecord" x-text="rec.state==='idle'?'🎤':'⏹'"></button>
        <button class="px-2.5 py-1.5 rounded-lg btn-accent-ghost" :disabled="!activeId" @click="openCamera()">📷</button>
        <div class="text-xs muted" x-show="rec.state!=='idle'">
          <span x-text="rec.state==='rec' ? 'Merekam… '+rec.mmss : (rec.state==='stop' ? 'Menyiapkan audio…' : '')"></span>
          <button class="ml-2 underline" @click="cancelRecord">batal</button>
        </div>
      </div>

      <!-- input -->
      <div class="flex items-end gap-2">
        <textarea x-model="draft" @input="onDraftInput()" @keydown.enter.prevent="send()"
          rows="1" class="flex-1 rounded-xl px-3 py-2 border bg-transparent resize-none"
          :placeholder="activeId ? 'Tulis pesan…' : 'Pilih chat dulu'"></textarea>
        <button class="px-4 py-2 rounded-xl btn-accent text-white"
                :disabled="!activeId || (!draft && !canQuickSend)" @click="send()"
                x-text="editId?'Simpan':'Kirim'"></button>
      </div>
      <div class="text-[11px] muted mt-1">Tip: seret & jatuhkan gambar ke area chat untuk mengirim cepat. Geser bubble ke kanan untuk membalas (mobile).</div>
    </div>
  </div>

  <!-- ============ RIGHT: SARAN FOLLOW ============ -->
  <aside class="glass rounded-2xl overflow-hidden h-[76vh] md:h-[78vh] grid grid-rows-[auto,1fr] hidden md:grid">
    <div class="px-4 py-3 border-b border-white/10 font-semibold">Untuk diikuti</div>
    <div class="p-3 overflow-auto space-y-2 no-scrollbar">
      <template x-for="u in recommend.slice(0,10)" :key="u.username">
        <div class="flex items-center gap-2 p-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5">
          <img :src="avatarOf(u.username)" class="w-9 h-9 rounded-full object-cover" alt="">
          <div class="min-w-0">
            <div class="text-sm font-medium truncate" x-text="nameOf(u.username)"></div>
            <div class="text-xs muted truncate">@<span x-text="u.username"></span></div>
          </div>
          <div class="ml-auto flex items-center gap-1">
            <button class="text-xs px-2 py-1 rounded-lg btn-accent-ghost" @click="startChat(u.username)">Chat</button>
            <button class="text-xs px-2 py-1 rounded-lg btn-accent-ghost" @click="toggleFollowUser(u.username)">
              <span x-text="isFollowingUser(u.username)?'Mengikuti':'Ikuti'"></span>
            </button>
          </div>
        </div>
      </template>
    </div>
  </aside>

  <!-- ======= PICKER: Chat baru / Forward ======= -->
  <div x-show="pickerOpen" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60" @click="pickerOpen=false"></div>
    <div class="relative w-full max-w-lg glass rounded-2xl p-4">
      <div class="flex items-center gap-2 mb-3">
        <div class="font-semibold" x-text="pickerPurpose==='forward'?'Teruskan ke…':'Pilih pengguna'"></div>
        <button class="ml-auto px-2 py-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5" @click="pickerOpen=false">✕</button>
      </div>
      <div class="glass px-2 py-1.5 rounded-xl flex items-center gap-2 mb-3">
        <svg class="w-4 h-4 opacity-60" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a8 8 0 1 1 5.292-14.03l4.869 4.869-1.414 1.414-4.869-4.869A8 8 0 0 1 10 18z"/></svg>
        <input class="bg-transparent outline-none text-sm w-full" :placeholder="pickerPurpose==='forward'?'Cari tujuan forward…':'Cari username/nama…'" x-model.trim="pickerQ">
      </div>
      <div class="max-h-72 overflow-auto space-y-2 no-scrollbar">
        <template x-for="u in pickableUnified()" :key="u.username">
          <button class="w-full text-left p-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5 flex items-center gap-2"
                  @click="pickerPurpose==='forward'? forwardToUser(u.username) : (startChat(u.username), pickerOpen=false)">
            <img :src="avatarOf(u.username)" class="w-8 h-8 rounded-full object-cover" alt="">
            <div class="min-w-0">
              <div class="text-sm font-medium truncate" x-text="nameOf(u.username)"></div>
              <div class="text-xs muted">@<span x-text="u.username"></span></div>
            </div>
          </button>
        </template>
        <div x-show="pickableUnified().length===0" class="muted text-sm text-center py-6">Tidak ditemukan.</div>
      </div>
    </div>
  </div>

  <!-- ======= CAMERA ======= -->
  <div x-show="camera.open" x-cloak class="fixed inset-0 z-[75] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70" @click="closeCamera()"></div>
    <div class="relative w-full max-w-md glass rounded-2xl p-3 grid gap-2">
      <div class="flex items-center gap-2">
        <div class="font-semibold">Kamera</div>
        <button class="ml-auto px-2 py-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5" @click="closeCamera()">✕</button>
      </div>
      <video x-ref="video" class="rounded-xl w-full aspect-video bg-black"></video>
      <div class="flex items-center gap-2">
        <button class="px-3 py-2 rounded-xl btn-accent text-white" @click="snap()">Ambil</button>
        <button class="px-3 py-2 rounded-xl btn-accent-ghost" @click="closeCamera()">Batal</button>
      </div>
    </div>
  </div>

</section>
@endsection

@push('scripts')
<style>
  /* chat wallpaper dengan overlay agar teks mudah dibaca */
  .bg-chat{
    background-image:
      linear-gradient(180deg, rgba(0,0,0,.30), rgba(0,0,0,.30)),
      url('https://images.unsplash.com/photo-1496307042754-b4aa456c4a2d?q=80&w=1400&auto=format&fit=crop');
    background-size: cover; background-position: center; background-attachment: fixed;
  }
  .bg-fade{ background: linear-gradient(to bottom, rgba(0,0,0,0), rgba(0,0,0,0)); }
  .msg-btn{ font-size:12px; line-height:1; padding:.15rem .35rem; border-radius:.5rem; background:rgba(0,0,0,.12) }
  .dark .msg-btn{ background:rgba(255,255,255,.12) }
  mark{ background: #fde68a; color: inherit; padding:0 .12rem; border-radius:.2rem }
  .msg-check{ position:absolute; top:-8px; left:-8px; width:18px; height:18px; }
  @media (max-width: 767px){ .h-\[76vh\]{ height: 76vh } }
</style>
<script>
function MessagesPage(){
  // --- helpers storage ---
  const LS={get(k,d){try{return JSON.parse(localStorage.getItem(k))??d}catch{return d}},set(k,v){localStorage.setItem(k,JSON.stringify(v))}};
  const persist=(k,v)=>localStorage.setItem(k, JSON.stringify(v));
  const TKEY='hs-dm-threads';
  const MKEY=id=>`hs-dm-msgs-${id}`;
  const DRAFTKEY='hs-dm-drafts';
  const idFor=(a,b)=>[a,b].sort().join('~');
  const now=()=>Date.now();

  const toDataUrl=(file)=>new Promise((res,rej)=>{ const r=new FileReader(); r.onload=()=>res(r.result); r.onerror=rej; r.readAsDataURL(file); });
  const loadImage=(src)=>new Promise((res,rej)=>{ const img=new Image(); img.onload=()=>res(img); img.onerror=rej; img.src=src; });
  async function compressImage(file, maxW=1280, maxH=1280, quality=.78){
    // baca sebagai DataURL
    const raw=await toDataUrl(file);
    const img=await loadImage(raw);
    let {width:w, height:h}=img;
    const ratio=Math.min(maxW/w, maxH/h, 1);
    const cw=Math.round(w*ratio), ch=Math.round(h*ratio);
    const c=document.createElement('canvas'); c.width=cw; c.height=ch;
    c.getContext('2d').drawImage(img,0,0,cw,ch);
    const type='image/jpeg'; // kompres jadi jpeg
    const data=c.toDataURL(type, quality);
    return {src:data, size:data.length, mime:type, name:file.name.replace(/\.(png|webp|gif)$/i,'.jpg')};
  }

  const timefmt=(ts)=>new Date(ts).toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
  const humanSize=(n)=>{ if(!n&&n!==0) return ''; const u=['B','KB','MB','GB']; let i=0; while(n>1024&&i<u.length-1){n/=1024;i++} return (Math.round(n*10)/10)+' '+u[i] };

  // sanitizer + linkify + highlight
  const esc = (s)=>s.replace(/[&<>\"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
  const urlRe = /\b((https?:\/\/|www\.)[^\s<]+[^<.,:;!?)\]\s])/gi;
  const linkify=(text)=>{
    return esc(text).replace(urlRe, m=>{
      const url = m.startsWith('http')? m : 'https://'+m;
      return `<a href="${url}" target="_blank" rel="noreferrer" class="underline break-words">${esc(m)}</a>`;
    });
  };
  const applyHighlight=(html, query)=>{
    if(!query) return html;
    const q = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return html.replace(new RegExp(q,'gi'), m=>`<mark>${m}</mark>`);
  };

  // preview link (client-only, heuristik)
  function buildPreview(text){
    const m = (text||'').match(urlRe);
    if(!m || !m.length) return null;
    const raw = m[0];
    const url = raw.startsWith('http')? raw : 'https://'+raw;
    try{
      const u = new URL(url);
      const host=u.hostname, path=(u.pathname+u.search).slice(0,120)||'/';
      // YouTube
      if(/(youtube\.com|youtu\.be)/i.test(host)){
        let id=null;
        if(host.includes('youtu.be')) id=u.pathname.slice(1);
        else id=new URLSearchParams(u.search).get('v');
        const thumb=id?`https://i.ytimg.com/vi/${id}/hqdefault.jpg`:null;
        return {type:'youtube', url, host, path, title:u.searchParams.get('v')?'Video':'YouTube', thumb};
      }
      // Gambar langsung
      if(/\.(png|jpg|jpeg|webp|gif)$/i.test(u.pathname)){
        return {type:'image', url};
      }
      // Generic
      return {type:'link', url, host, path};
    }catch{ return null; }
  }

  return {
    // expose
    timefmt, humanSize, persist,

    // prefs
    chatFont: JSON.parse(localStorage.getItem('hs-dm-font')||'"base"'),

    // data
    users:[], recommend:[],
    me:(window.CURRENT_USER && window.CURRENT_USER.username) || 'rina',
    q:'', showArchived:false,
    typing:{ remote:false, local:false, _deb:null },
    autoReply: JSON.parse(localStorage.getItem('hs-dm-autoreply')||'true'),
    notifOK:false,
    threads:[], activeId:null, activeOther:'', activeMeta:{name:'',avatar:'/images/demo/rina.jpg'},
    items:[], draft:'', unreadActive:0,
    isMobile: window.matchMedia('(max-width: 767px)').matches,
    mobileView:'list',
    emojis:['👍','❤️','😂','😮','😢','🙏'],
    lastActiveMap: LS.get('hs-last-active',{}),

    // recorder
    rec:{ state:'idle', mmss:'00:00', _mr:null, _timer:null, _startAt:0 },
    get canQuickSend(){ return false },

    // reply/edit
    replyTo:null, editId:null,

    // search in chat
    chatQ:'', matches:[], matchIndex:0,

    // virtualized
    visibleCount: 40,
    visibleItems(){
      const start = Math.max(0, this.items.length - this.visibleCount);
      return this.items.slice(start);
    },

    // camera
    camera:{ open:false, stream:null },

    // picker (new / forward)
    pickerOpen:false, pickerQ:'', pickerPurpose:'new', forwardBuffer:[], // messages to forward

    // multi-select
    selectionMode:false, selectedIds: new Set(),

    // swipe to reply
    swipe:{ id:null, x0:0, y0:0, dx:0, active:false },

    followUsers:new Set(LS.get('hs-follow-users',[])),

    // ===== INIT =====
    async init(){
      const [users] = await Promise.all([
        fetch('/data/users.json').then(r=>r.json()).catch(()=>[])
      ]);
      this.users = Array.isArray(users)?users:[];
      this.recommend = this.users.filter(u=>u.username!==this.me).slice(0,12);
      this.loadThreads();

      // ?to=
      const params = new URLSearchParams(location.search);
      const to = (params.get('to')||'').trim();
      if (to) { if(to!==this.me) this.startChat(to); history.replaceState({},'',location.pathname); }

      // cross-tab
      window.addEventListener('storage', (e)=>{
        if(e.key===TKEY || (e.key||'').startsWith('hs-dm-msgs-')) this.loadThreads();
        if(e.key===DRAFTKEY && this.activeId){ const map=LS.get(DRAFTKEY,{}); this.draft=map[this.activeId]||''; }
        if(e.key==='hs-user-overrides') this.refreshActiveMeta();
      });
      window.addEventListener('user:meta-updated', ()=>this.refreshActiveMeta());

      // notif
      this.ensurePermission();
      this.updateTitle();
      document.addEventListener('visibilitychange', ()=>this.updateTitle());

      // last active ticker (simulasi online/offline)
      setInterval(()=>{ this.lastActiveMap[this.me]=Date.now(); LS.set('hs-last-active', this.lastActiveMap); }, 10000);

      // restore draft (kalau sudah ada active)
      const map=LS.get(DRAFTKEY,{});
      if(this.activeId) this.draft = map[this.activeId]||'';
    },

    // ===== Notif & typing =====
    ensurePermission(){
      if(!('Notification' in window)) return;
      if(Notification.permission==='granted'){ this.notifOK=true; }
      else if(Notification.permission==='default'){ Notification.requestPermission().then(p=>this.notifOK=(p==='granted')); }
    },
    notify(uname, text){
      if(!this.notifOK || !document.hidden) return;
      const th=(LS.get(TKEY,[])||[]).find(t=>t.id===this.activeId);
      if(th?.mutedUntil && th.mutedUntil>Date.now()) return;
      try{ new Notification(this.nameOf(uname), { body:text, icon:this.avatarOf(uname) }); this._ding(); }catch{}
    },
    _ding(){ const beep='data:audio/wav;base64,UklGRiQAAABXQVZFZm10IBAAAAABAAEAESsAACJWAAACABAAZGF0YQgAAAAA//8AAP//AAD//wAA//8='; try{ new Audio(beep).play().catch(()=>{});}catch{} },
    onDraftInput(){
      this.typing.local = true;
      clearTimeout(this.typing._deb);
      this.typing._deb = setTimeout(()=>{ this.typing.local=false }, 1200);
      // autosave per-thread
      if(this.activeId){ const map=LS.get(DRAFTKEY,{}); map[this.activeId]=this.draft; LS.set(DRAFTKEY,map); }
    },
    scheduleAutoReply(){
      if(!this.autoReply || !this.activeId) return;
      const other=this.activeOther;
      this.typing.remote = true;
      const delay = 900 + Math.random()*1200;
      setTimeout(()=>{
        this.typing.remote = false;
        const repl = ['Siap!','Oke 🙌','Noted ya.','Mantap!','Besok lanjut ya.','Gas!','Sipp','On it!','Thanks!'];
        const text = repl[Math.floor(Math.random()*repl.length)];
        const msg={ id:'m-'+now().toString(36), from:other, to:this.me, at:now(), read:(!document.hidden && this.activeOther===other), type:'text', text, preview:buildPreview(text) };
        const arr=LS.get(MKEY(this.activeId),[]); arr.push(msg); LS.set(MKEY(this.activeId),arr);
        this._upsertThread(this.activeId, { lastMsg:text, lastAt:msg.at }, /*incUnread=*/true);
        if(this.activeOther===other){ this.items=arr; this.$nextTick(()=>this.scrollBottom()); }
        this.notify(other, text); this.updateTitle();
      }, delay);
    },

    // ===== User meta =====
    userMeta(uname){
      const base = this.users.find(u=>u.username===uname) || {username:uname, name:uname, avatar:'/images/demo/rina.jpg'};
      const ov = window.UserMeta?.get?.(uname) || {};
      return {...base, ...ov};
    },
    nameOf(uname){ return this.userMeta(uname).name },
    avatarOf(uname){ return this.userMeta(uname).avatar || '/images/demo/rina.jpg' },
    isOnline(uname){ const t = (this.lastActiveMap||{})[uname]; return t && (Date.now()-t<20000); },
    lastSeenText(uname){
      const t=(this.lastActiveMap||{})[uname]; if(this.isOnline(uname)) return 'online';
      if(!t) return 'terakhir terlihat lama';
      const diff=Math.floor((Date.now()-t)/1000); if(diff<60) return diff+'d yg lalu';
      const m=Math.floor(diff/60); if(m<60) return m+'m yg lalu';
      const h=Math.floor(m/60); return h+'j yg lalu';
    },

    // ===== Threads =====
    loadThreads(){
      this.threads = Array.isArray(LS.get(TKEY,[]))?LS.get(TKEY,[]):[];
      if (this.activeId) {
        this.items = LS.get(MKEY(this.activeId),[]);
        // pastikan preview sudah dihitung untuk pesan lama
        this.ensurePreviews();
        this.unreadActive = this._unreadFor(this.activeId);
        this.refreshSearchMatches();
      }
      this.updateTitle();
    },
    ensurePreviews(){
      if(!this.activeId) return;
      const arr = LS.get(MKEY(this.activeId),[]);
      let changed=false;
      arr.forEach(m=>{
        if(m.type==='text' && !m.preview){
          const p=buildPreview(m.text||''); if(p){ m.preview=p; changed=true; }
        }
      });
      if(changed){ LS.set(MKEY(this.activeId),arr); this.items=arr; }
    },
    otherOf(t){ return t.a===this.me ? t.b : t.a },
    filtered(){
      let arr = Array.isArray(this.threads)?this.threads:[];
      if(!this.showArchived) arr = arr.filter(t=>!t.archived);
      const q=(this.q||'').toLowerCase();
      if(q){
        arr = arr.filter(t=>{
          const uname=this.otherOf(t);
          const name=this.nameOf(uname).toLowerCase();
          const u=uname.toLowerCase();
          return name.includes(q)||u.includes(q)||(t.lastMsg||'').toLowerCase().includes(q);
        });
      }
      // pinned dulu
      return arr.sort((a,b)=>((b.pinned?1:0)-(a.pinned?1:0)) || (b.lastAt||0)-(a.lastAt||0));
    },
    openThread(t){
      this.activeId = t.id;
      this.activeOther = this.otherOf(t);
      this.refreshActiveMeta();
      this.items = LS.get(MKEY(this.activeId),[]);
      this.ensurePreviews();
      this.markRead(this.activeId);
      this.unreadActive = 0;
      // restore draft
      const map=LS.get(DRAFTKEY,{});
      this.draft = map[this.activeId]||'';
      // reset state
      this.replyTo=null; this.editId=null; this.chatQ=''; this.visibleCount=40;
      this.selectionMode=false; this.selectedIds.clear();
      if (this.isMobile) this.mobileView='chat';
      this.$nextTick(()=>{ this.refreshSearchMatches(); this.scrollBottom(); });
    },
    startChat(uname){
      if(!uname || uname===this.me){ window.toast?.('Tidak bisa chat ke diri sendiri'); return; }
      const id=idFor(this.me, uname);
      let th = Array.isArray(LS.get(TKEY,[]))?LS.get(TKEY,[]):[];
      if (th.findIndex(t=>t.id===id)<0) th.unshift({id, a:this.me, b:uname, lastAt:0, lastMsg:'', unread:0, archived:false, pinned:false});
      LS.set(TKEY, th); this.loadThreads();
      const t = this.threads.find(x=>x.id===id); if (t) this.openThread(t);
    },
    refreshActiveMeta(){ if(!this.activeId) return; this.activeMeta = { name:this.nameOf(this.activeOther), avatar:this.avatarOf(this.activeOther) }; },

    // ===== Message ops =====
    send(){
      if(!this.activeId) return;
      if(this.editId){ this.saveEdit(); return; }
      const text=(this.draft||'').trim();
      if (text){
        this._pushMsg({type:'text', text, replyTo:this.replyTo||null, preview:buildPreview(text)});
        this.draft=''; this.replyTo=null; this.saveDraft();
        this.scheduleAutoReply();
      }
    },
    _pushMsg(payload){
      const msg={ id:'m-'+now().toString(36), from:this.me, to:this.activeOther, at:now(), read:true, reactions:{}, ...payload };
      const arr=LS.get(MKEY(this.activeId),[]); arr.push(msg); LS.set(MKEY(this.activeId),arr);
      this.items=arr;
      const lastPreview = payload.type==='text' ? (payload.text||'')
                        : (payload.type==='image' ? '🖼️ Gambar' : payload.type==='audio' ? '🎤 Audio' : '📄 File');
      this._upsertThread(this.activeId, { lastMsg:lastPreview, lastAt:msg.at }, false);
      this.$nextTick(()=>{ this.refreshSearchMatches(); this.scrollBottom(); });
      const la=LS.get('hs-last-active',{}); la[this.me]=Date.now(); LS.set('hs-last-active',la);
    },
    // push ke thread lain (untuk forward)
    _pushToThread(tid, other, payload){
      const msg={ id:'m-'+now().toString(36), from:this.me, to:other, at:now(), read:true, reactions:{}, ...payload };
      const arr=LS.get(MKEY(tid),[]); arr.push(msg); LS.set(MKEY(tid),arr);
      const lastPreview = payload.type==='text' ? (payload.text||'')
                        : (payload.type==='image' ? '🖼️ Gambar' : payload.type==='audio' ? '🎤 Audio' : '📄 File');
      const th=LS.get(TKEY,[]); const i=th.findIndex(t=>t.id===tid);
      if(i<0){ th.unshift({id:tid, a:this.me, b:other, lastAt:msg.at, lastMsg:lastPreview, unread:0, archived:false}); }
      else { th[i]={...th[i], lastAt:msg.at, lastMsg:lastPreview }; }
      LS.set(TKEY, th);
    },
    saveDraft(){ if(!this.activeId) return; const map=LS.get(DRAFTKEY,{}); map[this.activeId]=this.draft; LS.set(DRAFTKEY,map); },

    markRead(id){
      const arr=LS.get(MKEY(id),[]);
      let changed=false;
      const mapped=arr.map(m => (m.to===this.me && !m.read) ? (changed=true,{...m, read:true}) : m );
      if(changed){ LS.set(MKEY(id), mapped); if(this.activeId===id) this.items = mapped; }
      const th=LS.get(TKEY,[]).map(t=> t.id===id ? {...t, unread:this._unreadFor(id)} : t);
      LS.set(TKEY, th); this.loadThreads();
    },
    markAllRead(){ (LS.get(TKEY,[])||[]).forEach(t=>this.markRead(t.id)); },
    toggleArchive(id){ const th=LS.get(TKEY,[]).map(t=> t.id===id ? {...t, archived:!t.archived} : t); LS.set(TKEY, th); this.loadThreads(); },
    isArchived(id){ const t=(LS.get(TKEY,[])||[]).find(x=>x.id===id); return !!(t && t.archived); },
    togglePin(id){ const th=LS.get(TKEY,[]).map(t=> t.id===id ? {...t, pinned:!t.pinned} : t); LS.set(TKEY, th); this.loadThreads(); },
    isPinned(id){ const t=(LS.get(TKEY,[])||[]).find(x=>x.id===id); return !!(t && t.pinned); },
    deleteThread(id){
      LS.set(TKEY, (LS.get(TKEY,[])||[]).filter(t=>t.id!==id));
      localStorage.removeItem(MKEY(id));
      if (this.activeId===id){ this.activeId=null; this.activeOther=''; this.items=[]; this.unreadActive=0; }
      this.loadThreads();
    },
    _unreadFor(id){ return (LS.get(MKEY(id),[])||[]).filter(m=>m.to===this.me && !m.read).length; },
    _upsertThread(id, patch={}, incUnread=false){
      const th=LS.get(TKEY,[]);
      const i=th.findIndex(t=>t.id===id);
      if(i<0){ th.unshift({id, a:this.me, b:this.activeOther, unread:0, archived:false, pinned:false, ...patch}); }
      else{
        const cur=th[i];
        const unread = incUnread ? (cur.unread||0)+1 : this._unreadFor(id);
        th[i] = {...cur, ...patch, unread};
      }
      LS.set(TKEY, th);
      this.loadThreads();
      this.updateTitle();
    },
    updateTitle(){ const total=((LS.get(TKEY,[])||[]).reduce((s,t)=>s+(t.unread||0),0))|0; document.title = (total?`(${total}) `:'') + 'Pesan | HobiSpace'; },

    // ===== Message actions: reply/edit/delete/star/react/copy/forward =====
    beginReply(m){ this.replyTo=m.id; this.$nextTick(()=>document.activeElement?.blur()); },
    cancelReplyEdit(){ this.replyTo=null; this.editId=null; this.draft=''; this.saveDraft(); },
    replyPreview(id){
      const msg=(this.items||[]).find(x=>x.id===id); if(!msg) return '';
      if(msg.type==='text') return (msg.text||'').slice(0,80);
      if(msg.type==='image') return '🖼️ Gambar';
      if(msg.type==='audio') return '🎤 Audio';
      if(msg.type==='file')  return '📄 '+(msg.media?.name||'file');
      return '';
    },
    beginEdit(m){ if(!(m.from===this.me && m.type==='text' && !m.deleted)) return; this.editId=m.id; this.draft=m.text||''; this.$nextTick(()=>this.saveDraft()); },
    saveEdit(){
      const text=(this.draft||'').trim(); if(!text){ this.cancelReplyEdit(); return; }
      const arr=LS.get(MKEY(this.activeId),[]);
      const i=arr.findIndex(x=>x.id===this.editId); if(i>=0){ arr[i]={...arr[i], text, editedAt:Date.now(), preview:buildPreview(text)}; LS.set(MKEY(this.activeId),arr); this.items=arr; }
      this._upsertThread(this.activeId, { lastMsg:text, lastAt:Date.now() }, false);
      this.editId=null; this.draft=''; this.saveDraft(); this.$nextTick(()=>this.scrollBottom());
    },
    deleteMsg(m){
      const arr=LS.get(MKEY(this.activeId),[]);
      const i=arr.findIndex(x=>x.id===m.id); if(i>=0){ arr[i]={...arr[i], deleted:true, text:'', media:null, preview:null}; LS.set(MKEY(this.activeId),arr); this.items=arr; }
      this.$nextTick(()=>this.refreshSearchMatches());
    },
    toggleStar(m){ m.starred=!m.starred; const arr=LS.get(MKEY(this.activeId),[]).map(x=>x.id===m.id?m:x); LS.set(MKEY(this.activeId),arr); },
    toggleReaction(m,emo){
      const u=this.me; if(!m.reactions) m.reactions={};
      const set=new Set(m.reactions[emo]||[]);
      set.has(u)?set.delete(u):set.add(u);
      m.reactions[emo]=[...set];
      const arr=LS.get(MKEY(this.activeId),[]).map(x=>x.id===m.id?m:x);
      LS.set(MKEY(this.activeId),arr); this.items=arr;
    },
    copyText(m){ try{ navigator.clipboard.writeText(m.text||''); window.toast?.('Disalin'); }catch{} },
    forwardSingle(m){
      this.forwardBuffer=[m];
      this.openPicker('forward');
    },
    forwardSelected(){
      if(!this.selectedIds.size){ window.toast?.('Pilih pesan dulu'); return; }
      this.forwardBuffer = this.items.filter(x=>this.selectedIds.has(x.id));
      this.openPicker('forward');
    },
    forwardToUser(uname){
      // siapkan thread
      if(!uname || uname===this.me){ window.toast?.('Tujuan tidak valid'); return; }
      const tid=idFor(this.me, uname);
      const th = LS.get(TKEY,[]); if(th.findIndex(t=>t.id===tid)<0){ th.unshift({id:tid,a:this.me,b:uname,lastAt:0,lastMsg:'',unread:0,archived:false}); LS.set(TKEY, th); }
      // kirim
      (this.forwardBuffer||[]).forEach(src=>{
        const payload = src.type==='text'
          ? {type:'text', text: src.text || '', replyTo:null, preview: buildPreview(src.text||''), fwdOf:src.id }
          : src.type==='image'
            ? {type:'image', media:{...src.media}, fwdOf:src.id}
            : src.type==='audio'
              ? {type:'audio', media:{...src.media}, fwdOf:src.id}
              : {type:'file',  media:{...src.media}, fwdOf:src.id};
        this._pushToThread(tid, uname, payload);
      });
      this.pickerOpen=false; this.forwardBuffer=[];
      window.toast?.('Diteruskan');
    },

    // ===== Multi-select helpers =====
    isSelected(id){ return this.selectedIds.has(id); },
    toggleSelectMsg(m){ this.selectedIds.has(m.id) ? this.selectedIds.delete(m.id) : this.selectedIds.add(m.id); },
    selectedCount(){ return this.selectedIds.size; },
    deleteSelected(){
      if(!this.selectedIds.size) return;
      const arr=LS.get(MKEY(this.activeId),[]).map(x=> this.selectedIds.has(x.id)? {...x, deleted:true, text:'', media:null, preview:null} : x);
      LS.set(MKEY(this.activeId),arr); this.items=arr; this.selectedIds.clear();
    },
    starSelected(){
      if(!this.selectedIds.size) return;
      const arr=LS.get(MKEY(this.activeId),[]).map(x=> this.selectedIds.has(x.id)? {...x, starred:!x.starred} : x);
      LS.set(MKEY(this.activeId),arr); this.items=arr;
    },

    // ===== Swipe to reply =====
    swipeStart(ev,m){
      if(this.selectionMode) return;
      const t=ev.changedTouches[0]; this.swipe={id:m.id,x0:t.clientX,y0:t.clientY,dx:0,active:true};
    },
    swipeMove(ev,m){
      if(!this.swipe.active || this.swipe.id!==m.id) return;
      const t=ev.changedTouches[0];
      this.swipe.dx = t.clientX - this.swipe.x0;
    },
    swipeEnd(ev,m){
      if(!this.swipe.active || this.swipe.id!==m.id) return;
      const dy = Math.abs(ev.changedTouches[0].clientY - this.swipe.y0);
      if(this.swipe.dx>55 && dy<25){ this.beginReply(m); }
      this.swipe={id:null,x0:0,y0:0,dx:0,active:false};
    },
    swipeStyle(id){
      if(this.swipe.active && this.swipe.id===id){
        const dx=Math.max(0, Math.min(this.swipe.dx, 72));
        return `transform: translateX(${dx}px)`;
      }
      return '';
      },

    // ===== Attachments =====
    async attachImages(e){
      const files=[...(e.target.files||[])].slice(0,6); e.target.value='';
      if(!this.activeId || !files.length) return;
      for(const f of files){
        if(!f.type.startsWith('image/')) continue;
        // kompres
        const data = await compressImage(f);
        this._pushMsg({type:'image', media:data});
      }
      window.toast?.('Gambar terkirim (terkompres)');
    },
    async attachFiles(e){
      const files=[...(e.target.files||[])].slice(0,6); e.target.value='';
      if(!this.activeId || !files.length) return;
      for(const f of files){
        const src = await toDataUrl(f);
        this._pushMsg({type:'file', media:{src, name:f.name, size:f.size, mime:f.type||'application/octet-stream'}});
      }
      window.toast?.('File terkirim');
    },
    onDrop(ev){
      if(!this.activeId) return;
      const files=[...(ev.dataTransfer?.files||[])].slice(0,6);
      if(!files.length) return;
      files.forEach(async f=>{
        if(f.type.startsWith('image/')){
          const data=await compressImage(f);
          this._pushMsg({type:'image', media:data});
        }else{
          const src=await toDataUrl(f);
          this._pushMsg({type:'file', media:{src, name:f.name, size:f.size, mime:f.type||'application/octet-stream'}});
        }
      });
      window.toast?.('Terkirim');
    },

    // ===== Recorder =====
    async toggleRecord(){
      if(this.rec.state==='idle'){
        try{
          const stream = await navigator.mediaDevices.getUserMedia({audio:true});
          this.rec._mr = new MediaRecorder(stream, {mimeType:'audio/webm'});
          const chunks=[];
          this.rec._mr.ondataavailable = e=> e.data.size && chunks.push(e.data);
          this.rec._mr.onstop = async ()=>{
            clearInterval(this.rec._timer); this.rec.state='stop';
            try{
              const blob = new Blob(chunks,{type:'audio/webm'});
              const src = await new Promise(r=>{ const fr=new FileReader(); fr.onload=()=>r(fr.result); fr.readAsDataURL(blob); });
              this._pushMsg({type:'audio', media:{src, mime:'audio/webm', size:blob.size}});
            }finally{ stream.getTracks().forEach(t=>t.stop()); this.rec.state='idle'; this.rec.mmss='00:00'; }
          };
          this.rec.state='rec'; this.rec._mr.start();
          this.rec._startAt=Date.now();
          this.rec._timer=setInterval(()=>{ const s=Math.floor((Date.now()-this.rec._startAt)/1000); const mm=('0'+Math.floor(s/60)).slice(-2); const ss=('0'+(s%60)).slice(-2); this.rec.mmss=`${mm}:${ss}`; }, 250);
        }catch{ window.toast?.('Mic tidak tersedia / ditolak'); this.rec.state='idle'; }
      }else if(this.rec.state==='rec'){ this.rec._mr?.stop(); }
    },
    cancelRecord(){ try{ this.rec._mr?.stop(); }catch{} clearInterval(this.rec._timer); this.rec.state='idle'; this.rec.mmss='00:00'; },

    // ===== Camera =====
    async openCamera(){
      if(!this.activeId) return;
      try{
        this.camera.open=true;
        this.camera.stream = await navigator.mediaDevices.getUserMedia({video:true});
        this.$refs.video.srcObject=this.camera.stream; this.$refs.video.play();
      }catch{ this.camera.open=false; window.toast?.('Kamera tidak tersedia/ditolak'); }
    },
    async snap(){
      try{
        const v=this.$refs.video; const c=document.createElement('canvas'); c.width=v.videoWidth; c.height=v.videoHeight;
        c.getContext('2d').drawImage(v,0,0); const src=c.toDataURL('image/jpeg', .82);
        this._pushMsg({type:'image', media:{src, name:'camera.jpg', size:src.length, mime:'image/jpeg'}});
        this.closeCamera();
      }catch{ this.closeCamera(); }
    },
    closeCamera(){ try{ this.$refs.video.pause(); }catch{} try{ this.camera.stream?.getTracks().forEach(t=>t.stop()); }catch{} this.camera.open=false; },

    // ===== Search in chat =====
    renderText(m){ const txt = m.text || ''; return applyHighlight(linkify(txt), this.chatQ); },
    refreshSearchMatches(){
      if(!this.activeId || !this.chatQ){ this.matches=[]; this.matchIndex=0; return; }
      const q=this.chatQ.toLowerCase();
      this.matches = this.items.filter(m=>m.type==='text' && (m.text||'').toLowerCase().includes(q)).map(m=>m.id);
      this.matchIndex=0;
      if(this.matches.length) this.scrollToMsg(this.matches[0]);
    },
    gotoNextMatch(){ if(!this.matches.length) return; this.matchIndex=(this.matchIndex+1)%this.matches.length; this.scrollToMsg(this.matches[this.matchIndex]); },
    gotoPrevMatch(){ if(!this.matches.length) return; this.matchIndex=(this.matchIndex-1+this.matches.length)%this.matches.length; this.scrollToMsg(this.matches[this.matchIndex]); },
    scrollToMsg(id){ this.$nextTick(()=>{ const el=document.getElementById('m-'+id); if(el){ el.scrollIntoView({behavior:'smooth', block:'center'}); el.classList.add('ring-2','ring-cyan-500'); setTimeout(()=>el.classList.remove('ring-2','ring-cyan-500'),900); } }); },

    // ===== Picker =====
    openPicker(purpose){ this.pickerPurpose=purpose; this.pickerOpen=true; this.pickerQ=''; },
    pickable(){
      const taken = new Set(this.threads.map(t=>this.otherOf(t)));
      const q=(this.pickerQ||'').toLowerCase();
      return this.users
        .filter(u=>u.username!==this.me && !taken.has(u.username))
        .filter(u=>!q || u.username.toLowerCase().includes(q) || (this.nameOf(u.username)||'').toLowerCase().includes(q));
    },
    // untuk forward: semua user (termasuk yang sudah ada thread)
    pickableUnified(){
      const q=(this.pickerQ||'').toLowerCase();
      return this.users
        .filter(u=>u.username!==this.me)
        .filter(u=>!q || u.username.toLowerCase().includes(q) || (this.nameOf(u.username)||'').toLowerCase().includes(q))
        .slice(0,50);
    },

    // ===== Mute =====
    muteFor(mins){
      const th=LS.get(TKEY,[]).map(t=> t.id===this.activeId ? {...t, mutedUntil: (mins>0? Date.now()+mins*60*1000 : 0) } : t);
      LS.set(TKEY, th); window.toast?.(mins>0?`Dimute ${mins} menit`:'Mute dimatikan');
    },

    // composer helpers
    scrollBottom(){ const el=this.$refs.scroll; if(!el) return; el.scrollTop = el.scrollHeight + 999; },

    // Follow
    toggleFollowUser(u){ if(!u || u===this.me) return; this.followUsers.has(u)?this.followUsers.delete(u):this.followUsers.add(u); LS.set('hs-follow-users',[...this.followUsers]) },
    isFollowingUser(u){ return this.followUsers.has(u) },

    // Export / Import
    exportChat(id){
      const t = (LS.get(TKEY,[])||[]).find(x=>x.id===id);
      const data = { thread:t, messages:LS.get(MKEY(id),[]) };
      const blob = new Blob([JSON.stringify(data,null,2)],{type:'application/json'});
      const a = document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=`chat-${id}.json`; a.click(); URL.revokeObjectURL(a.href);
    },
    exportAll(){
      const threads = LS.get(TKEY,[])||[]; const all = threads.map(t=>({thread:t, messages:LS.get(MKEY(t.id),[])}));
      const blob = new Blob([JSON.stringify(all,null,2)],{type:'application/json'});
      const a = document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=`hobispace-chats.json`; a.click(); URL.revokeObjectURL(a.href);
    },
    async importAll(e){
      const f=e.target.files?.[0]; e.target.value=''; if(!f) return;
      try{
        const text = await f.text(); const data = JSON.parse(text);
        if(Array.isArray(data)){
          const th = LS.get(TKEY,[]); data.forEach(({thread,messages})=>{ if(!thread?.id) return; const ex = th.find(x=>x.id===thread.id); if(!ex) th.push(thread); else Object.assign(ex, thread); if(Array.isArray(messages)) LS.set(MKEY(thread.id), messages); });
          LS.set(TKEY, th); this.loadThreads(); window.toast?.('Impor selesai');
        }else if(data?.thread && Array.isArray(data?.messages)){
          const th = LS.get(TKEY,[]); const i = th.findIndex(x=>x.id===data.thread.id); if(i<0) th.push(data.thread); else th[i]=data.thread;
          LS.set(TKEY, th); LS.set(MKEY(data.thread.id), data.messages); this.loadThreads(); window.toast?.('Impor selesai');
        }else{ window.toast?.('Format tidak dikenali'); }
      }catch{ window.toast?.('Gagal impor'); }
    },

    // ===== Utils =====
    updateTitle(){ const total=((LS.get(TKEY,[])||[]).reduce((s,t)=>s+(t.unread||0),0))|0; document.title = (total?`(${total}) `:'') + 'Pesan | HobiSpace'; },
    saveAll(){ const map=LS.get(DRAFTKEY,{}); if(this.activeId) { map[this.activeId]=this.draft; LS.set(DRAFTKEY,map); } },
  }
}
</script>
@endpush
