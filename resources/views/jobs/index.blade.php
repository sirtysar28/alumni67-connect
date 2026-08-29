<x-app-layout>
    <div class="wrap py-8">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <div class="kicker">Bursa kerja alumni</div>
                <h1 class="font-display text-2xl text-cream sm:text-3xl">#HiringAlumniPrioritasAlumni 💼</h1>
            </div>
            @auth
                <a href="{{ route('jobs.create') }}" class="btn-neon">+ Pasang Lowongan</a>
            @endauth
        </div>

        <form class="card mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" method="GET">
            <input class="input" type="text" name="q" placeholder="🔎 Cari posisi / perusahaan…" value="{{ request('q') }}">
            <select class="input" name="kategori">
                <option value="">Semua kategori</option>
                @foreach (\App\Http\Controllers\JobController::KATEGORI as $k)
                    <option value="{{ $k }}" @selected(request('kategori') === $k)>{{ $k }}</option>
                @endforeach
            </select>
            <select class="input" name="tipe">
                <option value="">Semua tipe</option>
                @foreach (\App\Http\Controllers\JobController::TIPE as $t)
                    <option value="{{ $t }}" @selected(request('tipe') === $t)>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            <button class="btn-neon">Terapkan</button>
        </form>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            @forelse ($jobs as $j)
                <a href="{{ route('jobs.show', $j) }}" class="card transition hover:border-neon">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="font-display text-lg leading-snug text-cream">{{ $j->judul }}</h2>
                        <span class="chip-line shrink-0">{{ $j->tipe }}</span>
                    </div>
                    <p class="mt-1 text-sm text-creamDim">{{ $j->perusahaan }}@if($j->lokasi) · {{ $j->lokasi }}@endif</p>
                    <p class="mt-2 line-clamp-2 text-sm text-cream/80">{{ Str::limit(strip_tags($j->deskripsi), 120) }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-1.5">
                        <span class="chip-neon">{{ $j->kategori }}</span>
                        @if ($j->gaji_min || $j->gaji_max)
                            <span class="chip-line">
                                Rp {{ number_format($j->gaji_min ?? 0, 0, ',', '.') }}{{ $j->gaji_max ? ' – '.number_format($j->gaji_max, 0, ',', '.') : '+' }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-3 font-mono text-[10px] text-creamDim/60">oleh {{ $j->user?->name }} · {{ $j->created_at->translatedFormat('d M Y') }}</p>
                </a>
            @empty
                <p class="text-sm text-creamDim sm:col-span-2">Belum ada lowongan yang cocok.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $jobs->links() }}</div>
    </div>
</x-app-layout>
