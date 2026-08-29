<x-app-layout>
    <div class="wrap max-w-3xl py-8">
        <div class="kicker">Profil saya</div>
        <h1 class="font-display text-2xl text-cream sm:text-3xl">Kelola Profil Alumni 👤</h1>
        <p class="mt-1 text-sm text-creamDim">Lengkapi data supaya teman seangkatan mudah menemukanmu di direktori.</p>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <span class="chip-line">Status: {{ match(auth()->user()->profile?->verification_status) {
                'approved' => '✓ Terverifikasi',
                'pending' => '⏳ Menunggu verifikasi',
                'rejected' => '✗ Ditolak',
                default => 'Belum diajukan',
            } }}</span>
            @if (auth()->user()->profile?->verification_status === 'rejected' && auth()->user()->profile->catatan_verifikasi)
                <span class="text-xs text-red-300">Catatan admin: {{ auth()->user()->profile->catatan_verifikasi }}</span>
            @endif
        </div>

        <div class="mt-6 space-y-6">
            @if (session('status') === 'profil-updated')
                <div class="flash-ok">Profil berhasil diperbarui ✓</div>
            @elseif (session('status') === 'verifikasi-diajukan')
                <div class="flash-ok">Pengajuan verifikasi terkirim! Menunggu approval pengurus ⏳</div>
            @endif

            {{-- ==== INFO AKUN & PROFESIONAL ==== --}}
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="card space-y-5">
                @csrf
                @method('PATCH')

                <div class="kicker">Info akun</div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="name">Nama lengkap *</label>
                        <input id="name" name="name" class="input @error('name') input-error @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="email">Email *</label>
                        <input id="email" name="email" type="email" class="input @error('email') input-error @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="angkatan_id">Angkatan</label>
                        <select id="angkatan_id" name="angkatan_id" class="input">
                            <option value="">— pilih —</option>
                            @foreach ($angkatanList as $a)
                                <option value="{{ $a->id }}" @selected(old('angkatan_id', $user->angkatan_id) == $a->id)>{{ $a->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="kelas">Kelas dulu</label>
                        <input id="kelas" name="kelas" class="input" placeholder="cth. IPA 1 / IPS 3" value="{{ old('kelas', $user->profile->kelas) }}">
                    </div>
                </div>

                <div class="kicker border-t border-line/20 pt-4">Data profesional (untuk direktori)</div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="pekerjaan">Pekerjaan / jabatan</label>
                        <input id="pekerjaan" name="pekerjaan" class="input" value="{{ old('pekerjaan', $user->profile->pekerjaan) }}">
                    </div>
                    <div>
                        <label class="label" for="perusahaan">Perusahaan / instansi</label>
                        <input id="perusahaan" name="perusahaan" class="input" value="{{ old('perusahaan', $user->profile->perusahaan) }}">
                    </div>
                    <div>
                        <label class="label" for="bidang">Bidang</label>
                        <select id="bidang" name="bidang" class="input">
                            <option value="">— pilih —</option>
                            @foreach ($bidangList as $b)
                                <option value="{{ $b }}" @selected(old('bidang', $user->profile->bidang) === $b)>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="kota">Kota domisili</label>
                        <input id="kota" name="kota" class="input" value="{{ old('kota', $user->profile->kota) }}">
                    </div>
                    <div>
                        <label class="label" for="kampus">Kampus / almamater</label>
                        <input id="kampus" name="kampus" class="input" value="{{ old('kampus', $user->profile->kampus) }}">
                    </div>
                    <div>
                        <label class="label" for="skill">Skill (pisah koma)</label>
                        <input id="skill" name="skill" class="input" placeholder="IT, Public Speaking, Figma" value="{{ old('skill', $user->profile->skill) }}">
                    </div>
                </div>

                <div class="kicker border-t border-line/20 pt-4">Kontak & sosial</div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="no_wa">Nomor WhatsApp</label>
                        <input id="no_wa" name="no_wa" class="input" placeholder="08xx-xxxx-xxxx" value="{{ old('no_wa', $user->profile->no_wa) }}">
                        <p class="mt-1 text-[10px] text-creamDim/70">Hanya terlihat oleh alumni yang login.</p>
                    </div>
                    <div>
                        <label class="label" for="tgl_lahir">Tanggal lahir</label>
                        <input id="tgl_lahir" name="tgl_lahir" type="date" class="input" value="{{ old('tgl_lahir', $user->profile->tgl_lahir?->format('Y-m-d')) }}">
                    </div>
                    <div>
                        <label class="label" for="instagram">Instagram (tanpa @)</label>
                        <input id="instagram" name="instagram" class="input" value="{{ old('instagram', $user->profile->instagram) }}">
                    </div>
                    <div>
                        <label class="label" for="linkedin">URL LinkedIn</label>
                        <input id="linkedin" name="linkedin" class="input" placeholder="https://linkedin.com/in/…" value="{{ old('linkedin', $user->profile->linkedin) }}">
                    </div>
                </div>

                <div>
                    <label class="label" for="bio">Bio singkat</label>
                    <textarea id="bio" name="bio" rows="3" class="input">{{ old('bio', $user->profile->bio) }}</textarea>
                </div>

                <div class="kicker border-t border-line/20 pt-4">Usaha / bisnis — #BisnisAlumni67</div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="usaha_nama">Nama usaha</label>
                        <input id="usaha_nama" name="usaha_nama" class="input" value="{{ old('usaha_nama', $user->profile->usaha_nama) }}">
                    </div>
                    <div>
                        <label class="label" for="usaha_deskripsi">Deskripsi singkat</label>
                        <input id="usaha_deskripsi" name="usaha_deskripsi" class="input" value="{{ old('usaha_deskripsi', $user->profile->usaha_deskripsi) }}">
                    </div>
                </div>

                <div class="kicker border-t border-line/20 pt-4">Foto profil</div>
                <div class="flex flex-wrap items-center gap-4">
                    @if ($user->profile->foto)
                        <img src="{{ Storage::url($user->profile->foto) }}" class="h-16 w-16 rounded-full object-cover" alt="">
                    @else
                        <div class="avatar h-16 w-16">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    @endif
                    <input type="file" name="foto" accept="image/*" class="block text-xs text-creamDim file:mr-3 file:rounded file:border-0 file:bg-navy-deep file:px-3 file:py-1.5 file:text-xs file:text-neon">
                </div>

                <button class="btn-neon w-full sm:w-auto">Simpan Profil</button>
            </form>

            {{-- ==== VERIFIED BADGE ==== --}}
            @if (! $user->profile->isVerified())
                <form method="POST" action="{{ route('profile.verify') }}" enctype="multipart/form-data" class="card space-y-4">
                    @csrf
                    <div class="kicker">Verified badge alumni ✓</div>
                    <p class="text-sm text-creamDim">
                        Upload ijazah/NIS untuk verifikasi keangkatan — disetujui admin, profilmu dapat badge
                        <span class="badge-verified">✓ Verified</span> dan lebih dipercaya di direktori & bursa kerja.
                    </p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label" for="nis">NIS (nomor induk siswa) *</label>
                            <input id="nis" name="nis" class="input @error('nis') input-error @enderror" value="{{ old('nis', $user->profile->nis) }}" required>
                            @error('nis')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="ijazah">Foto ijazah / SKL (JPG/PNG/PDF) *</label>
                            <input id="ijazah" name="ijazah" type="file" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-creamDim file:mr-3 file:rounded file:border-0 file:bg-navy-deep file:px-3 file:py-1.5 file:text-xs file:text-neon @error('ijazah') input-error @enderror" required>
                            @error('ijazah')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <button class="btn-outline">Ajukan Verifikasi</button>
                </form>
            @endif

            {{-- ==== PASSWORD & HAPUS AKUN (Breeze) ==== --}}
            <form method="POST" action="{{ route('password.update') }}" class="card space-y-4">
                @csrf
                <div class="kicker">Ganti password</div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="label" for="current_password">Password sekarang</label>
                        <input id="current_password" name="current_password" type="password" class="input @error('updatePassword.current_password') input-error @enderror" required>
                        @error('updatePassword.current_password')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="password">Password baru</label>
                        <input id="password" name="password" type="password" class="input @error('updatePassword.password') input-error @enderror" required>
                        @error('updatePassword.password')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="password_confirmation">Ulangi password baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="input" required>
                    </div>
                </div>
                <button class="btn-outline">Update Password</button>
            </form>
        </div>
    </div>
</x-app-layout>
