<x-app-layout>
    <div class="wrap py-8">
        <a href="{{ route('admin.index') }}" class="font-mono text-xs text-neon hover:underline">← panel admin</a>
        <h1 class="mt-2 font-display text-2xl text-cream sm:text-3xl">Setujui akun baru ⏳</h1>
        <p class="mt-1 text-sm text-creamDim">
            Pendaftar baru <b class="text-cream">tidak bisa login</b> sampai kamu menyetujui akunnya.
            Saat disetujui, sistem otomatis mengirim email selamat datang (sesuai pengaturan SMTP).
        </p>

        @if (session('success'))<div class="flash-ok mt-4">{!! session('success') !!}</div>@endif
        @if (session('error'))<div class="flash-err mt-4">{{ session('error') }}</div>@endif

        <div class="card mt-6">
            <div class="kicker">👥 Menunggu persetujuan ({{ $users->count() }})</div>

            @if ($users->isEmpty())
                <p class="mt-4 text-sm text-creamDim">
                    Tidak ada pendaftar menunggu — semua beres! ✓
                </p>
            @else
                <div class="mt-3">
                    @foreach ($users as $u)
                        <div class="border-t border-line/10 py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                {{-- Info pendaftar --}}
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="avatar h-10 w-10">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                                        <div>
                                            <div class="font-semibold text-cream">
                                                {{ $u->name }}
                                                @if ($u->approval_note)
                                                    <span class="chip-line ml-1 !py-0.5 !text-red-300" style="border-color:rgba(255,143,143,.4)">ditolak sebelumnya</span>
                                                @endif
                                            </div>
                                            <div class="mt-0.5 font-mono text-[11px] text-creamDim">{{ $u->email }}</div>
                                            <div class="mt-0.5 font-mono text-[10px] text-creamDim/80">
                                                {{ $u->profile?->kelas ?? '—' }}{{ $u->angkatan?->tahun ? ' · Angkatan '.$u->angkatan->tahun : ($u->profile?->kelas === 'Bestie 67' ? '' : ' · Bestie/non-angkatan') }}
                                                · daftar {{ $u->created_at->translatedFormat('d M Y H:i') }}
                                            </div>
                                            @if ($u->approval_note)
                                                <div class="mt-1 text-xs text-red-300/90">Alasan tolak: {{ $u->approval_note }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Aksi --}}
                                <div class="flex shrink-0 items-center gap-2">
                                    <form method="POST" action="{{ route('admin.users.approve', $u) }}"
                                          onsubmit="return confirm('Setujui akun {{ $u->name }}? Email notifikasi akan dikirim.')">
                                        @csrf
                                        <button type="submit" class="btn-neon !px-3 !py-1.5 text-xs">✓ Setujui</button>
                                    </form>
                                    <details class="relative">
                                        <summary class="btn-outline cursor-pointer list-none !px-3 !py-1.5 text-xs">✗ Tolak</summary>
                                        <form method="POST" action="{{ route('admin.users.reject', $u) }}"
                                              class="card absolute right-0 z-10 mt-2 w-72 space-y-3">
                                            @csrf
                                            <div>
                                                <label for="alasan-{{ $u->id }}" class="label">Alasan penolakan</label>
                                                <textarea id="alasan-{{ $u->id }}" name="alasan" rows="3"
                                                          class="input"
                                                          placeholder="Misal: data kelas tidak ditemukan di angkatan 2003">{{ old('alasan') }}</textarea>
                                            </div>
                                            <button type="submit" class="btn-danger w-full !py-2 text-xs">Kirim Penolakan</button>
                                            <p class="text-[10px] text-creamDim/70">Alasan dikirim ke email pendaftar (bila SMTP aktif). Akun tetap tidak bisa login.</p>
                                        </form>
                                    </details>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
