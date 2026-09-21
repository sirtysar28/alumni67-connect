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

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="card p-6 sm:p-8">
                <div class="mb-6">
                    <div class="kicker">Lupa password?</div>
                    <h2 class="h-display">Atur ulang password 🔑</h2>
                    <p class="mt-2 text-sm leading-relaxed text-creamDim">
                        Santai, semua orang pernah lupa. 😄 Masukkan email terdaftar kamu —
                        kami kirim tautan untuk membuat password baru.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="label">Email terdaftar</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               class="input {{ $errors->has('email') ? 'input-error' : '' }}"
                               required autofocus autocomplete="username"
                               placeholder="nama@email.com" />
                        @error('email')<p class="error-text">{{ $message }}</p>@enderror
                    </div>

                    <x-primary-button class="w-full py-3">
                        Kirim Tautan Reset 📧
                    </x-primary-button>
                </form>
            </div>

            <p class="mt-6 text-center text-sm text-creamDim">
                Tiba-tiba ingat password-nya?
                <a href="{{ route('login') }}" class="font-semibold text-neon underline-offset-4 hover:underline">Balik ke login</a>
            </p>
        </div>
    </div>
</x-guest-layout>
