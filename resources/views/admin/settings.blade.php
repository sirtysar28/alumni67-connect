<x-app-layout>
    <div class="wrap max-w-2xl py-8">
        <a href="{{ route('admin.index') }}" class="font-mono text-xs text-neon hover:underline">← panel admin</a>
        <h1 class="mt-2 font-display text-2xl text-cream">Pengaturan Situs</h1>
        <p class="mt-1 text-sm text-creamDim">
            Ubah tampilan situs (logo & tema) dan konfigurasi SMTP untuk notifikasi email
            (verifikasi akun, info event, dsb). Nilai tersimpan di database — tanpa perlu edit file.
        </p>

        @if (session('success'))<div class="flash-ok mt-4">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="flash-err mt-4">{{ session('error') }}</div>@endif

        {{-- ==== TAMPILAN SITUS: LOGO & TEMA (khusus Super Admin) ==== --}}
        @role('super_admin')
        <form method="POST" action="{{ route('admin.settings.appearance') }}" class="card mt-6 space-y-5">
            @csrf
            <div class="kicker">Tampilan situs 🎨 <span class="chip-line ml-2 !py-0.5">khusus Super Admin</span></div>
            <p class="text-sm text-creamDim">
                Logo dipakai di <b class="text-cream">satu tempat pengaturan</b> untuk semua halaman:
                header/navbar, form login, dan form register — dengan <b class="text-cream">2 varian</b>:
                logo khusus mode gelap (🌙) & logo khusus mode terang (☀️).
            </p>

            {{-- Logo DARK MODE + preview di bg gelap --}}
            <div>
                <label class="label" for="site_logo_dark">🌙 Link logo — mode gelap (dark)</label>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center" x-data="{ url: @js(old('site_logo_dark', $site_logo_dark ?? '')) }">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded border border-line/30 bg-[#150A34]">
                        <template x-if="url">
                            <img :src="url" alt="preview dark" class="h-12 w-12 rounded object-contain"
                                 x-on:error="$el.style.display='none'" x-on:load="$el.style.display='block'">
                        </template>
                        <div x-show="!url" class="font-display text-lg text-neon">67</div>
                    </div>
                    <input id="site_logo_dark" name="site_logo_dark" type="url" value="{{ old('site_logo_dark', $site_logo_dark) }}"
                           class="input @error('site_logo_dark') input-error @enderror"
                           placeholder="https://domainmu.com/logo-dark.png"
                           @input="url = $event.target.value">
                </div>
                @error('site_logo_dark')<p class="error-text">{{ $message }}</p>@enderror
                <p class="mt-1.5 text-[10px] text-creamDim/70">
                    Tampil saat situs ber tema gelap (default). Kosongkan untuk kembali ke logo bawaan.
                </p>
            </div>

            {{-- Logo LIGHT MODE + preview di bg terang --}}
            <div>
                <label class="label" for="site_logo_light">☀️ Link logo — mode terang (light)</label>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center" x-data="{ url: @js(old('site_logo_light', $site_logo_light ?? '')) }">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded border border-line/30 bg-[#F6F3EA]">
                        <template x-if="url">
                            <img :src="url" alt="preview light" class="h-12 w-12 rounded object-contain"
                                 x-on:error="$el.style.display='none'" x-on:load="$el.style.display='block'">
                        </template>
                        <div x-show="!url" class="font-display text-lg text-neon/60">67</div>
                    </div>
                    <input id="site_logo_light" name="site_logo_light" type="url" value="{{ old('site_logo_light', $site_logo_light) }}"
                           class="input @error('site_logo_light') input-error @enderror"
                           placeholder="https://domainmu.com/logo-light.png"
                           @input="url = $event.target.value">
                </div>
                @error('site_logo_light')<p class="error-text">{{ $message }}</p>@enderror
                <p class="mt-1.5 text-[10px] text-creamDim/70">
                    Tampil saat situs ber tema terang. Kalau dikosongkan, mode terang ikut memakai logo dark di atas.
                    Tip: gunakan versi logo berwarna gelap agar terbaca di latar terang.
                </p>
            </div>

            {{-- Tema dark/light --}}
            <div>
                <label class="label">Tema tampilan situs</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="theme_mode" value="dark" class="peer sr-only" @checked(old('theme_mode', $theme_mode) === 'dark')>
                        <div class="rounded border border-line/30 bg-navy-deep/60 p-3 transition peer-checked:border-neon peer-checked:ring-1 peer-checked:ring-neon">
                            <div class="mx-auto mb-2 h-14 w-full max-w-[120px] rounded bg-[#150A34] p-2 ring-1 ring-white/10">
                                <div class="h-2 w-3/5 rounded bg-[#01F501]"></div>
                                <div class="mt-1.5 h-1.5 w-4/5 rounded bg-white/25"></div>
                                <div class="mt-1 h-1.5 w-2/5 rounded bg-white/15"></div>
                            </div>
                            <div class="text-center text-sm font-semibold text-cream">🌙 Gelap</div>
                            <div class="text-center text-[10px] text-creamDim">default</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="theme_mode" value="light" class="peer sr-only" @checked(old('theme_mode', $theme_mode) === 'light')>
                        <div class="rounded border border-line/30 bg-navy-deep/60 p-3 transition peer-checked:border-neon peer-checked:ring-1 peer-checked:ring-neon">
                            <div class="mx-auto mb-2 h-14 w-full max-w-[120px] rounded bg-[#F6F3EA] p-2 ring-1 ring-black/10">
                                <div class="h-2 w-3/5 rounded bg-[#018A01]"></div>
                                <div class="mt-1.5 h-1.5 w-4/5 rounded bg-black/25"></div>
                                <div class="mt-1 h-1.5 w-2/5 rounded bg-black/15"></div>
                            </div>
                            <div class="text-center text-sm font-semibold text-cream">☀️ Terang</div>
                            <div class="text-center text-[10px] text-creamDim">kontras siang hari</div>
                        </div>
                    </label>
                </div>
                @error('theme_mode')<p class="error-text">{{ $message }}</p>@enderror
                <p class="mt-1.5 text-[10px] text-creamDim/70">
                    Tema ini jadi default seluruh situs. Pengunjung tetap bisa ganti sendiri
                    lewat tombol 🌙/☀️ di pojok atas — pilihannya tersimpan di browser masing-masing.
                </p>
            </div>

            <button class="btn-neon w-full">Simpan Tampilan Situs</button>
        </form>
        @endrole

        {{-- ==== FORM SMTP ==== --}}
        <form method="POST" action="{{ route('admin.settings.update') }}" class="card mt-6 space-y-5">
            @csrf
            <div class="kicker">Server SMTP</div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="mail_mailer">Driver pengiriman *</label>
                    <select id="mail_mailer" name="mail_mailer" class="input" required>
                        <option value="smtp" @selected($mail_mailer === 'smtp')>SMTP (rekomendasi)</option>
                        <option value="log" @selected($mail_mailer === 'log')>Log (dev — email ditulis ke log)</option>
                        <option value="sendmail" @selected($mail_mailer === 'sendmail')>Sendmail</option>
                        <option value="mailgun" @selected($mail_mailer === 'mailgun')>Mailgun</option>
                        <option value="ses" @selected($mail_mailer === 'ses')>Amazon SES</option>
                        <option value="postmark" @selected($mail_mailer === 'postmark')>Postmark</option>
                    </select>
                </div>
                <div>
                    <label class="label" for="mail_encryption">Enkripsi</label>
                    <select id="mail_encryption" name="mail_encryption" class="input">
                        <option value="tls" @selected($mail_encryption === 'tls')>TLS (port 587)</option>
                        <option value="ssl" @selected($mail_encryption === 'ssl')>SSL (port 465)</option>
                        <option value="none" @selected($mail_encryption === 'none' || $mail_encryption === null)>Tanpa enkripsi (25)</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="mail_host">SMTP Host</label>
                    <input id="mail_host" name="mail_host" class="input @error('mail_host') input-error @enderror"
                           placeholder="smtp.gmail.com / mail.domainmu.com" value="{{ old('mail_host', $mail_host) }}">
                    @error('mail_host')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="mail_port">Port</label>
                    <input id="mail_port" name="mail_port" type="number" min="1" max="65535" class="input @error('mail_port') input-error @enderror"
                           placeholder="587" value="{{ old('mail_port', $mail_port) }}">
                    @error('mail_port')<p class="error-text">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="mail_username">Username SMTP</label>
                    <input id="mail_username" name="mail_username" class="input @error('mail_username') input-error @enderror"
                           placeholder="email@domainmu.com" value="{{ old('mail_username', $mail_username) }}">
                    @error('mail_username')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="mail_password">Password SMTP</label>
                    <input id="mail_password" name="mail_password" type="password" autocomplete="new-password"
                           class="input @error('mail_password') input-error @enderror"
                           placeholder="{{ $mail_has_password ? '•••••••• (tersimpan — kosongkan bila tidak diubah)' : 'app password / password email' }}">
                    @error('mail_password')<p class="error-text">{{ $message }}</p>@enderror
                    <p class="mt-1 text-[10px] text-creamDim/70">🔐 Disimpan terenkripsi di database.</p>
                </div>
            </div>

            <div class="kicker border-t border-line/20 pt-4">Pengirim email</div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="mail_from_address">Alamat pengirim (From)</label>
                    <input id="mail_from_address" name="mail_from_address" type="email" class="input @error('mail_from_address') input-error @enderror"
                           placeholder="no-reply@alumnismun67halim2003.id" value="{{ old('mail_from_address', $mail_from_address) }}">
                    @error('mail_from_address')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="mail_from_name">Nama pengirim</label>
                    <input id="mail_from_name" name="mail_from_name" class="input @error('mail_from_name') input-error @enderror"
                           placeholder="Alumni67 Connect" value="{{ old('mail_from_name', $mail_from_name) }}">
                    @error('mail_from_name')<p class="error-text">{{ $message }}</p>@enderror
                </div>
            </div>

            <button class="btn-neon w-full">Simpan Pengaturan SMTP</button>
        </form>

        {{-- ==== TES KIRIM EMAIL ==== --}}
        <form method="POST" action="{{ route('admin.settings.testmail') }}" class="card mt-6 space-y-4">
            @csrf
            <div class="kicker">Tes koneksi</div>
            <p class="text-sm text-creamDim">Pastikan pengaturan sudah disimpan, lalu kirim email percobaan untuk memastikan SMTP berfungsi.</p>
            <div class="flex flex-col gap-3 sm:flex-row">
                <input name="test_email" type="email" class="input @error('test_email') input-error @enderror"
                       placeholder="email@tujuan.com" value="{{ old('test_email', auth()->user()->email) }}" required>
                <button class="btn-neon shrink-0">Kirim Email Tes 📤</button>
            </div>
            @error('test_email')<p class="error-text">{{ $message }}</p>@enderror
        </form>

        {{-- Tips --}}
        <div class="card-plain mt-5 text-xs leading-relaxed text-creamDim">
            <b class="text-cream">💡 Contoh konfigurasi Gmail:</b><br>
            Host <span class="font-mono text-neon">smtp.gmail.com</span> · Port <span class="font-mono text-neon">587</span> ·
            Enkripsi <span class="font-mono text-neon">TLS</span> · Username email Gmail + <b>App Password</b>
            (buat di myaccount.google.com → Security → 2-Step Verification → App passwords).<br>
            Untuk email hosting sendiri (cPanel): <span class="font-mono text-neon">mail.domainmu.com</span>, port 465/SSL.
        </div>
    </div>
</x-app-layout>
