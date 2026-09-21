<x-guest-layout>
    <div class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-md">

            {{-- Logo --}}
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
                    <div class="kicker">Password baru</div>
                    <h2 class="h-display">Buat password baru 🎉</h2>
                    <p class="mt-2 text-sm leading-relaxed text-creamDim">
                        Pilih password yang kuat ya — minimal 8 karakter.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                    @csrf

                    <!-- Token reset (dari URL email) -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div>
                        <label for="email" class="label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
                               class="input {{ $errors->has('email') ? 'input-error' : '' }}"
                               required autocomplete="username" />
                        @error('email')<p class="error-text">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="label">Password baru</label>
                        <x-password-input id="password" name="password" required
                                           autocomplete="new-password"
                                           class="{{ $errors->has('password') ? 'input-error' : '' }}"
                                           placeholder="Password baru kamu" />
                        @error('password')<p class="error-text">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="label">Ulangi password baru</label>
                        <x-password-input id="password_confirmation" name="password_confirmation" required
                                           autocomplete="new-password"
                                           placeholder="Ketik ulang password" />
                        @error('password_confirmation')<p class="error-text">{{ $message }}</p>@enderror
                    </div>

                    <x-primary-button class="w-full py-3">
                        Simpan Password Baru 🔒
                    </x-primary-button>
                </form>
            </div>

            <p class="mt-6 text-center text-sm text-creamDim">
                Ingat password lamanya?
                <a href="{{ route('login') }}" class="font-semibold text-neon underline-offset-4 hover:underline">Balik ke login</a>
            </p>
        </div>
    </div>
</x-guest-layout>
