<x-app-layout>
    <div class="wrap py-8">
        <a href="{{ route('admin.index') }}" class="font-mono text-xs text-neon hover:underline">← panel admin</a>
        <div class="mt-2 flex flex-wrap items-center gap-3">
            <h1 class="font-display text-2xl text-cream">>_ Terminal Artisan</h1>
            <span class="chip-line">khusus Super Admin</span>
        </div>
        <p class="mt-1 text-sm text-creamDim">
            Jalankan perintah Laravel langsung dari browser — berguna di hosting tanpa SSH:
            <span class="font-mono text-neon">migrate</span>,
            <span class="font-mono text-neon">db:seed</span>,
            <span class="font-mono text-neon">cache:clear</span>, dst.
            Hanya perintah whitelist yang diizinkan (aman dari injeksi).
        </p>

        {{-- ==== OUTPUT / ERROR ==== --}}
        @php $output = session('terminal_output'); $error = session('terminal_error'); @endphp
        @if ($error)
            <pre class="mt-6 overflow-x-auto rounded-lg border border-red-400/40 bg-black/60 p-4 font-mono text-xs leading-relaxed whitespace-pre-wrap text-red-300">❌ {{ $error }}</pre>
        @endif
        @if ($output)
            <pre class="mt-6 overflow-x-auto rounded-lg border border-neonDim/40 bg-black/70 p-4 font-mono text-xs leading-relaxed whitespace-pre-wrap text-[#b8ffb8] shadow-lg shadow-black/40">{{ $output }}</pre>
        @endif

        {{-- ==== KONSEP LAYAR TERMINAL ==== --}}
        <div class="card mt-6">
            <div class="kicker !mb-0">>_ Konsol</div>

            <form method="POST" action="{{ route('admin.terminal.run') }}" class="mt-4"
                  onsubmit="const c=this.querySelector('[name=command]').value.trim(); if(['migrate:fresh','migrate:reset','migrate:refresh','migrate:rollback','queue:flush','down'].some(d=>c.startsWith(d)) && !confirm('⚠ Perintah ini BERSIFAT DESTRUKTIF (bisa menghapus/mengubah data). Yakin lanjut?')) return false;">
                @csrf
                <label class="label" for="command">Perintah artisan</label>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <div class="flex flex-1 items-center gap-2 rounded border border-line/30 bg-black/50 px-3 py-2 focus-within:border-neon">
                        <span class="font-mono text-xs text-neon">$ php artisan</span>
                        <input id="command" name="command" list="cmd-list" autocomplete="off" spellcheck="false"
                               placeholder="migrate --force"
                               class="w-full bg-transparent font-mono text-xs text-cream placeholder:text-creamDim/50 focus:outline-none">
                    </div>
                    <button type="submit" class="btn-neon shrink-0">Jalankan ⏎</button>
                </div>
                <datalist id="cmd-list">
                    @foreach ($commands as $c)
                        <option value="{{ $c }}"></option>
                    @endforeach
                </datalist>
            </form>

            {{-- ==== TOMBOL CEPAT (berkelompok) ==== --}}
            <div class="label mt-6">Perintah cepat</div>
            <div class="mt-2 grid gap-4 sm:grid-cols-2">
                @foreach ($groups as [$judul, $tombol])
                    <div class="rounded border border-line/20 bg-navy-deep/40 p-3">
                        <div class="font-mono text-[10px] uppercase tracking-wider text-creamDim">{{ $judul }}</div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($tombol as [$cmd, $label, $danger])
                                <form method="POST" action="{{ route('admin.terminal.run') }}" class="inline"
                                      @if ($danger) onclick="return confirm('⚠ migrate:fresh menghapus SEMUA tabel lalu migrasi ulang + seed. Data (user, berita, donasi, dll.) akan diganti data dummy. Lanjut?')" @endif>
                                    @csrf
                                    <input type="hidden" name="command" value="{{ $cmd }}">
                                    <button type="submit"
                                            class="rounded border px-3 py-1.5 font-mono text-xs transition
                                            @if ($danger)
                                                border-red-400/50 text-red-300 hover:bg-red-400/10
                                            @else
                                                border-neonDim/50 text-neon hover:bg-neon/10
                                            @endif">
                                        {{ $label }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ==== PERINGKATAN & WHITELIST ==== --}}
        <div class="card mt-4">
            <div class="kicker !mb-0">🛡 Keamanan</div>
            <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-creamDim">
                <li>Halaman ini hanya bisa diakses role <b class="text-cream">super_admin</b>.</li>
                <li>Perintah dijalankan lewat <span class="font-mono text-neon">Artisan::call()</span> (bukan shell) dan hanya nama perintah di whitelist.</li>
                <li>Perintah destruktif (fresh/reset/rollback) selalu minta konfirmasi.</li>
                <li>Setelah upload file migration baru di kemudian hari, cukup klik <span class="font-mono text-neon">migrate</span> di sini.</li>
            </ul>
            <details class="mt-3">
                <summary class="cursor-pointer font-mono text-xs text-neon">lihat daftar whitelist ({{ count($commands) }} perintah)</summary>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach ($commands as $c)
                        <span class="chip-line !py-0.5">{{ $c }}</span>
                    @endforeach
                </div>
            </details>
        </div>
    </div>
</x-app-layout>
