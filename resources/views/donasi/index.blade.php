<x-app-layout>
    <div class="wrap py-8">
        <div class="kicker">Donasi & sosial</div>
        <h1 class="font-display text-2xl text-cream sm:text-3xl">Bantu teman seangkatan ❤️</h1>
        <p class="mt-1 text-sm text-creamDim">Campaign transparan: progress bar, bukti transfer, dan laporan penggunaan dana.</p>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            @forelse ($campaigns as $c)
                <a href="{{ route('donasi.show', $c) }}" class="card transition hover:border-neon">
                    <h2 class="font-display text-lg text-cream">{{ $c->judul }}</h2>
                    <p class="mt-1 line-clamp-2 text-sm text-creamDim">{{ Str::limit(strip_tags($c->deskripsi), 120) }}</p>

                    {{-- Progress bar --}}
                    @php $pct = $c->progressPercent(); @endphp
                    <div class="mt-4">
                        <div class="flex justify-between font-mono text-[11px]">
                            <span class="text-neon">Rp {{ number_format($c->terkumpul(), 0, ',', '.') }}</span>
                            <span class="text-creamDim">target Rp {{ number_format($c->target, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-navy-deep">
                            <div class="h-full rounded-full bg-neon transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="mt-1.5 flex justify-between font-mono text-[10px] text-creamDim/70">
                            <span>{{ $pct }}% tercapai</span>
                            <span>{{ $c->transactions_count }} donatur</span>
                        </div>
                    </div>
                    @if ($c->deadline)
                        <p class="mt-3 font-mono text-[10px] text-creamDim/60">⏳ s/d {{ $c->deadline->translatedFormat('d M Y') }}</p>
                    @endif
                </a>
            @empty
                <p class="text-sm text-creamDim sm:col-span-2">Belum ada campaign aktif.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $campaigns->links() }}</div>
    </div>
</x-app-layout>
