<x-app-layout>
    <div class="wrap max-w-3xl py-8">
        <a href="{{ route('events.index') }}" class="font-mono text-xs text-neon hover:underline">← semua event</a>

        <div class="card mt-4">
            <h1 class="font-display text-2xl text-cream sm:text-3xl">{{ $event->judul }}</h1>
            <div class="mt-3 flex flex-wrap gap-1.5">
                <span class="chip-neon">{{ $event->mulai->translatedFormat('l, d F Y · H:i') }} WIB</span>
                <span class="chip-line">📍 {{ $event->lokasi }}</span>
                <span class="chip-line">{{ $event->registrations_count }} terdaftar</span>
                @if ($event->harga_tiket > 0)
                    <span class="chip-line">Rp {{ number_format($event->harga_tiket, 0, ',', '.') }}</span>
                @else<span class="chip-line">gratis</span>@endif
            </div>

            <x-share-buttons :url="route('events.show', $event)" :title="$event->judul" />

            <div class="mt-5 space-y-3 text-sm leading-relaxed text-cream/90">
                {!! nl2br(e($event->deskripsi)) !!}
            </div>

            <div class="mt-6 border-t border-line/20 pt-5">
                @auth
                    @if ($myReg)
                        <p class="text-sm text-neon">✓ Kamu sudah terdaftar di event ini.</p>
                        <a href="{{ route('tickets.show', $myReg->kode_tiket) }}" class="btn-neon mt-2">Lihat E-Ticket (QR)</a>
                    @elseif ($event->status === 'publish' && $event->mulai->isFuture())
                        <form method="POST" action="{{ route('events.register', $event) }}">
                            @csrf
                            <button class="btn-neon w-full sm:w-auto">Daftar & Dapat E-Ticket 🎟️</button>
                        </form>
                    @else
                        <p class="text-sm text-creamDim">Pendaftaran ditutup (event sudah berlalu).</p>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn-neon">Login untuk mendaftar</a>
                @endauth
            </div>

            @role('super_admin|pengurus')
                <div class="mt-5 flex flex-wrap gap-2 border-t border-line/20 pt-4">
                    <a href="{{ route('admin.event.checkin', $event) }}" class="btn-outline">📱 Check-in QR</a>
                    <a href="{{ route('admin.event.edit', $event) }}" class="btn-outline">Edit</a>
                    <form method="POST" action="{{ route('admin.event.destroy', $event) }}" onsubmit="return confirm('Hapus event?')">
                        @csrf @method('DELETE')
                        <button class="btn-danger">Hapus</button>
                    </form>
                </div>
            @endrole
        </div>
    </div>
</x-app-layout>
