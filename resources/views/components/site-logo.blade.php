{{--
    Logo situs — SATU sumber untuk semua halaman (header, login, register).
    Dua varian sesuai tema:
      🌙 Dark mode  → setting `site_logo_dark`  (fallback: `site_logo` lama → file lokal)
      ☀️ Light mode → setting `site_logo_light` (fallback: pakai logo dark)
    Pergantian gambar dilakukan via CSS (lihat .logo-img-dark/.logo-img-light
    di app.css) sehingga instan saat tombol tema ditekan — tanpa reload.
    Kalau kedua gambar gagal load → tampilkan badge "67".
--}}
@props([
    'size'      => 'h-9 w-9',   // ukuran logo
    'textClass' => 'text-lg',   // ukuran teks badge default
])

@php
    $default = asset('img/logo-reuni67.png');
    $logoDark  = trim((string) \App\Models\Setting::get('site_logo_dark'))
        ?: trim((string) \App\Models\Setting::get('site_logo'))   // setting lama (legacy)
        ?: $default;
    $logoLight = trim((string) \App\Models\Setting::get('site_logo_light')) ?: $logoDark;
@endphp

<div {{ $attributes->merge(['class' => "$size shrink-0"]) }}>
    <img src="{{ $logoDark }}"
         alt="Logo {{ config('app.name', 'Alumni67') }} — mode gelap"
         class="logo-img-dark {{ $size }} rounded object-contain"
         loading="lazy"
         onerror="this.style.display='none';var p=this.closest('div');if(p&&p.querySelectorAll('img[style*=&quot;none&quot;]').length===2)p.querySelector('.logo-badge')?.classList.replace('hidden','flex');" />

    <img src="{{ $logoLight }}"
         alt="Logo {{ config('app.name', 'Alumni67') }} — mode terang"
         class="logo-img-light {{ $size }} rounded object-contain"
         loading="lazy"
         onerror="this.style.display='none';var p=this.closest('div');if(p&&p.querySelectorAll('img[style*=&quot;none&quot;]').length===2)p.querySelector('.logo-badge')?.classList.replace('hidden','flex');" />

    <div class="logo-badge {{ $size }} hidden items-center justify-center rounded bg-neon/10 font-display {{ $textClass }} text-neon ring-1 ring-neonDim">
        67
    </div>
</div>
