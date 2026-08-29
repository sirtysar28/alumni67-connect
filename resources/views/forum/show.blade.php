<x-app-layout>
    <div class="wrap max-w-2xl py-8">
        <a href="{{ route('forum.index') }}" class="font-mono text-xs text-neon hover:underline">← semua diskusi</a>

        <div class="card mt-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="chip-line">{{ $thread->kategori }}</span>
                @if ($thread->is_anonymous)<span class="chip-line">🎭 anonim</span>@endif
                @if ($thread->angkatan)<span class="chip-neon">{{ $thread->angkatan->nama }}</span>@endif
            </div>
            <h1 class="mt-2 font-display text-xl text-cream sm:text-2xl">{{ $thread->judul }}</h1>
            <p class="mt-1 font-mono text-[10px] text-creamDim/70">{{ $thread->displayName() }} · {{ $thread->created_at->translatedFormat('d M Y H:i') }}</p>
            <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-cream/90">{{ $thread->isi }}</p>
        </div>

        {{-- Balasan --}}
        <div class="mt-6 space-y-3">
            <div class="kicker !mb-0">{{ $thread->replies->count() }} balasan</div>
            @forelse ($thread->replies as $r)
                <div class="card-plain">
                    <div class="flex items-center gap-2.5">
                        @unless ($r->is_anonymous)
                            <div class="avatar h-8 w-8 text-[11px]">{{ strtoupper(substr($r->displayName(), 0, 1)) }}</div>
                        @else
                            <div class="avatar h-8 w-8 text-[11px]">🎭</div>
                        @endunless
                        <div>
                            <span class="text-sm font-semibold text-cream">{{ $r->displayName() }}</span>
                            <span class="ml-2 font-mono text-[10px] text-creamDim/60">{{ $r->created_at->translatedFormat('d M H:i') }}</span>
                        </div>
                    </div>
                    <p class="mt-2 whitespace-pre-line text-sm text-cream/85">{{ $r->isi }}</p>
                </div>
            @empty
                <p class="text-sm text-creamDim">Belum ada balasan — jadilah yang pertama!</p>
            @endforelse
        </div>

        @auth
            @if (session('success'))<div class="flash-ok mt-4">{{ session('success') }}</div>@endif
            <form method="POST" action="{{ route('forum.reply', $thread) }}" class="card mt-4 space-y-3">
                @csrf
                <label class="label" for="isi">Balas diskusi</label>
                <textarea id="isi" name="isi" rows="4" class="input @error('isi') input-error @enderror" placeholder="Tulis balasanmu…" required>{{ old('isi') }}</textarea>
                @error('isi')<p class="error-text">{{ $message }}</p>@enderror
                <label class="flex items-center gap-2 text-sm text-creamDim">
                    <input type="checkbox" name="is_anonymous" value="1" class="rounded border-line/30 bg-navy-deep text-neon focus:ring-neon">
                    🎭 Balas sebagai <b class="text-cream">Anonim</b>
                </label>
                <button class="btn-neon">Kirim Balasan</button>
            </form>
        @else
            <p class="mt-4 text-sm text-creamDim"><a href="{{ route('login') }}" class="text-neon hover:underline">Login</a> untuk ikut berdiskusi.</p>
        @endauth
    </div>
</x-app-layout>
