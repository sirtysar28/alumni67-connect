<x-app-layout>
    <div class="wrap max-w-3xl py-8">
        <a href="{{ route('admin.index') }}" class="font-mono text-xs text-neon hover:underline">← panel admin</a>
        <h1 class="mt-2 font-display text-2xl text-cream">Verifikasi Donasi 💚</h1>

        @if (session('success'))<div class="flash-ok mt-4">{{ session('success') }}</div>@endif

        <div class="mt-6 space-y-4">
            @forelse ($trx as $t)
                <div class="card">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="font-mono text-lg text-neon">Rp {{ number_format($t->amount, 0, ',', '.') }}</div>
                            <div class="text-sm text-cream">{{ $t->labelDonatur() }} → {{ $t->campaign->judul }}</div>
                            @if ($t->pesan)<p class="text-xs text-creamDim">"{{ $t->pesan }}"</p>@endif
                        </div>
                        @if ($t->bukti_path)
                            <a href="{{ Storage::url($t->bukti_path) }}" target="_blank" class="btn-outline !px-3 !py-1.5 text-xs">Lihat Bukti ↗</a>
                        @else
                            <span class="chip-line">tanpa bukti</span>
                        @endif
                    </div>
                    <div class="mt-4 flex gap-2">
                        <form method="POST" action="{{ route('admin.donations.verify', $t) }}">@csrf
                            <button class="btn-neon !px-4 !py-2 text-xs">✓ Verifikasi</button>
                        </form>
                        <form method="POST" action="{{ route('admin.donations.reject', $t) }}">@csrf
                            <button class="btn-danger !px-4 !py-2 text-xs">Tolak</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-creamDim">Tidak ada donasi menunggu verifikasi. 👍</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
