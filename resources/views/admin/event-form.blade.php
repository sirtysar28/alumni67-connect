<x-app-layout>
    <div class="wrap max-w-2xl py-8">
        <a href="{{ route('admin.index') }}" class="font-mono text-xs text-neon hover:underline">← panel admin</a>
        <h1 class="mt-2 font-display text-2xl text-cream">{{ $event->exists ? 'Edit' : 'Buat' }} Event 🎉</h1>

        <form method="POST" action="{{ $event->exists ? route('admin.event.update', $event) : route('admin.event.store') }}" enctype="multipart/form-data" class="card mt-6 space-y-4">
            @csrf
            @if ($event->exists) @method('PUT') @endif
            <div>
                <label class="label" for="judul">Judul *</label>
                <input id="judul" name="judul" class="input @error('judul') input-error @enderror" value="{{ old('judul', $event->judul) }}" required>
                @error('judul')<p class="error-text">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="mulai">Mulai *</label>
                    <input id="mulai" name="mulai" type="datetime-local" class="input @error('mulai') input-error @enderror" value="{{ old('mulai', $event->mulai?->format('Y-m-d\TH:i')) }}" required>
                    @error('mulai')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="selesai">Selesai</label>
                    <input id="selesai" name="selesai" type="datetime-local" class="input" value="{{ old('selesai', $event->selesai?->format('Y-m-d\TH:i')) }}">
                </div>
            </div>
            <div>
                <label class="label" for="lokasi">Lokasi *</label>
                <input id="lokasi" name="lokasi" class="input" value="{{ old('lokasi', $event->lokasi) }}" required>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="label" for="kapasitas">Kapasitas</label>
                    <input id="kapasitas" name="kapasitas" type="number" min="1" class="input" value="{{ old('kapasitas', $event->kapasitas) }}">
                </div>
                <div>
                    <label class="label" for="harga_tiket">Harga tiket (Rp)</label>
                    <input id="harga_tiket" name="harga_tiket" type="number" min="0" class="input" value="{{ old('harga_tiket', $event->harga_tiket ?? 0) }}">
                </div>
                <div>
                    <label class="label" for="status">Status</label>
                    <select id="status" name="status" class="input">
                        @foreach (['draft', 'publish', 'selesai'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $event->status ?: 'publish') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="label" for="deskripsi">Deskripsi *</label>
                <textarea id="deskripsi" name="deskripsi" rows="6" class="input @error('deskripsi') input-error @enderror" required>{{ old('deskripsi', $event->deskripsi) }}</textarea>
                @error('deskripsi')<p class="error-text">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="poster">Poster (opsional)</label>
                <input id="poster" name="poster" type="file" accept="image/*" class="block text-xs text-creamDim file:mr-3 file:rounded file:border-0 file:bg-navy-deep file:px-3 file:py-1.5 file:text-xs file:text-neon">
            </div>
            <button class="btn-neon w-full">{{ $event->exists ? 'Simpan Perubahan' : 'Buat Event' }}</button>
        </form>
    </div>
</x-app-layout>
