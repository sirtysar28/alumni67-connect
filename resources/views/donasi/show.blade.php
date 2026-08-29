<x-app-layout>
    <div class="wrap max-w-4xl py-8">
        <a href="{{ route('donasi.index') }}" class="font-mono text-xs text-neon hover:underline">← semua campaign</a>

        <div class="mt-4 grid gap-6 lg:grid-cols-[1.2fr_.8fr]">
            <div>
                <div class="card">
                    <h1 class="font-display text-2xl text-cream">{{ $campaign->judul }}</h1>
                    <div class="mt-3 space-y-3 text-sm leading-relaxed text-cream/90">
                        {!! nl2br(e($campaign->deskripsi)) !!}
                    </div>
                </div>

                {{-- Laporan transparan --}}
                <div class="card mt-5">
                    <div class="kicker">Laporan donasi (transparan)</div>
                    <div class="mt-3 space-y-2.5">
                        @forelse ($transactions as $t)
                            <div class="flex items-center justify-between gap-3 border-b border-line/15 pb-2.5 text-sm">
                                <div class="min-w-0">
                                    <span class="text-cream">{{ $t->labelDonatur() }}</span>
                                    @if ($t->pesan)<p class="truncate text-xs text-creamDim">"{{ $t->pesan }}"</p>@endif
                                </div>
                                <span class="shrink-0 font-mono text-neon">Rp {{ number_format($t->amount, 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-creamDim">Belum ada donasi terverifikasi.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div>
                {{-- Progress + form donasi --}}
                <div class="card sticky top-20">
                    @php $pct = $campaign->progressPercent(); @endphp
                    <div class="flex justify-between font-mono text-xs">
                        <span class="text-neon">Rp {{ number_format($campaign->terkumpul(), 0, ',', '.') }}</span>
                        <span class="text-creamDim">/ Rp {{ number_format($campaign->target, 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-navy-deep">
                        <div class="h-full rounded-full bg-neon" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="mt-1.5 font-mono text-[10px] text-creamDim/70">{{ $pct }}% tercapai dari target</p>

                    <form method="POST" action="{{ route('donasi.donate', $campaign) }}" enctype="multipart/form-data" class="mt-5 space-y-3 border-t border-line/20 pt-4">
                        @csrf
                        <div>
                            <label class="label" for="amount">Nominal donasi (Rp) *</label>
                            <input id="amount" name="amount" type="number" min="1000" step="1000" class="input @error('amount') input-error @enderror" value="{{ old('amount', 100000) }}" required>
                            @error('amount')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="nama_donatur">Nama (kosongkan bila anonim)</label>
                            <input id="nama_donatur" name="nama_donatur" class="input" value="{{ old('nama_donatur', auth()->user()->name ?? '') }}">
                        </div>
                        <div>
                            <label class="label" for="pesan">Pesan / doa</label>
                            <input id="pesan" name="pesan" class="input" value="{{ old('pesan') }}" placeholder="Semoga membantu!">
                        </div>
                        <div>
                            <label class="label" for="bukti">Bukti transfer (opsional)</label>
                            <input id="bukti" name="bukti" type="file" accept="image/*" class="block w-full text-xs text-creamDim file:mr-3 file:rounded file:border-0 file:bg-navy-deep file:px-3 file:py-1.5 file:text-xs file:text-neon">
                            <p class="mt-1 text-[10px] text-creamDim/70">Transfer ke rekening panitia, lalu upload bukti — donasi diverifikasi pengurus.</p>
                        </div>
                        <button class="btn-neon w-full">Kirim Donasi 💚</button>
                    </form>

                    @if (session('success'))<div class="flash-ok mt-3">{{ session('success') }}</div>@endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
