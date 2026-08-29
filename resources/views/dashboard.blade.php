<x-app-layout>
    <div class="wrap py-8">
        {{-- Sapaan --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <div class="kicker">Dashboard alumni</div>
                <h1 class="font-display text-2xl text-cream sm:text-3xl">
                    Halo, {{ explode(' ', auth()->user()->name)[0] }}! 👋
                </h1>
                <p class="mt-1 text-sm text-creamDim">
                    {{ auth()->user()->angkatan?->nama ?? 'Belum pilih angkatan' }}
                    @if (auth()->user()->profile?->isVerified())
                        <span class="badge-verified ml-1">✓ Terverifikasi</span>
                    @endif
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('forum.create') }}" class="btn-outline">Buat Diskusi</a>
                <a href="{{ route('jobs.create') }}" class="btn-neon">+ Pasang Lowongan</a>
            </div>
        </div>

        {{-- Flash --}}
        @if (session('success'))
            <div class="flash-ok mt-4">{{ session('success') }}</div>
        @endif

        {{-- Statistik singkat --}}
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="card-plain">
                <div class="font-display text-3xl text-neon">{{ $jobs->count() }}</div>
                <div class="font-mono text-[11px] uppercase text-creamDim">Lowongan aktif</div>
            </div>
            <div class="card-plain">
                <div class="font-display text-3xl text-neon">{{ $events->count() }}</div>
                <div class="font-mono text-[11px] uppercase text-creamDim">Event mendatang</div>
            </div>
            <div class="card-plain">
                <div class="font-display text-3xl text-neon">{{ \App\Models\User::whereHas('profile')->count() }}</div>
                <div class="font-mono text-[11px] uppercase text-creamDim">Alumni terdaftar</div>
            </div>
            <div class="card-plain">
                <div class="font-display text-3xl text-neon">{{ $posts->count() }}</div>
                <div class="font-mono text-[11px] uppercase text-creamDim">Postingan baru</div>
            </div>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1.2fr_.8fr]">
            <div class="space-y-8">
                {{-- Berita --}}
                <section>
                    <div class="flex items-center justify-between">
                        <div class="kicker !mb-0">Berita & pengumuman</div>
                        <a href="{{ route('berita.index') }}" class="font-mono text-xs text-neon hover:underline">lihat semua →</a>
                    </div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach ($beritas as $b)
                            <a href="{{ route('berita.show', $b) }}" class="card transition hover:border-neon">
                                @if ($b->is_pinned)<span class="badge-pending">📌 penting</span>@endif
                                <h3 class="mt-1 font-display text-base leading-snug text-cream">{{ $b->judul }}</h3>
                                <p class="mt-1.5 line-clamp-2 text-xs text-creamDim">{{ $b->ringkasan }}</p>
                                <p class="mt-2 font-mono text-[10px] text-creamDim/60">{{ $b->published_at?->translatedFormat('d M Y H:i') }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>

                {{-- Feed terbaru --}}
                <section>
                    <div class="flex items-center justify-between">
                        <div class="kicker !mb-0">Aktivitas terbaru</div>
                        <a href="{{ route('feed.index') }}" class="font-mono text-xs text-neon hover:underline">buka feed →</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($posts as $p)
                            <div class="card-plain">
                                <div class="flex items-center gap-3">
                                    <div class="avatar">{{ strtoupper(substr($p->user->name, 0, 1)) }}</div>
                                    <div>
                                        <div class="text-sm font-semibold text-cream">{{ $p->user->name }}</div>
                                        <div class="font-mono text-[10px] text-creamDim/70">{{ $p->created_at->translatedFormat('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                <p class="mt-3 line-clamp-3 text-sm text-cream/90">{{ $p->isi }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-creamDim">Belum ada aktivitas. Jadilah yang pertama posting!</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="space-y-8">
                {{-- Agenda --}}
                <section>
                    <div class="flex items-center justify-between">
                        <div class="kicker !mb-0">Agenda mendatang</div>
                        <a href="{{ route('events.index') }}" class="font-mono text-xs text-neon hover:underline">semua →</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($events as $e)
                            <a href="{{ route('events.show', $e) }}" class="card-plain block transition hover:border-neon">
                                <div class="text-sm font-semibold text-cream">{{ $e->judul }}</div>
                                <div class="mt-1 font-mono text-[10px] text-creamDim">
                                    {{ $e->mulai->translatedFormat('D, d M Y · H:i') }} · {{ $e->lokasi }}
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-creamDim">Belum ada agenda.</p>
                        @endforelse
                    </div>
                </section>

                {{-- Lowongan --}}
                <section>
                    <div class="flex items-center justify-between">
                        <div class="kicker !mb-0">Lowongan baru</div>
                        <a href="{{ route('jobs.index') }}" class="font-mono text-xs text-neon hover:underline">semua →</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($jobs as $j)
                            <a href="{{ route('jobs.show', $j) }}" class="card-plain block transition hover:border-neon">
                                <div class="text-sm font-semibold text-cream">{{ $j->judul }}</div>
                                <div class="mt-1 flex flex-wrap gap-1.5">
                                    <span class="chip-line !px-2 !py-0.5">{{ $j->kategori }}</span>
                                    <span class="chip-line !px-2 !py-0.5">{{ $j->tipe }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-creamDim">Belum ada lowongan.</p>
                        @endforelse
                    </div>
                </section>

                {{-- Ulang tahun bulan ini --}}
                @if ($birthday->isNotEmpty())
                    <section>
                        <div class="kicker !mb-4">🎂 Ulang tahun bulan ini</div>
                        <div class="card-plain space-y-2">
                            @foreach ($birthday as $u)
                                <a href="{{ route('direktori.show', $u) }}" class="flex items-center gap-3 text-sm text-cream hover:text-neon">
                                    <div class="avatar h-8 w-8 text-xs">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                                    {{ $u->name }}
                                    <span class="ml-auto font-mono text-[10px] text-creamDim">{{ $u->profile->tgl_lahir?->translatedFormat('d M') }}</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
