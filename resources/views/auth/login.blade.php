<x-guest-layout>
    <div class="flex min-h-screen flex-col lg:flex-row">

        {{-- ============ PANEL BRANDING (kiri — tampil di layar besar) ============ --}}
        <aside class="hero-scan relative hidden w-[44%] flex-col justify-between overflow-hidden border-e border-neonDim/30 bg-navy/40 p-10 lg:flex xl:p-14">
            {{-- Ornamen glow neon --}}
            <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-neon/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -right-16 h-80 w-80 rounded-full bg-maroon/30 blur-3xl"></div>

            <div class="relative flex items-center justify-between">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                    <x-site-logo size="h-10 w-10" />
                    <span class="font-display text-xl text-cream">Alumni<span class="text-neon">67</span> Connect</span>
                </a>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-creamDim transition hover:text-cream">
                    <svg class="h-5 w-5 text-neon" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span class="hidden font-mono text-xs uppercase tracking-widest xl:inline">Kembali ke beranda</span>
                </a>
            </div>

            <div class="relative">
                <span class="chip-neon">● Komunitas Alumni SMUN 67 Halim</span>
                <h1 class="mt-6 font-display text-5xl leading-[1.05] text-cream xl:text-6xl">
                    Selamat<br>datang <span class="text-neon">kembali!</span>
                </h1>
                <p class="mt-5 max-w-sm text-sm leading-relaxed text-creamDim">
                    Masuk untuk kejar-kejaran kabar, info reuni, bursa kerja, dan segala
                    update seru dari teman-teman seangkatanmu. 🎉
                </p>
                <p class="mt-8 font-hand text-2xl text-neon/90">
                    "Sekali teman 67, selamanya teman 67."
                </p>
            </div>

            {{-- Marquee strip --}}
            <div class="relative -mx-10 -mb-10 overflow-hidden border-t border-neonDim/30 bg-navy-deep/70 py-3 xl:-mx-14 xl:-mb-14">
                <div class="strip-track inline-block whitespace-nowrap font-mono text-xs font-bold tracking-wide text-neon">
                    @foreach (range(1, 2) as $i)
                        <span class="mx-5">DIREKTORI ALUMNI</span> ●
                        <span class="mx-5">BURSA KERJA #HiringAlumni</span> ●
                        <span class="mx-5">EVENT & REUNI</span> ●
                        <span class="mx-5">FORUM ASPIRASI</span> ●
                    @endforeach
                </div>
            </div>
        </aside>

        {{-- ============ PANEL FORM (kanan) ============ --}}
        <main class="flex flex-1 items-center justify-center px-4 py-10 sm:px-6">
            <div class="w-full max-w-md">

                {{-- Logo mobile (tampil di bawah layar besar) — dari setting Super Admin --}}
                <div class="mb-8 text-center lg:hidden">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                        <x-site-logo size="h-11 w-11" />
                        <span class="font-display text-2xl text-cream">Alumni<span class="text-neon">67</span></span>
                    </a>
                    <p class="mt-2 font-mono text-[11px] uppercase tracking-[0.2em] text-creamDim">
                        Komunitas Alumni SMUN 67 Halim
                    </p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                {{-- Notifikasi: guest dialihkan dari halaman yang butuh login --}}
                @php
                    $intended = session('url.intended');
                    $pesan = match (true) {
                        str_contains($intended, '/direktori/') => 'Silakan login dulu untuk melihat detail profil alumni. 🔒',
                        str_contains($intended, '/forum/')     => 'Silakan login dulu untuk membaca isi diskusi forum. 🔒',
                        default                                 => 'Silakan login dulu untuk melanjutkan. 🔒',
                    };
                @endphp
                @if ($intended)
                    <div class="flash-ok mb-4">{{ $pesan }}</div>
                @endif

                <div class="card p-6 sm:p-8">
                    <div class="mb-6">
                        <div class="kicker">Masuk dulu ya</div>
                        <h2 class="h-display">Login ke akunmu</h2>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <!-- Email -->
                        <div>
                            <label for="email" class="label">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   class="input {{ $errors->has('email') || $errors->has('credentials') ? 'input-error' : '' }}"
                                   required autofocus autocomplete="username"
                                   placeholder="nama@email.com" />
                            @error('email')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <!-- Password (dengan tombol intip) -->
                        <div>
                            <label for="password" class="label">Password</label>
                            <x-password-input id="password" name="password"
                                               autocomplete="current-password" required
                                               class="{{ $errors->has('password') ? 'input-error' : '' }}"
                                               placeholder="Password kamu" />
                            @error('password')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <!-- Captcha -->
                        <div>
                            <label for="captcha" class="label">Buktikan kamu bukan robot 👀</label>
                            <div class="flex items-stretch gap-3">
                                <div id="captcha-box"
                                     class="flex min-w-[7.5rem] -rotate-2 select-none items-center justify-center rounded border border-neonDim/50 bg-navy-deep px-4 py-2 font-hand text-3xl tracking-wider text-neon shadow-inner shadow-black/40">
                                    {{ $captchaA }} + {{ $captchaB }} = ?
                                </div>
                                <button type="button" id="captcha-reload"
                                        class="flex items-center justify-center rounded border border-line/30 px-3 text-creamDim transition hover:border-neon hover:text-neon"
                                        title="Ganti soal captcha">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                </button>
                                <input id="captcha" type="text" name="captcha" inputmode="numeric"
                                       class="input flex-1 {{ $errors->has('captcha') ? 'input-error' : '' }}"
                                       autocomplete="off" required placeholder="Jawabanmu" />
                            </div>
                            @error('captcha')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center justify-between gap-3">
                            <label for="remember_me" class="inline-flex cursor-pointer select-none items-center gap-2 text-sm text-creamDim">
                                <input id="remember_me" type="checkbox" name="remember"
                                       class="h-4 w-4 rounded border-line/40 bg-navy-deep accent-neon">
                                Ingat saya
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                   class="text-sm text-neon/90 underline-offset-4 transition hover:text-neon hover:underline">
                                    Lupa password?
                                </a>
                            @endif
                        </div>

                        <x-primary-button class="w-full py-3 text-base">
                            Masuk 🚀
                        </x-primary-button>
                    </form>
                </div>

                <p class="mt-6 text-center text-sm text-creamDim">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-semibold text-neon underline-offset-4 hover:underline">
                        Daftar sekarang
                    </a>
                </p>
            </div>
        </main>
    </div>

    {{-- JS: ganti soal captcha tanpa reload — aman walau route cache server masih stale --}}
    @php($captchaUrl = \Illuminate\Support\Facades\Route::has('captcha.reload') ? route('captcha.reload') : null)
    <script>
        const CAPTCHA_URL = {{ json_encode($captchaUrl) }};

        document.getElementById('captcha-reload')?.addEventListener('click', async function () {
            // Kalau route belum terdaftar (cache lama di server), fallback reload halaman
            if (!CAPTCHA_URL) {
                window.location.reload();
                return;
            }

            try {
                const res = await fetch(CAPTCHA_URL, {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) throw new Error();
                const data = await res.json();
                document.getElementById('captcha-box').textContent = `${data.a} + ${data.b} = ?`;
                const input = document.getElementById('captcha');
                input.value = '';
                input.focus();
            } catch (e) {
                window.location.reload();
            }
        });
    </script>
</x-guest-layout>
