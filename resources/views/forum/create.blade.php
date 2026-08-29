<x-app-layout>
    <div class="wrap max-w-2xl py-8">
        <a href="{{ route('forum.index') }}" class="font-mono text-xs text-neon hover:underline">← kembali</a>
        <h1 class="mt-2 font-display text-2xl text-cream">Buat Diskusi Baru 💬</h1>

        <form method="POST" action="{{ route('forum.store') }}" class="card mt-6 space-y-4">
            @csrf
            <div>
                <label class="label" for="judul">Judul *</label>
                <input id="judul" name="judul" class="input @error('judul') input-error @enderror" value="{{ old('judul') }}" required>
                @error('judul')<p class="error-text">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="kategori">Kategori *</label>
                <select id="kategori" name="kategori" class="input" required>
                    @foreach (\App\Http\Controllers\ForumController::KATEGORI as $k)
                        <option value="{{ $k }}" @selected(old('kategori') === $k)>{{ ucfirst($k) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="isi">Isi diskusi *</label>
                <textarea id="isi" name="isi" rows="6" class="input @error('isi') input-error @enderror" required>{{ old('isi') }}</textarea>
                @error('isi')<p class="error-text">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-creamDim">
                <input type="checkbox" name="is_anonymous" value="1" class="rounded border-line/30 bg-navy-deep text-neon focus:ring-neon" @checked(old('is_anonymous'))>
                🎭 Posting sebagai <b class="text-cream">Anonim</b>
            </label>
            <button class="btn-neon w-full">Posting Diskusi</button>
        </form>
    </div>
</x-app-layout>
