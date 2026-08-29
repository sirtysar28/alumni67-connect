<x-app-layout>
    <div class="wrap max-w-2xl py-8">
        <a href="{{ route('jobs.index') }}" class="font-mono text-xs text-neon hover:underline">← kembali</a>
        <h1 class="mt-2 font-display text-2xl text-cream">Pasang Lowongan 💼</h1>
        <p class="mt-1 text-sm text-creamDim">Sekali pasang, kebaca seluruh alumni. #HiringAlumni</p>

        <form method="POST" action="{{ route('jobs.store') }}" class="card mt-6 space-y-4">
            @csrf
            <div>
                <label class="label" for="judul">Judul posisi *</label>
                <input id="judul" name="judul" class="input @error('judul') input-error @enderror" value="{{ old('judul') }}" required>
                @error('judul')<p class="error-text">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="perusahaan">Perusahaan</label>
                    <input id="perusahaan" name="perusahaan" class="input" value="{{ old('perusahaan') }}">
                </div>
                <div>
                    <label class="label" for="lokasi">Lokasi</label>
                    <input id="lokasi" name="lokasi" class="input" placeholder="Jakarta / Remote" value="{{ old('lokasi') }}">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="tipe">Tipe *</label>
                    <select id="tipe" name="tipe" class="input" required>
                        @foreach (\App\Http\Controllers\JobController::TIPE as $t)
                            <option value="{{ $t }}" @selected(old('tipe') === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="kategori">Kategori *</label>
                    <select id="kategori" name="kategori" class="input" required>
                        @foreach (\App\Http\Controllers\JobController::KATEGORI as $k)
                            <option value="{{ $k }}" @selected(old('kategori') === $k)>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="gaji_min">Gaji min (Rp)</label>
                    <input id="gaji_min" name="gaji_min" type="number" min="0" class="input" value="{{ old('gaji_min') }}">
                </div>
                <div>
                    <label class="label" for="gaji_max">Gaji maks (Rp)</label>
                    <input id="gaji_max" name="gaji_max" type="number" min="0" class="input" value="{{ old('gaji_max') }}">
                </div>
            </div>
            <div>
                <label class="label" for="deskripsi">Deskripsi pekerjaan *</label>
                <textarea id="deskripsi" name="deskripsi" rows="6" class="input @error('deskripsi') input-error @enderror" required>{{ old('deskripsi') }}</textarea>
                @error('deskripsi')<p class="error-text">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="cara_melamar">Cara melamar</label>
                <textarea id="cara_melamar" name="cara_melamar" rows="3" class="input">{{ old('cara_melamar') }}</textarea>
            </div>
            <button class="btn-neon w-full">Terbitkan Lowongan</button>
        </form>
    </div>
</x-app-layout>
