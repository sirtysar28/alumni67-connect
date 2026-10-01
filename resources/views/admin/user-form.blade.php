<x-app-layout>
    <div class="wrap max-w-3xl py-8">
        <a href="{{ route('admin.users.index') }}" class="font-mono text-xs text-neon hover:underline">← kelola akun alumni</a>
        <h1 class="mt-2 font-display text-2xl text-cream sm:text-3xl">Edit akun: {{ $user->name }} ✎</h1>
        <p class="mt-1 text-sm text-creamDim">
            Daftar {{ $user->created_at->translatedFormat('d M Y H:i') }} ·
            @if ($user->is_approved)<span class="text-neon">✓ aktif</span>@else<span class="text-yellow-300">⏳ belum disetujui</span>@endif
        </p>

        @if (session('error'))<div class="flash-err mt-4">{{ session('error') }}</div>@endif
        @if ($errors->any())
            <div class="flash-err mt-4">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="card mt-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="kicker">🪪 Info akun</div>
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
                    <p class="mt-1 text-[10px] text-creamDim/70">Email diubah → status verifikasi email direset.</p>
                </div>
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
                    <label class="label" for="password">Reset password (opsional)</label>
                    <input id="password" name="password" type="text" autocomplete="new-password" class="input @error('password') input-error @enderror" placeholder="kosongkan bila tidak diubah" value="{{ old('password') }}">
                    @error('password')<p class="error-text">{{ $message }}</p>@enderror
                    <p class="mt-1 text-[10px] text-creamDim/70">Min. 8 karakter. Informasikan ke user terkait.</p>
                </div>
            </div>

            <label class="flex items-center gap-3 border-t border-line/20 pt-4">
                <input type="checkbox" name="is_approved" value="1" class="h-4 w-4 accent-neon" @checked(old('is_approved', $user->is_approved))>
                <span class="text-sm text-cream">✓ Akun disetujui <span class="text-creamDim">(bisa login)</span></span>
            </label>

            <div class="kicker border-t border-line/20 pt-4">🔐 Role</div>
            <div class="flex flex-wrap gap-4">
                @foreach ($rolesList as $r)
                    <label class="flex items-center gap-2 text-sm text-cream">
                        <input type="checkbox" name="roles[]" value="{{ $r }}" class="h-4 w-4 accent-neon"
                               @checked(in_array($r, old('roles', $user->roles->pluck('name')->toArray())))>
                        {{ str_replace('_', ' ', $r) }}
                        @if ($r === 'super_admin')<span class="text-neon">★</span>@endif
                    </label>
                @endforeach
            </div>
            <p class="text-[10px] text-creamDim/70">Tanpa role terpilih → otomatis jadi <b>alumni</b>. Jangan mencabut role super_admin dari Super Admin terakhir.</p>

            <div class="kicker border-t border-line/20 pt-4">🎓 Data sekolah</div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="kelas">Kelas dulu</label>
                    <input id="kelas" name="kelas" class="input" placeholder="cth. IPA 1 / IPS 3" value="{{ old('kelas', $user->profile->kelas) }}">
                </div>
                <div>
                    <label class="label" for="tahun_lulus">Tahun lulus</label>
                    <input id="tahun_lulus" name="tahun_lulus" type="number" min="1990" max="2030" class="input" value="{{ old('tahun_lulus', $user->profile->tahun_lulus) }}">
                </div>
                <div>
                    <label class="label" for="nis">NIS</label>
                    <input id="nis" name="nis" class="input" value="{{ old('nis', $user->profile->nis) }}">
                </div>
                <div>
                    <label class="label" for="tgl_lahir">Tanggal lahir</label>
                    <input id="tgl_lahir" name="tgl_lahir" type="date" class="input" value="{{ old('tgl_lahir', $user->profile->tgl_lahir?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="kicker border-t border-line/20 pt-4">💼 Data profesional (untuk direktori)</div>
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
                <div>
                    <label class="label" for="no_wa">Nomor WhatsApp</label>
                    <input id="no_wa" name="no_wa" class="input" value="{{ old('no_wa', $user->profile->no_wa) }}">
                </div>
                <div>
                    <label class="label" for="instagram">Instagram (tanpa @)</label>
                    <input id="instagram" name="instagram" class="input" value="{{ old('instagram', $user->profile->instagram) }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="linkedin">URL LinkedIn</label>
                    <input id="linkedin" name="linkedin" class="input" placeholder="https://linkedin.com/in/…" value="{{ old('linkedin', $user->profile->linkedin) }}">
                </div>
            </div>

            <div>
                <label class="label" for="bio">Bio singkat</label>
                <textarea id="bio" name="bio" rows="3" class="input">{{ old('bio', $user->profile->bio) }}</textarea>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-line/20 pt-4">
                <a href="{{ route('admin.users.index') }}" class="btn-ghost !py-2 text-xs">Batal</a>
                <button type="submit" class="btn-neon !py-2 text-sm">💾 Simpan Perubahan</button>
            </div>
        </form>

        {{-- ==== HAPUS AKUN ==== --}}
        @if ($user->id !== auth()->id())
            <div class="card mt-6 border-red-400/30">
                <div class="kicker !mb-0 text-red-300">☠️ Zona berbahaya</div>
                <p class="mt-2 text-xs text-creamDim">
                    Menghapus akun <b class="text-cream">{{ $user->name }}</b> bersifat <b class="text-red-300">permanen</b> —
                    profil, postingan feed, komentar, tiket event, dan lowongan kerja miliknya ikut terhapus.
                </p>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-3"
                      onsubmit="return confirm('⚠️ YAKIN hapus permanen akun {{ $user->name }}?\\nTindakan ini TIDAK bisa dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger !py-2 text-xs">🗑 Hapus Akun Permanen</button>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>
