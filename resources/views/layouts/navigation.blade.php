<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-neonDim/40 bg-navy-deep/90 backdrop-blur">
    <div class="wrap flex h-16 items-center justify-between gap-3">
        {{-- Brand — logo diambil dari setting `site_logo` (diubah Super Admin) --}}
        <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="flex items-center gap-2.5">
            <x-site-logo size="h-9 w-9" />
            <div class="leading-tight">
                <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-neon">SMUN 67 Halim</div>
                <div class="font-display text-base text-cream">Alumni67 Connect</div>
            </div>
        </a>

        {{-- Menu desktop --}}
        <div class="hidden items-center gap-5 text-sm text-cream/80 lg:flex">
            <a href="{{ route('direktori.index') }}" class="hover:text-neon">Direktori</a>
            <a href="{{ route('feed.index') }}" class="hover:text-neon">Feed</a>
            <a href="{{ route('jobs.index') }}" class="hover:text-neon">Bursa Kerja</a>
            <a href="{{ route('events.index') }}" class="hover:text-neon">Event</a>
            <a href="{{ route('forum.index') }}" class="hover:text-neon">Forum</a>
            <a href="{{ route('donasi.index') }}" class="hover:text-neon">Donasi</a>
            @role('super_admin|pengurus')
                <a href="{{ route('admin.index') }}" class="font-semibold text-neon hover:underline">Admin</a>
            @endrole
        </div>

        <div class="flex items-center gap-3">
            @auth
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 rounded-full border border-line/30 py-1 pl-1 pr-3 text-sm text-cream hover:border-neon">
                            <div class="avatar h-8 w-8 text-xs">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                            <span class="hidden max-w-28 truncate sm:block">{{ auth()->user()->name }}</span>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('dashboard')">Dashboard</x-dropdown-link>
                        <x-dropdown-link :href="route('tickets.index')">Tiket Saya</x-dropdown-link>
                        <x-dropdown-link :href="route('profile.edit')">Profil</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Keluar
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            @else
                {{-- Desktop saja — di mobile digantikan toggle tema; login lewat menu hamburger --}}
                <a href="{{ route('login') }}" class="btn-outline hidden !px-4 !py-2 lg:flex">Masuk</a>
                <a href="{{ route('register') }}" class="btn-neon !px-4 !py-2 hidden sm:inline-flex">Daftar</a>
            @endauth

            {{-- Toggle tema dark/light — tampil di mobile/tablet (desktop pakai tombol melayang).
                Menggantikan posisi tombol "Masuk" di header mobile. --}}
            <button type="button" data-theme-toggle
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-line/40 text-creamDim transition hover:border-neon hover:text-neon lg:hidden"
                    title="Ganti tema gelap/terang" aria-label="Ganti tema">
                <svg class="h-5 w-5" style="display:none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
                <svg class="h-5 w-5" style="display:none" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
            </button>

            {{-- Tombol hamburger — khusus mobile/tablet (Desktop tampil di lg+) --}}
            <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-line/40 text-creamDim transition hover:border-neon hover:text-neon lg:hidden"
                    aria-label="Buka menu navigasi" aria-controls="nav-mobile">
                <svg x-show="!open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg x-show="open" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- ==== PANEL MENU MOBILE ==== --}}
    <div id="nav-mobile" x-show="open" x-cloak x-transition class="border-t border-neonDim/40 lg:hidden">
        <div class="wrap grid grid-cols-2 gap-x-6 py-3 text-sm text-cream/80">
            <a href="{{ route('direktori.index') }}" class="py-2 hover:text-neon" @click="open = false">Direktori</a>
            <a href="{{ route('feed.index') }}" class="py-2 hover:text-neon" @click="open = false">Feed</a>
            <a href="{{ route('jobs.index') }}" class="py-2 hover:text-neon" @click="open = false">Bursa Kerja</a>
            <a href="{{ route('events.index') }}" class="py-2 hover:text-neon" @click="open = false">Event</a>
            <a href="{{ route('forum.index') }}" class="py-2 hover:text-neon" @click="open = false">Forum</a>
            <a href="{{ route('donasi.index') }}" class="py-2 hover:text-neon" @click="open = false">Donasi</a>
            @role('super_admin|pengurus')
                <a href="{{ route('admin.index') }}" class="py-2 font-semibold text-neon hover:underline" @click="open = false">Admin</a>
            @endrole
            @guest
                <a href="{{ route('login') }}" class="py-2 font-semibold text-cream hover:text-neon" @click="open = false">Masuk</a>
                <a href="{{ route('register') }}" class="py-2 text-neon hover:underline" @click="open = false">Daftar</a>
            @endguest
        </div>
    </div>
</nav>
