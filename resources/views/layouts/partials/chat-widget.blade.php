{{--
    Widget chat 💬 antar alumni — melayang di pojok kanan bawah.
    Khusus user login. Kontak = semua alumni disetujui.
    Logika fetch + polling di public/js/widgets.js (fungsi ChatWidget).
    Peerpilihan awal: ?with={id} (dari notifikasi / halaman /chat).
--}}
@auth
    @if (! \App\Support\Feature::chat())
        {{-- Tabel `messages` belum dibuat migrasi — widget disembunyikan agar tidak error.
            Super Admin bisa mengaktifkannya: Admin → Terminal → migrate --force. --}}
    @else
    <div id="chat-widget"
         data-contacts-url="{{ route('chat.contacts') }}"
         data-peer="{{ $chatPeerId ?? request()->query('with') }}"
         data-auto-open="{{ request()->routeIs('chat.page') ? '1' : '0' }}">

        {{-- Tombol melayang 💬 --}}
        <button type="button" id="chat-fab" class="chat-fab" title="Chat alumni" aria-label="Buka chat alumni">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
            </svg>
            <span id="chat-badge" class="chat-badge">0</span>
        </button>

        {{-- Panel: daftar kontak / percakapan --}}
        <div id="chat-panel" class="chat-panel" aria-live="polite">

            {{-- == VIEW KONTAK == --}}
            <div id="chat-view-contacts">
                <div class="chat-head">
                    <div class="c-ava">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="min-w-0">
                        <div class="c-title">Chat Alumni</div>
                        <div class="c-sub">{{ auth()->user()->name }} · online</div>
                    </div>
                    <button type="button" id="chat-close-1" class="c-close" aria-label="Tutup chat">✕</button>
                </div>
                <div class="chat-search">
                    <input type="text" id="chat-search" placeholder="🔎 Cari nama alumni…" autocomplete="off">
                </div>
                <div id="chat-contacts" class="chat-contacts">
                    <div class="chat-empty">Memuat kontak…</div>
                </div>
            </div>

            {{-- == VIEW PERCAKAPAN == --}}
            <div id="chat-view-thread" style="display:none; flex-direction:column; flex:1; min-height:0;">
                <div class="chat-head">
                    <button type="button" id="chat-back" class="c-back" aria-label="Kembali ke kontak">←</button>
                    <div class="c-ava" id="chat-peer-ava">?</div>
                    <div class="min-w-0">
                        <div class="c-title" id="chat-peer-name">…</div>
                        <div class="c-sub" id="chat-peer-sub">alumni</div>
                    </div>
                    <button type="button" id="chat-close-2" class="c-close" aria-label="Tutup chat">✕</button>
                </div>
                <div id="chat-msgs" class="chat-msgs">
                    <div class="chat-info">Memuat percakapan…</div>
                </div>
                <div id="chat-typing" class="chat-typing">✍️ mengirim…</div>
                <form id="chat-form" class="chat-input">
                    <textarea id="chat-body" rows="1" maxlength="2000" placeholder="Tulis pesan… (Enter kirim, Shift+Enter baris baru)"></textarea>
                    <button type="submit" id="chat-send-btn" class="chat-send" aria-label="Kirim pesan">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
@endauth
