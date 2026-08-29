<x-app-layout>
    <div class="wrap max-w-md py-8">
        <div class="kicker">Verifikasi e-ticket ✅</div>

        <div class="card mt-4">
            {{-- ==== NAMA ACARA ==== --}}
            <div class="font-mono text-[11px] uppercase tracking-[0.2em] text-creamDim">Nama acara</div>
            <h1 class="mt-1 font-display text-xl text-cream">{{ $reg->event->judul }}</h1>
            <p class="mt-1 font-mono text-[10px] text-creamDim">
                {{ $reg->event->mulai->translatedFormat('D, d M Y · H:i') }} · {{ $reg->event->lokasi }}
            </p>

            {{-- ==== PEMILIK TIKET ==== --}}
            <div class="mt-4 border-t border-dashed border-neonDim/50 pt-4 text-center">
                <div class="font-mono text-xs text-creamDim">tiket milik</div>
                <div class="mt-0.5 font-display text-2xl text-cream">{{ $reg->user->name }}</div>
                <div class="mt-1 font-mono text-[10px] text-creamDim">
                    {{ $reg->user->angkatan?->nama ?? '—' }}
                    @if ($reg->user->profile?->kelas) · {{ $reg->user->profile->kelas }} @endif
                </div>
                <div class="mt-2 font-mono text-sm font-bold tracking-wider text-neon">{{ $reg->kode_tiket }}</div>
            </div>

            {{-- ==== STATUS ==== --}}
            <div class="mt-4 flex items-center justify-center gap-2">
                <span class="chip-{{ $reg->status === 'hadir' ? 'neon' : 'line' }}">status: {{ $reg->status }}</span>
                @if ($reg->checked_in_at)
                    <span class="chip-line">check-in {{ $reg->checked_in_at->translatedFormat('d M H:i') }}</span>
                @endif
            </div>

            {{-- ==== AKSI PANITIA ==== --}}
            @if (auth()->user()->hasAnyRole(['super_admin', 'pengurus']) && $reg->status !== 'hadir')
                <form method="POST" action="{{ route('admin.event.checkin.store', $reg->event) }}" class="mt-5">
                    @csrf
                    <input type="hidden" name="kode_tiket" value="{{ $reg->kode_tiket }}">
                    <button class="btn-neon w-full">✓ Check-in Sekarang</button>
                </form>
            @endif
        </div>

        <a href="{{ route('tickets.index') }}" class="mt-4 block text-center font-mono text-xs text-neon hover:underline">
            ← kembali ke tiket saya
        </a>
    </div>
</x-app-layout>
