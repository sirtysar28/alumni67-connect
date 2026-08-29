<x-app-layout>
    <div class="wrap max-w-md py-8">
        <a href="{{ route('tickets.index') }}" class="font-mono text-xs text-neon hover:underline">← semua tiket</a>

        <div class="card mt-4 text-center">
            <div class="font-mono text-[11px] uppercase tracking-[0.2em] text-creamDim">E-Ticket · tunjukkan QR saat check-in</div>
            <h1 class="mt-2 font-display text-xl text-cream">{{ $reg->event->judul }}</h1>
            <p class="mt-1 font-mono text-[10px] text-creamDim">
                {{ $reg->event->mulai->translatedFormat('D, d M Y · H:i') }} · {{ $reg->event->lokasi }}
            </p>

            <div class="mx-auto mt-5 w-fit rounded-lg border border-neonDim bg-white p-3">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(190)->generate(route('tickets.verify', $reg->kode_tiket)) !!}
            </div>
            <p class="mt-2 text-[10px] text-creamDim/70">📷 Scan QR → tampil nama acara & pemilik tiket</p>

            <div class="mt-4 border-t border-dashed border-neonDim/50 pt-4">
                <div class="font-mono text-xs text-creamDim">kode tiket</div>
                <div class="font-display text-2xl tracking-wider text-neon">{{ $reg->kode_tiket }}</div>
            </div>

            <div class="mt-4 flex items-center justify-center gap-2">
                <span class="chip-{{ $reg->status === 'hadir' ? 'neon' : 'line' }}">status: {{ $reg->status }}</span>
                @if ($reg->checked_in_at)
                    <span class="chip-line">check-in {{ $reg->checked_in_at->translatedFormat('d M H:i') }}</span>
                @endif
            </div>

            <a href="{{ route('tickets.pdf', $reg->kode_tiket) }}"
               class="btn-neon mt-4 w-full">⬇ Download PDF</a>
        </div>
    </div>
</x-app-layout>
