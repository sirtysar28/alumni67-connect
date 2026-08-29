<x-app-layout>
    <div class="wrap max-w-3xl py-8">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <div class="kicker">Forum aspirasi & diskusi</div>
                <h1 class="font-display text-2xl text-cream sm:text-3xl">Suara angkatan 💬</h1>
                <p class="mt-1 text-sm text-creamDim">Saran reuni, ide kegiatan, voting keputusan — bisa anonim.</p>
            </div>
            @auth
                <a href="{{ route('forum.create') }}" class="btn-neon">+ Diskusi Baru</a>
            @endauth
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
            <a href="{{ route('forum.index') }}" class="{{ request()->missing('kategori') ? 'btn-neon' : 'btn-outline' }} !px-3.5 !py-1.5 text-xs">Semua</a>
            @foreach (\App\Http\Controllers\ForumController::KATEGORI as $k)
                <a href="{{ route('forum.index', ['kategori' => $k]) }}" class="{{ request('kategori') === $k ? 'btn-neon' : 'btn-outline' }} !px-3.5 !py-1.5 text-xs">{{ ucfirst($k) }}</a>
            @endforeach
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($threads as $t)
                <a href="{{ route('forum.show', $t) }}" class="card block transition hover:border-neon">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($t->is_pinned)<span class="badge-pending">📌 disematkan</span>@endif
                        <span class="chip-line !px-2 !py-0.5">{{ $t->kategori }}</span>
                        @if ($t->is_anonymous)<span class="chip-line !px-2 !py-0.5">🎭 anonim</span>@endif
                        @unless (auth()->check())<span class="chip-line !px-2 !py-0.5">🔒 login untuk baca</span>@endunless
                    </div>
                    <h2 class="mt-1.5 font-display text-lg text-cream">{{ $t->judul }}</h2>
                    <p class="mt-1 line-clamp-2 text-sm text-creamDim">{{ Str::limit(strip_tags($t->isi), 160) }}</p>
                    <p class="mt-2 font-mono text-[10px] text-creamDim/60">
                        {{ $t->displayName() }} · {{ $t->created_at->translatedFormat('d M Y') }} · 💬 {{ $t->replies_count }} balasan
                    </p>
                </a>
            @empty
                <p class="text-sm text-creamDim">Belum ada diskusi.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $threads->links() }}</div>
    </div>
</x-app-layout>
