<x-app-layout>
    <div class="wrap max-w-3xl py-8">
        <div class="kicker">Pesan pribadi</div>
        <h1 class="font-display text-2xl text-cream sm:text-3xl">Chat Alumni 💬</h1>

        @if ($ready === false)
            <div class="flash-err mt-4">
                🔧 <b>Fitur chat belum aktif di server ini.</b>
                <span class="text-xs">Tabel <code>messages</code> belum dibuat migrasi. Super Admin dapat mengaktifkannya melalui
                <b>Admin → Terminal Artisan</b> → tombol <b>migrate --force</b>.</span>
            </div>
        @endif

        @if ($peer)
            <p class="mt-2 text-sm text-creamDim">
                Percakapan dengan <b class="text-cream">{{ $peer->name }}</b> terbuka di panel chat
                <span class="text-neon">pojok kanan bawah ↘</span>
            </p>
        @else
            <p class="mt-2 text-sm text-creamDim">
                Panel chat terbuka di <span class="text-neon">pojok kanan bawah ↘</span> —
                pilih alumni untuk mulai mengobrol. Semua alumni yang sudah disetujui bisa di-chat.
            </p>
        @endif

        <div class="card mt-6">
            <div class="kicker !mb-0">ℹ️ Tentang chat alumni</div>
            <ul class="mt-3 list-inside list-disc space-y-1.5 text-sm text-creamDim">
                <li>Pesan tersimpan privat — hanya kamu dan penerima yang melihatnya.</li>
                <li>Penerima mendapat <b class="text-cream">notifikasi lonceng 🔔</b> saat ada pesan baru.</li>
                <li>Tanda <span class="font-mono text-neon">✓</span> = terkirim, <span class="font-mono text-neon">✓✓</span> = sudah dibaca penerima.</li>
                <li>Chat juga bisa dibuka dari ikon 💬 di semua halaman, kapan pun setelah login.</li>
            </ul>
            <div class="mt-4">
                <a href="{{ route('direktori.index') }}" class="btn-outline !px-4 !py-2 text-xs">Cari alumni di direktori →</a>
            </div>
        </div>
    </div>
</x-app-layout>
