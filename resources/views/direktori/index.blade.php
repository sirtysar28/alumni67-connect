<x-app-layout>
    <div class="wrap py-8">
        <div class="kicker">Direktori alumni</div>
        <h1 class="font-display text-2xl text-cream sm:text-3xl">Temukan teman seangkatan & relasi 🤝</h1>
        <p class="mt-1 text-sm text-creamDim">Networking power: filter berdasarkan kelas, profesi, atau kota.</p>

        {{-- Filter --}}
        <form class="card mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5" method="GET">
            <input class="input lg:col-span-2" type="text" name="q" placeholder="🔎 Cari nama / perusahaan / skill…" value="{{ request('q') }}">
            <select class="input" name="kelas">
                <option value="">Semua kelas</option>
                @foreach (\App\Http\Controllers\DirectoryController::KELAS as $k)
                    <option value="{{ $k }}" @selected(request('kelas') === $k)>{{ $k }}</option>
                @endforeach
            </select>
            <select class="input" name="bidang">
                <option value="">Semua bidang</option>
                @foreach (\App\Http\Controllers\DirectoryController::BIDANG as $b)
                    <option value="{{ $b }}" @selected(request('bidang') === $b)>{{ $b }}</option>
                @endforeach
            </select>
            <select class="input" name="kota">
                <option value="">Semua kota</option>
                @foreach ($kotaList as $k)
                    <option value="{{ $k }}" @selected(request('kota') === $k)>{{ $k }}</option>
                @endforeach
            </select>
            <button class="btn-neon sm:col-span-2 lg:col-span-1">Terapkan</button>
        </form>

        {{-- Grid alumni --}}
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($users as $u)
                <a href="{{ route('direktori.show', $u) }}" class="card transition hover:border-neon">
                    <div class="flex items-start gap-3">
                        @if ($u->profile->foto)
                            <img src="{{ Storage::url($u->profile->foto) }}" alt="{{ $u->name }}" class="h-12 w-12 rounded-full object-cover">
                        @else
                            <div class="avatar h-12 w-12">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                        @endif
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 font-semibold text-cream">
                                <span class="truncate">{{ $u->name }}</span>
                                @if ($u->profile->isVerified())<span class="badge-verified">✓</span>@endif
                            </div>
                            <div class="font-mono text-[10px] text-creamDim">
                                {{ $u->angkatan?->nama ?? '—' }} @if($u->profile->kelas)· {{ $u->profile->kelas }}@endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-sm text-cream/90">{{ $u->profile->pekerjaan ?: 'Belum isi pekerjaan' }}</div>
                    <div class="text-xs text-creamDim">{{ $u->profile->perusahaan }}@if($u->profile->kota) · {{ $u->profile->kota }}@endif</div>
                    @if ($u->profile->bidang)
                        <span class="chip-line mt-3">{{ $u->profile->bidang }}</span>
                    @endif
                    @unless (auth()->check())
                        <span class="chip-line mt-3">🔒 Login untuk lihat detail</span>
                    @endunless
                </a>
            @empty
                <p class="text-sm text-creamDim sm:col-span-2 lg:col-span-3">Tidak ada alumni yang cocok dengan filter.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $users->links() }}</div>
    </div>
</x-app-layout>
