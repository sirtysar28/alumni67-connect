<x-app-layout>
    <div class="wrap max-w-3xl py-8">
        <a href="{{ route('admin.index') }}" class="font-mono text-xs text-neon hover:underline">← panel admin</a>
        <h1 class="mt-2 font-display text-2xl text-cream">Verifikasi Badge Alumni ✓</h1>

        @if (session('success'))<div class="flash-ok mt-4">{{ session('success') }}</div>@endif

        <div class="mt-6 space-y-4">
            @forelse ($profiles as $p)
                <div class="card">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="font-semibold text-cream">{{ $p->user->name }}</div>
                            <div class="font-mono text-[11px] text-creamDim">
                                NIS {{ $p->nis }} · {{ $p->user->angkatan?->nama ?? 'angkatan ?' }} · {{ $p->kelas ?? 'kelas ?' }} · lulus {{ $p->tahun_lulus ?? '?' }}
                            </div>
                        </div>
                        @if ($p->ijazah_path)
                            <a href="{{ Storage::url($p->ijazah_path) }}" target="_blank" class="btn-outline !px-3 !py-1.5 text-xs">Lihat Ijazah ↗</a>
                        @else
                            <span class="chip-line">tanpa lampiran</span>
                        @endif
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('admin.verifications.approve', $p) }}">@csrf
                            <button class="btn-neon !px-4 !py-2 text-xs">✓ Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('admin.verifications.reject', $p) }}" class="flex gap-2">
                            @csrf
                            <input name="catatan" class="input !w-56 !py-2 text-xs" placeholder="alasan penolakan…">
                            <button class="btn-danger !px-4 !py-2 text-xs">Tolak</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-creamDim">Tidak ada pengajuan verifikasi. 👍</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
