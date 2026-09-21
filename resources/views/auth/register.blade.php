<x-guest-layout>
    <main class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-2xl">

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="mb-8 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                    <x-site-logo size="h-11 w-11" />
                    <span class="font-display text-2xl text-cream">Alumni<span class="text-neon">67</span></span>
                </a>
                <p class="mt-2 font-mono text-[11px] uppercase tracking-[0.2em] text-creamDim">
                    Komunitas Alumni SMUN 67 Halim
                </p>
            </div>

            <div class="card p-6 sm:p-8">
                <div class="mb-6">
                    <div class="kicker">Gabung komunitas</div>
                    <h2 class="h-display">Daftar akun baru</h2>
                    <p class="mt-2 text-sm text-creamDim">
                        Isi data di bawah biar kamu bisa diajak ketemu lagi sama teman seangkatan. 🎓
                    </p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <!-- Nama -->
                        <div>
                            <label for="name" class="label">Nama lengkap</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}"
                                   class="input {{ $errors->has('name') ? 'input-error' : '' }}"
                                   required autofocus autocomplete="name"
                                   placeholder="Nama kamu waktu sekolah" />
                            @error('name')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="label">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   class="input {{ $errors->has('email') ? 'input-error' : '' }}"
                                   required autocomplete="username"
                                   placeholder="nama@email.com" />
                            @error('email')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <!-- Kelas (angkatan 2003 & Bestie 67) -->
                        <div>
                            <label for="kelas" class="label">Kelas</label>
                            <select id="kelas" name="kelas" required
                                    class="input {{ $errors->has('kelas') ? 'input-error' : '' }}">
                                <option value="">— Pilih kelas —</option>
                                @foreach (\App\Http\Controllers\Auth\RegisteredUserController::KELAS as $k)
                                    <option value="{{ $k }}" @selected(old('kelas') === $k)>{{ $k }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-creamDim">
                                Bukan alumni angkatan 2003? Pilih <span class="text-neon">Bestie 67</span> 😎
                            </p>
                            @error('kelas')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <!-- Password (dengan tombol intip) -->
                        <div>
                            <label for="password" class="label">Password</label>
                            <x-password-input id="password" name="password"
                                               autocomplete="new-password" required
                                               class="{{ $errors->has('password') ? 'input-error' : '' }}"
                                               placeholder="Min. 8 karakter" />
                            @error('password')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <!-- Konfirmasi password (dengan tombol intip) -->
                        <div>
                            <label for="password_confirmation" class="label">Ulangi password</label>
                            <x-password-input id="password_confirmation" name="password_confirmation"
                                               autocomplete="new-password" required
                                               placeholder="Ketik ulang password" />
                        </div>
                    </div>

                    <x-primary-button class="w-full py-3 text-base">
                        Daftar Sekarang 🎉
                    </x-primary-button>
                </form>

                <div class="mt-4 rounded border border-neonDim/30 bg-neon/5 px-4 py-3 text-xs leading-relaxed text-creamDim">
                    ℹ️ Setelah mendaftar, akun kamu <b class="text-cream">menunggu persetujuan admin</b> dulu
                    sebelum bisa login — kami kabari via email begitu disetujui. ⏳
                </div>
            </div>

            <p class="mt-6 text-center text-sm text-creamDim">
                Udah punya akun?
                <a href="{{ route('login') }}" class="font-semibold text-neon underline-offset-4 hover:underline">
                    Login di sini
                </a>
            </p>
        </div>
    </main>
</x-guest-layout>
