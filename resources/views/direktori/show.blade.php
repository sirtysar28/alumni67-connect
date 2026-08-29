<x-app-layout>
    <div class="wrap py-8">
        <a href="{{ route('direktori.index') }}" class="font-mono text-xs text-neon hover:underline">← kembali ke direktori</a>

        <div class="card mt-4">
            <div class="flex flex-wrap items-start gap-5">
                @if ($alumni->profile->foto)
                    <img src="{{ Storage::url($alumni->profile->foto) }}" class="h-20 w-20 rounded-full object-cover" alt="{{ $alumni->name }}">
                @else
                    <div class="avatar h-20 w-20 text-2xl">{{ strtoupper(substr($alumni->name, 0, 1)) }}</div>
                @endif
                <div class="min-w-0 flex-1">
                    <h1 class="flex items-center gap-2 font-display text-2xl text-cream">
                        {{ $alumni->name }}
                        @if ($alumni->profile->isVerified())<span class="badge-verified">✓ Verified Alumni</span>@endif
                    </h1>
                    <p class="mt-1 text-sm text-creamDim">
                        {{ $alumni->profile->pekerjaan ?: '—' }}@if($alumni->profile->perusahaan) · {{ $alumni->profile->perusahaan }}@endif
                    </p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @if ($alumni->angkatan)<span class="chip-neon">{{ $alumni->angkatan->nama }}</span>@endif
                        @if ($alumni->profile->kelas)<span class="chip-line">{{ $alumni->profile->kelas }}</span>@endif
                        @if ($alumni->profile->kota)<span class="chip-line">📍 {{ $alumni->profile->kota }}</span>@endif
                        @if ($alumni->profile->bidang)<span class="chip-line">{{ $alumni->profile->bidang }}</span>@endif
                    </div>
                </div>
            </div>

            @if ($alumni->profile->bio)
                <p class="mt-5 text-sm leading-relaxed text-cream/90">{{ $alumni->profile->bio }}</p>
            @endif

            @if ($alumni->profile->skill_array)
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($alumni->profile->skill_array as $s)
                        <span class="chip-line">⚡ {{ $s }}</span>
                    @endforeach
                </div>
            @endif

            {{-- Info detail: kontak & usaha hanya untuk yang login --}}
            <div class="mt-6 grid gap-4 border-t border-line/20 pt-5 sm:grid-cols-2">
                <div>
                    <div class="label">Kampus</div>
                    <p class="text-sm text-cream/90">{{ $alumni->profile->kampus ?: '—' }}</p>
                    <div class="label mt-3">Sosial media</div>
                    <p class="flex flex-wrap gap-3 text-sm">
                        @if ($alumni->profile->instagram)
                            <a href="https://instagram.com/{{ $alumni->profile->instagram }}" target="_blank" rel="noopener" class="text-neon hover:underline">📷 IG: {{ $alumni->profile->instagram }}</a>
                        @endif
                        @if ($alumni->profile->linkedin)
                            <a href="{{ $alumni->profile->linkedin }}" target="_blank" rel="noopener" class="text-neon hover:underline">💼 LinkedIn</a>
                        @endif
                        @unless ($alumni->profile->instagram || $alumni->profile->linkedin)<span class="text-creamDim">—</span>@endunless
                    </p>
                </div>
                <div>
                    @auth
                        <div class="label">Nomor WhatsApp</div>
                        <p class="text-sm text-cream/90">{{ $alumni->profile->no_wa ?: 'Belum diisi' }}</p>
                    @else
                        <div class="card-plain text-sm text-creamDim">
                            🔒 <a href="{{ route('login') }}" class="text-neon hover:underline">Login</a> untuk melihat kontak alumni.
                        </div>
                    @endauth

                    @if ($alumni->profile->usaha_nama)
                        <div class="label mt-3">Usaha — #BisnisAlumni67</div>
                        <div class="card-plain">
                            <div class="text-sm font-semibold text-cream">🛍️ {{ $alumni->profile->usaha_nama }}</div>
                            <p class="mt-1 text-xs text-creamDim">{{ $alumni->profile->usaha_deskripsi }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
