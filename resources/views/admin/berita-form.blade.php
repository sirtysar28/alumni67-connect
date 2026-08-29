<x-app-layout>
    <div class="wrap max-w-2xl py-8">
        <a href="{{ route('admin.index') }}" class="font-mono text-xs text-neon hover:underline">← panel admin</a>
        <h1 class="mt-2 font-display text-2xl text-cream">{{ $berita->exists ? 'Edit' : 'Tulis' }} Berita 📰</h1>

        <form method="POST" action="{{ $berita->exists ? route('admin.berita.update', $berita) : route('admin.berita.store') }}" enctype="multipart/form-data" class="card mt-6 space-y-4">
            @csrf
            @if ($berita->exists) @method('PUT') @endif
            <div>
                <label class="label" for="judul">Judul *</label>
                <input id="judul" name="judul" class="input @error('judul') input-error @enderror" value="{{ old('judul', $berita->judul) }}" required>
                @error('judul')<p class="error-text">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="ringkasan">Ringkasan</label>
                <input id="ringkasan" name="ringkasan" class="input" value="{{ old('ringkasan', $berita->ringkasan) }}">
            </div>
            <div>
                <label class="label" for="isi">Isi berita *</label>
                <textarea id="isi" name="isi" rows="8" class="input @error('isi') input-error @enderror" required>{{ old('isi', $berita->isi) }}</textarea>
                @error('isi')<p class="error-text">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="gambar">Gambar (opsional)</label>
                <input id="gambar" name="gambar" type="file" accept="image/*" class="block text-xs text-creamDim file:mr-3 file:rounded file:border-0 file:bg-navy-deep file:px-3 file:py-1.5 file:text-xs file:text-neon">
            </div>
            <label class="flex items-center gap-2 text-sm text-creamDim">
                <input type="checkbox" name="is_pinned" value="1" class="rounded border-line/30 bg-navy-deep text-neon focus:ring-neon" @checked(old('is_pinned', $berita->is_pinned))>
                📌 Sematkan (tampil paling atas)
            </label>
            <button class="btn-neon w-full">{{ $berita->exists ? 'Simpan Perubahan' : 'Terbitkan Berita' }}</button>
        </form>
    </div>
</x-app-layout>
