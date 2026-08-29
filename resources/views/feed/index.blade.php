<x-app-layout>
    <div class="wrap max-w-2xl py-8">
        <div class="kicker">Timeline</div>
        <h1 class="font-display text-2xl text-cream sm:text-3xl">Feed Alumni 📣</h1>

        {{-- Buat postingan --}}
        <form method="POST" action="{{ route('feed.store') }}" enctype="multipart/form-data" class="card mt-6">
            @csrf
            <textarea name="isi" rows="3" class="input @error('isi') input-error @enderror" placeholder="Apa kabar hari ini? Ceritakan sesuatu…">{{ old('isi') }}</textarea>
            @error('isi')<p class="error-text">{{ $message }}</p>@enderror
            <div class="mt-3 flex items-center justify-between gap-3">
                <input type="file" name="gambar" accept="image/*" class="block w-full text-xs text-creamDim file:mr-3 file:rounded file:border-0 file:bg-navy-deep file:px-3 file:py-1.5 file:text-xs file:text-neon">
                <button class="btn-neon shrink-0">Posting</button>
            </div>
        </form>

        @if (session('success'))<div class="flash-ok mt-4">{{ session('success') }}</div>@endif

        {{-- Daftar post --}}
        <div class="mt-6 space-y-5">
            @forelse ($posts as $p)
                <div class="card">
                    <div class="flex items-center gap-3">
                        @if ($p->user->profile->foto)
                            <img src="{{ Storage::url($p->user->profile->foto) }}" class="h-10 w-10 rounded-full object-cover" alt="">
                        @else
                            <div class="avatar">{{ strtoupper(substr($p->user->name, 0, 1)) }}</div>
                        @endif
                        <div class="min-w-0">
                            <a href="{{ route('direktori.show', $p->user) }}" class="text-sm font-semibold text-cream hover:text-neon">{{ $p->user->name }}</a>
                            @if ($p->user->profile->isVerified())<span class="badge-verified ml-1">✓</span>@endif
                            <div class="font-mono text-[10px] text-creamDim/70">{{ $p->created_at->translatedFormat('d M Y · H:i') }} · {{ $p->user->angkatan?->nama }}</div>
                        </div>
                    </div>

                    <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-cream/90">{{ $p->isi }}</p>

                    @if ($p->gambar)
                        <img src="{{ Storage::url($p->gambar) }}" class="mt-3 w-full rounded" alt="lampiran">
                    @endif

                    {{-- Aksi: like & komentar --}}
                    <div class="mt-3 flex items-center gap-4 border-t border-line/20 pt-3 text-xs text-creamDim">
                        <form method="POST" action="{{ route('feed.like', $p) }}">
                            @csrf
                            <button class="flex items-center gap-1.5 hover:text-neon {{ $p->isLikedBy(auth()->user()) ? 'text-neon' : '' }}">
                                {{ $p->isLikedBy(auth()->user()) ? '❤️' : '🤍' }} <span class="font-mono">{{ $p->likes_count }}</span>
                            </button>
                        </form>
                        <span class="flex items-center gap-1.5">💬 <span class="font-mono">{{ $p->comments_count }}</span></span>
                    </div>

                    {{-- Komentar --}}
                    @if ($p->comments->isNotEmpty())
                        <div class="mt-3 space-y-2.5 border-t border-line/20 pt-3">
                            @foreach ($p->comments as $c)
                                <div class="flex gap-2.5">
                                    <div class="avatar h-7 w-7 text-[10px]">{{ strtoupper(substr($c->user->name, 0, 1)) }}</div>
                                    <div class="min-w-0 flex-1 rounded bg-navy-deep/60 px-3 py-2">
                                        <span class="text-xs font-semibold text-cream">{{ $c->user->name }}</span>
                                        <p class="mt-0.5 text-xs text-cream/85">{{ $c->isi }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('feed.comment', $p) }}" class="mt-3 flex gap-2">
                        @csrf
                        <input name="isi" class="input !py-2 text-xs" placeholder="Tulis komentar…" required>
                        <button class="btn-outline shrink-0 !px-3 !py-2 text-xs">Kirim</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-creamDim">Feed masih kosong. Mulai postingan pertama! ✨</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $posts->links() }}</div>
    </div>
</x-app-layout>
