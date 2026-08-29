<x-app-layout>
    <div class="wrap max-w-3xl py-8">
        <a href="{{ route('berita.index') }}" class="font-mono text-xs text-neon hover:underline">← semua berita</a>

        <article class="card mt-4">
            @if ($berita->is_pinned)<span class="badge-pending">📌 penting</span>@endif
            <h1 class="mt-1 font-display text-2xl leading-tight text-cream sm:text-3xl">{{ $berita->judul }}</h1>
            <p class="mt-2 font-mono text-[11px] text-creamDim/70">
                {{ $berita->published_at?->translatedFormat('l, d F Y · H:i') }} · oleh {{ $berita->user?->name ?? 'Panitia' }}
            </p>

            <x-share-buttons :url="route('berita.show', $berita)" :title="$berita->judul" />

            @if ($berita->gambar)
                <img src="{{ Storage::url($berita->gambar) }}" class="mt-4 w-full rounded" alt="{{ $berita->judul }}">
            @endif

            <div class="prose-invert mt-5 space-y-4 text-sm leading-relaxed text-cream/90">
                {!! nl2br(e($berita->isi)) !!}
            </div>

            @role('super_admin|pengurus')
                <div class="mt-6 flex gap-2 border-t border-line/20 pt-4">
                    <a href="{{ route('admin.berita.edit', $berita) }}" class="btn-outline">Edit</a>
                    <form method="POST" action="{{ route('admin.berita.destroy', $berita) }}" onsubmit="return confirm('Hapus berita ini?')">
                        @csrf @method('DELETE')
                        <button class="btn-danger">Hapus</button>
                    </form>
                </div>
            @endrole
        </article>

        @if ($lain->isNotEmpty())
            <section class="mt-8">
                <div class="kicker">Berita lainnya</div>
                <div class="mt-3 space-y-3">
                    @foreach ($lain as $b)
                        <a href="{{ route('berita.show', $b) }}" class="card-plain block text-sm text-cream transition hover:border-neon">
                            {{ $b->judul }}
                            <span class="ml-2 font-mono text-[10px] text-creamDim/60">{{ $b->published_at?->translatedFormat('d M') }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
