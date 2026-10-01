{{--
    Ikon notifikasi 🔔 — pojok kanan atas, di samping tombol profil.
    Berlaku untuk semua user login: like, komentar, forum, approval akun,
    badge, donasi, event, berita, chat.
    Dropdown via Alpine + polling ringan tiap 30 detik (public/js/widgets.js).
--}}
@auth
    @php
        $notifReady = \App\Support\Feature::notifications();
        $unreadCount = $notifReady ? auth()->user()->unreadNotificationsCountSafe() : 0;
        $recentNotifs = $notifReady ? auth()->user()->recentNotificationsSafe(6) : collect();
    @endphp
    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
        <button type="button" @click="open = !open" class="notif-btn"
                aria-label="Notifikasi ({{ $unreadCount }} belum dibaca)"
                title="Notifikasi">
            {{-- Lonceng --}}
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
            </svg>
            <span id="notif-badge"
                  class="notif-badge {{ $unreadCount > 0 ? 'show' : '' }}">{{ $unreadCount }}</span>
        </button>

        {{-- Dropdown daftar notifikasi --}}
        <div id="notif-panel" class="notif-panel" x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="notif-head">
                <b>🔔 Notifikasi</b>
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.readall') }}">
                        @csrf
                        <button type="submit" class="notif-mark">Tandai semua dibaca</button>
                    </form>
                @endif
            </div>

            <div id="notif-list" class="notif-list">
                @unless ($notifReady)
                    {{-- Tabel notifications belum dibuat migrasi — jangan error, tampilkan petunjuk --}}
                    <div class="notif-empty">
                        🔧 Fitur notifikasi belum aktif.<br>
                        <span class="text-\[10px\]">Super Admin: jalankan <code>migrate</code> lewat Admin → Terminal.</span>
                    </div>
                @else
                    {{-- Terisi server-side awal + di-refresh JS --}}
                    @forelse ($recentNotifs as $n)
                        <a href="{{ route('notifications.read', $n->id) }}" class="notif-item {{ $n->read_at ? '' : 'unread' }}">
                            <span class="n-icon">{{ $n->data['icon'] ?? '🔔' }}</span>
                            <span class="min-w-0">
                                <span class="n-title block">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                                <span class="n-msg block">{{ $n->data['message'] ?? '' }}</span>
                                <span class="n-when block">{{ $n->created_at->translatedFormat('d M Y · H:i') }}</span>
                            </span>
                        </a>
                    @empty
                        <div class="notif-empty">Belum ada notifikasi 🔔</div>
                    @endforelse
                @endunless
            </div>

            <div class="notif-foot">
                <a href="{{ route('notifications.index') }}">Lihat semua notifikasi →</a>
            </div>
        </div>
    </div>
@endauth
