<x-app-layout>
    <div class="wrap py-8">
        <a href="{{ route('admin.index') }}" class="font-mono text-xs text-neon hover:underline">← panel admin</a>
        <h1 class="mt-2 font-display text-2xl text-cream sm:text-3xl">Kelola akun alumni 🛂</h1>
        <p class="mt-1 text-sm text-creamDim">
            Semua akun teregistrasi — <b class="text-cream">edit</b> data/role & <b class="text-red-300">hapus permanen</b> akun.
            <span class="chip-line ml-1 !py-0.5 !text-[10px]">khusus Super Admin</span>
        </p>

        @if (session('success'))<div class="flash-ok mt-4">{!! session('success') !!}</div>@endif
        @if (session('error'))<div class="flash-err mt-4">{{ session('error') }}</div>@endif

        {{-- ==== PENCARIAN & FILTER ==== --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="card mt-6">
            <div class="kicker !mb-0">🔎 Cari & filter ({{ $users->total() }} akun)</div>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input type="text" name="q" value="{{ $q }}" class="input" placeholder="Nama, email, kelas, pekerjaan, kota…">
                <select name="status" class="input" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    <option value="approved" @selected($status === 'approved')>✓ Aktif / disetujui</option>
                    <option value="pending" @selected($status === 'pending')>⏳ Belum disetujui</option>
                </select>
                <select name="role" class="input" onchange="this.form.submit()">
                    <option value="">Semua role</option>
                    @foreach (['super_admin' => 'Super Admin', 'pengurus' => 'Pengurus', 'ketua_angkatan' => 'Ketua Angkatan', 'alumni' => 'Alumni'] as $r => $label)
                        <option value="{{ $r }}" @selected($role === $r)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="btn-neon flex-1 !py-2 text-xs">Cari</button>
                    <a href="{{ route('admin.users.index') }}" class="btn-outline !py-2 text-xs">Reset</a>
                </div>
            </div>
        </form>

        {{-- ==== DAFTAR AKUN ==== --}}
        <div class="card mt-4">
            @if ($users->isEmpty())
                <p class="text-sm text-creamDim">Tidak ada akun yang cocok dengan pencarian/filter.</p>
            @else
                <div class="hidden grid-cols-12 gap-2 border-b border-line/10 pb-2 font-mono text-[10px] uppercase text-creamDim lg:grid">
                    <div class="col-span-5">Alumni</div>
                    <div class="col-span-2">Angkatan / kelas</div>
                    <div class="col-span-2">Role</div>
                    <div class="col-span-1">Status</div>
                    <div class="col-span-2 text-right">Aksi</div>
                </div>

                @foreach ($users as $u)
                    <div class="grid grid-cols-1 items-center gap-3 border-t border-line/10 py-4 lg:grid-cols-12">
                        {{-- Info alumni --}}
                        <div class="flex min-w-0 items-center gap-3 lg:col-span-5">
                            <div class="avatar h-10 w-10 shrink-0">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                            <div class="min-w-0">
                                <div class="truncate font-semibold text-cream">
                                    {{ $u->name }}
                                    @if ($u->id === auth()->id())<span class="chip-line ml-1 !py-0.5 !text-[9px]">kamu</span>@endif
                                    @if ($u->profile?->verification_status === 'approved')<span class="text-neon" title="badge terverifikasi">✓</span>@endif
                                </div>
                                <div class="truncate font-mono text-[11px] text-creamDim">{{ $u->email }}</div>
                                <div class="mt-0.5 font-mono text-[10px] text-creamDim/80">
                                    daftar {{ $u->created_at->translatedFormat('d M Y') }}
                                    @if ($u->profile?->kota)· {{ $u->profile->kota }}@endif
                                    @if ($u->profile?->pekerjaan)· {{ $u->profile->pekerjaan }}@endif
                                </div>
                            </div>
                        </div>

                        {{-- Angkatan / kelas --}}
                        <div class="font-mono text-[11px] text-creamDim lg:col-span-2">
                            {{ $u->angkatan?->tahun ? 'Angkatan '.$u->angkatan->tahun : '—' }}<br>
                            <span class="text-creamDim/70">{{ $u->profile?->kelas ?? 'kelas belum diisi' }}</span>
                        </div>

                        {{-- Role --}}
                        <div class="flex flex-wrap gap-1 lg:col-span-2">
                            @foreach ($u->roles->pluck('name') as $r)
                                <span class="chip-line !py-0.5 !text-[10px] {{ $r === 'super_admin' ? '!text-neon' : '' }}">{{ $r }}</span>
                            @endforeach
                        </div>

                        {{-- Status --}}
                        <div class="lg:col-span-1">
                            @if ($u->is_approved)
                                <span class="text-[11px] text-neon">✓ aktif</span>
                            @else
                                <span class="text-[11px] text-yellow-300">⏳ pending</span>
                            @endif
                        </div>

                        {{-- Aksi --}}
                        <div class="flex items-center gap-2 lg:col-span-2 lg:justify-end">
                            <a href="{{ route('admin.users.edit', $u) }}" class="btn-outline !px-3 !py-1.5 text-xs">✎ Edit</a>
                            @if ($u->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                      onsubmit="return confirm('⚠️ HAPUS PERMANEN akun {{ $u->name }}?\\n\\nSemua data terkait (profil, postingan, tiket event, lowongan) ikut terhapus. Tindakan ini TIDAK bisa dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger !px-3 !py-1.5 text-xs">🗑 Hapus</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="mt-6">{{ $users->links() }}</div>
    </div>
</x-app-layout>
