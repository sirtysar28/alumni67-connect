<x-app-layout>
    <div class="wrap max-w-2xl py-8">
        <div class="kicker">Tiket saya</div>
        <h1 class="font-display text-2xl text-cream sm:text-3xl">E-Ticket Event 🎟️</h1>

        <div class="mt-6 space-y-4">
            @forelse ($regs as $r)
                <a href="{{ route('tickets.show', $r->kode_tiket) }}" class="card flex flex-wrap items-center justify-between gap-3 transition hover:border-neon">
                    <div>
                        <div class="font-display text-lg text-cream">{{ $r->event->judul }}</div>
                        <div class="mt-1 font-mono text-[10px] text-creamDim">
                            {{ $r->event->mulai->translatedFormat('D, d M Y · H:i') }} · {{ $r->event->lokasi }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-mono text-sm font-bold text-neon">{{ $r->kode_tiket }}</div>
                        <span class="chip-line mt-1">{{ $r->status }}</span>
                    </div>
                </a>
            @empty
                <p class="text-sm text-creamDim">Belum punya tiket. <a href="{{ route('events.index') }}" class="text-neon hover:underline">Lihat event mendatang →</a></p>
            @endforelse
        </div>
    </div>
</x-app-layout>
