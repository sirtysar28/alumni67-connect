<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sudah terpasang · Alumni67 Connect</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=Space+Mono&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; background: #150A34; color: #F6F3EA; font-family: 'Sora', system-ui, sans-serif; display: grid; place-items: center; min-height: 100vh; padding: 1rem; }
        .box { max-width: 34rem; background: rgba(52,32,110,.6); border: 1px solid rgba(1,245,1,.35); border-radius: .6rem; padding: 2rem; }
        h1 { font-size: 1.3rem; margin: 0 0 .5rem; color: #01F501; }
        p { font-size: .9rem; color: #D2CBE8; line-height: 1.6; }
        code { font-family: 'Space Mono', monospace; font-size: .8em; background: rgba(0,0,0,.35); padding: .1em .4em; border-radius: .3em; }
        a { color: #01F501; }
    </style>
</head>
<body>
<div class="box">
    <h1>🔒 Aplikasi sudah terpasang</h1>
    <p>
        Installer <code>/setup</code> sudah pernah dijalankan dan terkunci demi keamanan.
        Untuk menjalankan perintah artisan (<code>migrate</code>, <code>db:seed</code>,
        <code>cache:clear</code>, dst.), login sebagai <strong>Super Admin</strong> lalu buka
        <strong>Admin → Terminal</strong>.
    </p>
    <p>
        Butuh menjalankan ulang installer? Hapus file
        <code>storage/app/setup-installed.lock</code> lewat File Manager cPanel, lalu buka
        <a href="{{ url('setup') }}">/setup</a> lagi.
    </p>
    <p><a href="{{ url('/') }}">← Kembali ke website</a></p>
</div>
</body>
</html>
