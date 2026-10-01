<x-app-layout>
    <div class="wrap max-w-3xl py-8">
        <a href="{{ route('dashboard') }}" class="font-mono text-xs text-neon hover:underline">← dashboard</a>
        <h1 class="mt-2 font-display text-2xl text-cream sm:text-3xl">Notifikasi 🔔</h1>
        <p class="mt-1 text-sm text-creamDim">
            Semua kabar untukmu: like, komentar, balasan forum, approval akun & badge,
            donasi, event, berita, dan pesan chat.
        </p>

        @if (session('success'))<div class="flash-ok mt-4">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="flash-err mt-4">{{ session('error') }}</div>@endif

        @unless ($ready)
            {{-- Tabel notifications belum dibuat — jangan error, beri petunjuk --}}
            <div class="flash-err mt-6">
                🔧 <b>Fitur notifikasi belum aktif di server ini.</b><br>
                <span class="text-xs">Tabel <code>notifications</code> belum dibuat migrasi. Super Admin dapat mengaktifkannya melalui
                <b>Admin → Terminal Artisan</b> → tombol <b>migrate --force</b> (atau <b>📚 Database → migrate --force</b>).</span>
            </div>
        @else
        <div class="mt-6">
            <form method="POST" action="{{ route('notifications.readall') }}" class="mb-4 text-right">
                @csrf
                <button type="submit" class="btn-outline !px-4 !py-2 text-xs">✓ Tandai semua dibaca</button>
            </form>

            <div class="card">
                @forelse ($notifications as $n)
                    <a href="{{ route('notifications.read', $n->id) }}"
                       class="flex gap-3 border-t border-line/10 py-4 px-1 transition hover:bg-neon/5 {{ $n->read_at ? '' : 'bg-neon/[.06]' }}">
                        <span class="text-xl leading-none">{{ $n->data['icon'] ?? '🔔' }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold {{ $n->read_at ? 'text-cream' : 'text-neon' }}">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                            <span class="mt-0.5 block text-xs text-creamDim">{{ $n->data['message'] ?? '' }}</span>
                            <span class="mt-1 block font-mono text-[10px] text-creamDim/70">{{ $n->created_at->translatedFormat('d M Y · H:i') }}</span>
                        </span>
                        @if (! $n->read_at)
                            <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-neon" title="belum dibaca"></span>
                        @endif
                    </a>
                @empty
                    <p class="text-sm text-creamDim">Belum ada notifikasi — mulai interaksi di feed & forum ya! 🔔</p>
                @endforelse
            </div>

            <div class="mt-6">{{ $notifications->links() }}</div>
        </div>
        @endunless
    </div>
</x-app-layout>
