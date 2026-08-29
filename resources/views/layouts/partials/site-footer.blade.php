{{--
    Footer situs — dipakai di beranda (landing) & layout app (halaman dalam).
    Teks identik: "Alumni67 Connect — Komunitas Alumni SMUN 67 Halim · #SatuAngkatanSatuKompak"
--}}
<footer class="border-t border-neonDim/40 bg-navy-deep">
    <div class="wrap grid gap-8 py-10 sm:grid-cols-2 lg:grid-cols-[1.2fr_.8fr]">
        {{-- Brand --}}
        <div>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                <x-site-logo size="h-10 w-10" />
                <span class="leading-tight">
                    <span class="block font-display text-lg text-cream">Alumni67 Connect</span>
                    <span class="block font-mono text-[10px] uppercase tracking-[0.14em] text-neon">SMUN 67 Halim</span>
                </span>
            </a>
            <p class="mt-3 max-w-sm text-sm leading-relaxed text-creamDim">
                Tempat semua angkatan ngobrol, cari relasi, bantu teman —
                dari reuni sampai bursa kerja, semua ada di satu rumah. 🎉
            </p>
        </div>

        {{-- Link cepat --}}
        <nav class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm self-center">
            <a href="{{ route('berita.index') }}" class="text-creamDim transition hover:text-neon">Berita</a>
            <a href="{{ route('direktori.index') }}" class="text-creamDim transition hover:text-neon">Direktori Alumni</a>
            <a href="{{ route('events.index') }}" class="text-creamDim transition hover:text-neon">Event &amp; Reuni</a>
            <a href="{{ route('jobs.index') }}" class="text-creamDim transition hover:text-neon">Bursa Kerja</a>
            <a href="{{ route('donasi.index') }}" class="text-creamDim transition hover:text-neon">Donasi</a>
            <a href="{{ route('forum.index') }}" class="text-creamDim transition hover:text-neon">Forum</a>
        </nav>
    </div>

    {{-- Bar bawah --}}
    <div class="border-t border-line/20 py-4 text-center text-xs text-creamDim/70">
        Alumni67 Connect — Komunitas Alumni SMUN 67 Halim ·
        <span class="text-neon">#SatuAngkatanSatuKompak</span>
    </div>
</footer>
