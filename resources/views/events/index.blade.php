<x-app-layout>
    <div class="wrap py-8">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <div class="kicker">Event & reuni</div>
                <h1 class="font-display text-2xl text-cream sm:text-3xl">Kumpul-kumpul selanjutnya 🎉</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('events.index', ['tab' => 'mendatang']) }}" class="{{ $tab === 'mendatang' ? 'btn-neon' : 'btn-outline' }}">Mendatang</a>
                <a href="{{ route('events.index', ['tab' => 'lalu']) }}" class="{{ $tab === 'lalu' ? 'btn-neon' : 'btn-outline' }}">Sudah lewat</a>
                <a href="{{ route('tickets.index') }}" class="btn-outline">Tiket Saya</a>
            </div>
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            @forelse ($events as $e)
                <a href="{{ route('events.show', $e) }}" class="card transition hover:border-neon">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="font-display text-lg leading-snug text-cream">{{ $e->judul }}</h2>
                        <div class="shrink-0 rounded bg-navy-deep px-2.5 py-1.5 text-center">
                            <div class="font-display text-lg text-neon">{{ $e->mulai->format('d') }}</div>
                            <div class="font-mono text-[9px] uppercase text-creamDim">{{ $e->mulai->translatedFormat('M Y') }}</div>
                        </div>
                    </div>
                    <p class="mt-1 font-mono text-[10px] text-creamDim">📍 {{ $e->lokasi }}</p>
                    <p class="mt-2 line-clamp-2 text-sm text-creamDim">{{ Str::limit(strip_tags($e->deskripsi), 140) }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-1.5">
                        <span class="chip-line">{{ $e->mulai->translatedFormat('H:i') }} WIB</span>
                        <span class="chip-line">{{ $e->registrations_count }} terdaftar</span>
                        @if ($e->harga_tiket > 0)<span class="chip-line">Rp {{ number_format($e->harga_tiket, 0, ',', '.') }}</span>
                        @else<span class="chip-neon">gratis</span>@endif
                    </div>
                </a>
            @empty
                <p class="text-sm text-creamDim sm:col-span-2">Belum ada event {{ $tab === 'lalu' ? 'yang sudah lewat' : 'mendatang' }}.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $events->links() }}</div>
    </div>
</x-app-layout>
