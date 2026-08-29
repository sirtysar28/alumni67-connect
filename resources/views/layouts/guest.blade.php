<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="{{ \App\Models\Setting::get('theme_mode', 'dark') === 'light' ? 'light' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Alumni67') }}</title>

        <!-- Fonts: judul Sora · body Inter · aksen tangan Caveat -->
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
    <body class="font-sans">
        {{-- Tombol ganti tema dark/light — melayang (desktop saja).
            Mobile: disembunyikan — toggle sudah ada di navbar (di samping hamburger). --}}
        <div class="hidden lg:flex">
            @include('layouts.partials.theme-toggle')
        </div>

        {{ $slot }}
    </body>
</html>
