<x-app-layout>
    <div class="wrap py-8">
        <div class="kicker">Berita & pengumuman</div>
        <h1 class="font-display text-2xl text-cream sm:text-3xl">Kabar terbaru komunitas 📰</h1>

        <form class="mt-5 flex max-w-md gap-2" method="GET">
            <input class="input" type="text" name="q" placeholder="Cari judul berita…" value="{{ request('q') }}">
            <button class="btn-outline shrink-0">Cari</button>
        </form>

        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($beritas as $b)
                <a href="{{ route('berita.show', $b) }}" class="card flex flex-col transition hover:border-neon">
                    @if ($b->is_pinned)<span class="badge-pending self-start">📌 penting</span>@endif
                    <h2 class="mt-1 font-display text-lg leading-snug text-cream">{{ $b->judul }}</h2>
                    <p class="mt-2 line-clamp-3 flex-1 text-sm text-creamDim">{{ $b->ringkasan }}</p>
                    <p class="mt-3 font-mono text-[10px] text-creamDim/60">
                        {{ $b->published_at?->translatedFormat('d M Y') }} · {{ $b->user?->name }}
                    </p>
                </a>
            @empty
                <p class="text-sm text-creamDim sm:col-span-2 lg:col-span-3">Belum ada berita.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $beritas->links() }}</div>
    </div>
</x-app-layout>
