<x-guest-layout>
    @include('layouts.navigation')

    {{-- Efek gradasi warna berjalan utk teks hero --}}
    @once
    <style>
        .text-shimmer {
            background: linear-gradient(90deg,
                rgb(var(--c-neon)),
                #fde047,
                #22d3ee,
                rgb(var(--c-maroon)),
                rgb(var(--c-neon)));
            background-size: 300% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: shimmer-text 8s linear infinite;
        }
        @keyframes shimmer-text {
            to { background-position: 300% 0; }
        }
        /* Fallback: browser lama tetap pakai warna neon polos */
        @supports not ((-webkit-background-clip: text) or (background-clip: text)) {
            .text-shimmer { color: rgb(var(--c-neon)); animation: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            .text-shimmer { animation: none; }
        }
    </style>
    @endonce

    {{-- HERO --}}
    <section class="hero-scan relative overflow-hidden border-b border-neonDim/40">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -top-24 left-[10%] h-72 w-72 rounded-full bg-neon/10 blur-3xl"></div>
            <div class="absolute right-[5%] top-10 h-72 w-72 rounded-full bg-maroon/40 blur-3xl"></div>
        </div>

        <div class="wrap relative z-10 grid items-center gap-10 py-14 sm:py-20 lg:grid-cols-[1.15fr_.85fr]">
            <div>
                <span class="chip-neon">● Komunitas Alumni SMUN 67 Halim</span>
                <h1 class="mt-5 font-display text-4xl font-bold leading-[1.1] sm:text-5xl">
                    Reuninya kelar,<br><span class="text-shimmer">kebersamaannya lanjut.</span>
                </h1>
                <p class="mt-4 max-w-lg text-sm leading-relaxed text-creamDim sm:text-base">
                    Alumni67 Connect — tempat semua angkatan <b class="text-cream">ngobrol, cari relasi, bantu teman,
                    cari kerja, bikin acara, galang dana sosial,</b> sampai kolaborasi bisnis. Bukan sekadar website
                    alumni biasa.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    @guest
                        <a href="{{ route('register') }}" class="btn-neon">Gabung Sekarang — Gratis</a>
                        <a href="{{ route('login') }}" class="btn-outline">Sudah Punya Akun</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn-neon">Buka Dashboard</a>
                        <a href="{{ route('direktori.index') }}" class="btn-outline">Jelajahi Direktori</a>
                    @endguest
                </div>
            </div>

            <div class="card relative">
                <div class="font-mono text-[11px] uppercase tracking-[0.15em] text-creamDim">Sudah tergabung</div>
                <div class="mt-3 flex items-end gap-2">
                    <div class="font-display text-6xl text-neon">{{ \App\Models\User::whereHas('profile')->count() }}</div>
                    <div class="pb-2 font-mono text-xs text-creamDim">alumni<br>terdaftar</div>
                </div>
                <div class="mt-4 border-t border-dashed border-neonDim/50 pt-4 text-sm text-creamDim">
                    Angkatan 2003 udah mulai. Ajak teman seangkatanmu yang belum gabung! 🚀
                </div>
            </div>
        </div>
    </section>

    {{-- MARQUEE --}}
    <div class="overflow-hidden border-b border-neonDim/30 bg-gold py-2.5">
        <div class="strip-track inline-block whitespace-nowrap font-mono text-xs font-bold tracking-wide text-neon">
            @foreach (range(1, 2) as $i)
                <span class="mx-6">DIREKTORI ALUMNI</span> ●
                <span class="mx-6">BURSA KERJA #HiringAlumni</span> ●
                <span class="mx-6">EVENT & REUNI</span> ●
                <span class="mx-6">DONASI SOSIAL</span> ●
                <span class="mx-6">FORUM ASPIRASI</span> ●
                <span class="mx-6">#BisnisAlumni67</span> ●
            @endforeach
        </div>
    </div>

    {{-- BERITA & EVENT --}}
    <section class="wrap grid gap-8 py-12 sm:py-16 lg:grid-cols-[1.2fr_.8fr]">
        <div>
            <div class="kicker">Berita terbaru</div>
            <h2 class="h-display">Kabar terhangat buat kamu</h2>
            <div class="mt-5 space-y-4">
                @forelse ($beritas as $b)
                    <a href="{{ route('berita.show', $b) }}" class="card block transition hover:border-neon">
                        @if ($b->is_pinned)
                            <span class="badge-pending">📌 disematkan</span>
                        @endif
                        <h3 class="mt-1 font-display text-lg text-cream">{{ $b->judul }}</h3>
                        <p class="mt-1 line-clamp-2 text-sm text-creamDim">{{ $b->ringkasan }}</p>
                        <p class="mt-2 font-mono text-[11px] text-creamDim/60">{{ $b->published_at?->translatedFormat('d M Y') }}</p>
                    </a>
                @empty
                    <p class="text-sm text-creamDim">Belum ada berita.</p>
                @endforelse
                <a href="{{ route('berita.index') }}" class="btn-outline">Semua Berita →</a>
            </div>
        </div>

        <div>
            <div class="kicker">Agenda mendatang</div>
            <h2 class="h-display">Jangan ketinggalan</h2>
            <div class="mt-5 space-y-4">
                @forelse ($events as $e)
                    <a href="{{ route('events.show', $e) }}" class="card-plain block transition hover:border-neon">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-semibold text-cream">{{ $e->judul }}</h3>
                            <span class="chip-line shrink-0">{{ $e->mulai->translatedFormat('d M') }}</span>
                        </div>
                        <p class="mt-1 text-xs text-creamDim">📍 {{ $e->lokasi }}</p>
                    </a>
                @empty
                    <p class="text-sm text-creamDim">Belum ada agenda.</p>
                @endforelse
            </div>

            <div class="card mt-6">
                <div class="font-display text-lg text-cream">Fitur di dalamnya 🎉</div>
                <ul class="mt-3 space-y-2 text-sm text-creamDim">
                    <li>✓ Direktori alumni + filter angkatan/profesi/kota</li>
                    <li>✓ Bursa kerja: lowongan, referral, freelance, remote</li>
                    <li>✓ Event reuni: e-ticket & QR check-in</li>
                    <li>✓ Feed sosial + forum aspirasi (bisa anonim)</li>
                    <li>✓ Donasi sosial yang transparan</li>
                    <li>✓ Verified badge alumni (upload ijazah)</li>
                </ul>
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    @include('layouts.partials.site-footer')
</x-guest-layout>
