<x-app-layout>
    <div class="wrap max-w-3xl py-8">
        <a href="{{ route('jobs.index') }}" class="font-mono text-xs text-neon hover:underline">← semua lowongan</a>

        <div class="card mt-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <h1 class="font-display text-2xl text-cream">{{ $job->judul }}</h1>
                <span class="chip-line">{{ ucfirst($job->tipe) }}</span>
            </div>
            <p class="mt-1 text-sm text-creamDim">{{ $job->perusahaan }}@if($job->lokasi) · 📍 {{ $job->lokasi }}@endif</p>

            <div class="mt-3 flex flex-wrap gap-1.5">
                <span class="chip-neon">{{ $job->kategori }}</span>
                @if ($job->gaji_min || $job->gaji_max)
                    <span class="chip-line">gaji Rp {{ number_format($job->gaji_min ?? 0, 0, ',', '.') }}{{ $job->gaji_max ? ' – '.number_format($job->gaji_max, 0, ',', '.') : '+' }}</span>
                @endif
                <span class="chip-line">{{ $job->created_at->translatedFormat('d M Y') }}</span>
            </div>

            <div class="mt-5 space-y-3 text-sm leading-relaxed text-cream/90">
                {!! nl2br(e($job->deskripsi)) !!}
            </div>

            @if ($job->cara_melamar)
                <div class="card-plain mt-5">
                    <div class="label">Cara melamar</div>
                    <p class="text-sm text-cream/90">{!! nl2br(e($job->cara_melamar)) !!}</p>
                </div>
            @endif

            <p class="mt-5 border-t border-line/20 pt-4 font-mono text-[10px] text-creamDim/70">
                diposting oleh {{ $job->user?->name }}@if($job->user?->profile?->kelas) ({{ $job->user->profile->kelas }})@endif
                — seangkatan? <a href="{{ route('direktori.show', $job->user) }}" class="text-neon hover:underline">lihat profil</a>
            </p>
        </div>
    </div>
</x-app-layout>
