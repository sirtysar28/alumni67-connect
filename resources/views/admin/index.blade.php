<x-app-layout>
    <div class="wrap py-8">
        <div class="kicker">Panel admin</div>
        <h1 class="font-display text-2xl text-cream sm:text-3xl">Kendalikan komunitas 🛠️</h1>
        <p class="mt-1 text-sm text-creamDim">Role kamu: <span class="text-neon">{{ auth()->user()->getRoleNames()->join(', ') }}</span></p>

        <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
            <a href="{{ route('admin.users.pending') }}" class="card transition hover:border-neon">
                <div class="font-display text-3xl {{ $pendingAkun ? 'text-yellow-300' : 'text-neon' }}">{{ $pendingAkun }}</div>
                <div class="font-mono text-[11px] uppercase text-creamDim">Akun menunggu</div>
            </a>
            <a href="{{ route('admin.verifications') }}" class="card transition hover:border-neon">
                <div class="font-display text-3xl {{ $pendingVerify ? 'text-yellow-300' : 'text-neon' }}">{{ $pendingVerify }}</div>
                <div class="font-mono text-[11px] uppercase text-creamDim">Verifikasi alumni</div>
            </a>
            <a href="{{ route('admin.donations') }}" class="card transition hover:border-neon">
                <div class="font-display text-3xl {{ $pendingDonasi ? 'text-yellow-300' : 'text-neon' }}">{{ $pendingDonasi }}</div>
                <div class="font-mono text-[11px] uppercase text-creamDim">Donasi pending</div>
            </a>
            <a href="{{ route('events.index') }}" class="card transition hover:border-neon">
                <div class="font-display text-3xl text-neon">{{ $events }}</div>
                <div class="font-mono text-[11px] uppercase text-creamDim">Event mendatang</div>
            </a>
            <div class="card">
                <div class="font-display text-3xl text-neon">{{ $alumniTotal }}</div>
                <div class="font-mono text-[11px] uppercase text-creamDim">Alumni terdaftar</div>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('admin.users.pending') }}" class="card text-center transition hover:border-neon">⏳<div class="mt-1 text-sm font-semibold text-cream">Setujui Akun</div><div class="text-[10px] text-creamDim">pendaftar baru {{ $pendingAkun ? '· '.$pendingAkun.' menunggu!' : '' }}</div></a>
            <a href="{{ route('admin.berita.create') }}" class="card text-center transition hover:border-neon">📰<div class="mt-1 text-sm font-semibold text-cream">Tulis Berita</div></a>
            <a href="{{ route('admin.event.create') }}" class="card text-center transition hover:border-neon">🎉<div class="mt-1 text-sm font-semibold text-cream">Buat Event</div></a>
            <a href="{{ route('admin.verifications') }}" class="card text-center transition hover:border-neon">✓<div class="mt-1 text-sm font-semibold text-cream">Verifikasi Badge</div></a>
            <a href="{{ route('admin.settings') }}" class="card text-center transition hover:border-neon">🎨<div class="mt-1 text-sm font-semibold text-cream">Pengaturan Situs</div><div class="text-[10px] text-creamDim">logo · tema · SMTP</div></a>
            @role('super_admin')
                <a href="{{ route('admin.terminal') }}" class="card text-center transition hover:border-neon">
                    <div class="font-mono text-lg text-neon">&gt;_</div>
                    <div class="mt-1 text-sm font-semibold text-cream">Terminal Artisan</div>
                    <div class="text-[10px] text-creamDim">migrate · seed · cache — Super Admin</div>
                </a>
            @endrole
        </div>

        {{-- ==== BERITA TERPUBLISH ==== --}}
        <div class="card mt-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="kicker !mb-0">📰 Berita terpublish</div>
                <a href="{{ route('admin.berita.create') }}" class="btn-outline !px-3 !py-1.5 text-xs">+ Tulis Berita</a>
            </div>

            @if ($beritas->isEmpty())
                <p class="mt-4 text-sm text-creamDim">
                    Belum ada berita yang dipublish.
                    <a href="{{ route('admin.berita.create') }}" class="text-neon hover:underline">Tulis berita pertama →</a>
                </p>
            @else
                <div class="mt-3">
                    @foreach ($beritas as $b)
                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line/10 py-3">
                            <div class="min-w-0">
                                <a href="{{ route('berita.show', $b) }}" class="font-semibold text-cream transition hover:text-neon">
                                    @if ($b->is_pinned)<span title="disematkan">📌</span>@endif
                                    {{ $b->judul }}
                                </a>
                                <div class="mt-0.5 font-mono text-[10px] text-creamDim">
                                    {{ $b->published_at->translatedFormat('d M Y · H:i') }} · oleh {{ $b->user?->name ?? '—' }}
                                </div>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <a href="{{ route('berita.show', $b) }}" target="_blank" class="btn-outline !px-3 !py-1.5 text-xs">Lihat</a>
                                <a href="{{ route('admin.berita.edit', $b) }}" class="btn-outline !px-3 !py-1.5 text-xs">Edit</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-2 text-right text-[10px] text-creamDim/70">Menampilkan {{ $beritas->count() }} berita terbaru</p>
            @endif
        </div>
    </div>
</x-app-layout>
