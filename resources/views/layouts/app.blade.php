<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="{{ \App\Models\Setting::get('theme_mode', 'dark') === 'light' ? 'light' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Dashboard') · {{ config('app.name', 'Alumni67') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Space+Mono:wght@400;700&family=Inter:wght@400;500;600;700&family=Caveat:wght@600&display=swap" rel="stylesheet">

        {{-- Terapkan tema sebelum CSS render (anti flash) --}}
        @include('layouts.partials.theme-head')

        {{-- Favicon logo reuni 67 --}}
        @include('layouts.partials.favicon')

        {{-- Asset statis di public/ — tanpa Vite build, nama file tetap --}}
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="{{ asset('js/app.js') }}" defer></script>
    </head>
    <body class="font-sans pb-16 lg:pb-0">
        {{-- Tombol ganti tema dark/light — melayang.
            Mobile app: disembunyikan (toggle ada di navbar, gantikan tombol Masuk). --}}
        <div class="hidden lg:flex">
            @include('layouts.partials.theme-toggle')
        </div>

        @include('layouts.navigation')

        <main class="min-h-screen">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        @include('layouts.partials.site-footer')

        {{-- Menu bawah mobile: Home · Feed · Jobs · Event · Profile (sesuai konsep) --}}
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-neonDim/40 bg-navy-deep/95 backdrop-blur lg:hidden">
            <div class="grid grid-cols-5">
                <a href="{{ route('dashboard') }}" class="bottom-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5 9.5V21h5v-6h4v6h5V9.5"/></svg>
                    Home
                </a>
                <a href="{{ route('feed.index') }}" class="bottom-nav-link {{ request()->routeIs('feed.*') ? 'active' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 4v16l5-3 5 3V4H7z"/></svg>
                    Feed
                </a>
                <a href="{{ route('jobs.index') }}" class="bottom-nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path stroke-linecap="round" d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13h18"/></svg>
                    Jobs
                </a>
                <a href="{{ route('events.index') }}" class="bottom-nav-link {{ request()->routeIs('events.*', 'tickets.*') ? 'active' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path stroke-linecap="round" d="M8 3v4M16 3v4M3 11h18"/></svg>
                    Event
                </a>
                <a href="{{ route('profile.edit') }}" class="bottom-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 21c1.5-4 5-5 8-5s6.5 1 8 5"/></svg>
                    Profile
                </a>
            </div>
        </nav>
    </body>
</html>
