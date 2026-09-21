<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Installer · Alumni67 Connect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    {{-- CSS mandiri — installer tidak boleh bergantung pada session/DB --}}
    <style>
        :root {
            --navy-deep: #150A34; --navy-card: #34206E; --neon: #01F501;
            --neon-dim: rgba(1, 245, 1, .35); --cream: #F6F3EA; --cream-dim: #D2CBE8;
            --line: #DCD4EC;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; background: var(--navy-deep); color: var(--cream);
            font-family: 'Sora', system-ui, sans-serif; padding: 2.5rem 1rem 4rem;
        }
        .wrap { max-width: 52rem; margin: 0 auto; }
        .mono { font-family: 'Space Mono', ui-monospace, monospace; }
        .brand { display: flex; align-items: center; gap: .65rem; }
        .logo {
            width: 2.6rem; height: 2.6rem; border-radius: .55rem; display: grid; place-items: center;
            background: var(--navy-card); border: 1px solid var(--neon-dim);
            color: var(--neon); font-family: 'Space Mono', monospace; font-weight: 700;
        }
        .kicker { font-family: 'Space Mono', monospace; font-size: .68rem; letter-spacing: .18em; text-transform: uppercase; color: var(--neon); margin-bottom: .3rem; }
        h1 { font-size: 1.6rem; margin: .8rem 0 .2rem; }
        .sub { color: var(--cream-dim); font-size: .9rem; margin: 0 0 1.8rem; }
        .card {
            background: rgba(52, 32, 110, .6); border: 1px solid var(--neon-dim);
            border-radius: .6rem; padding: 1.25rem 1.4rem; margin-bottom: 1.25rem;
        }
        .card h2 { font-size: .95rem; margin: 0 0 1rem; display: flex; align-items: center; gap: .5rem; }
        .step {
            width: 1.5rem; height: 1.5rem; flex: none; border-radius: 999px; display: grid; place-items: center;
            background: var(--neon); color: var(--navy-deep); font-family: 'Space Mono', monospace; font-size: .75rem; font-weight: 700;
        }
        table { width: 100%; border-collapse: collapse; font-size: .85rem; }
        th { text-align: left; font-family: 'Space Mono', monospace; font-size: .68rem; text-transform: uppercase; letter-spacing: .1em; color: var(--cream-dim); padding: .45rem .5rem; border-bottom: 1px solid rgba(220,212,236,.15); }
        td { padding: .45rem .5rem; border-bottom: 1px solid rgba(220,212,236,.08); }
        .ok { color: var(--neon); } .bad { color: #ff6b6b; }
        label { display: block; font-family: 'Space Mono', monospace; font-size: .68rem; text-transform: uppercase; letter-spacing: .1em; color: var(--cream-dim); margin: .9rem 0 .35rem; }
        input, select {
            width: 100%; padding: .6rem .75rem; border-radius: .4rem; color: var(--cream);
            background: rgba(21, 10, 52, .7); border: 1px solid rgba(220,212,236,.25); font: inherit;
        }
        input:focus, select:focus { outline: none; border-color: var(--neon); }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1rem; }
        @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } }
        .btn {
            display: inline-flex; align-items: center; gap: .5rem; border: 0; cursor: pointer;
            background: var(--neon); color: var(--navy-deep); font: inherit; font-weight: 700;
            padding: .7rem 1.4rem; border-radius: .4rem; text-decoration: none;
        }
        .btn:hover { filter: brightness(1.15); }
        .btn[disabled] { opacity: .35; cursor: not-allowed; }
        .alert-ok { border: 1px solid var(--neon-dim); background: rgba(1,245,1,.1); color: var(--neon); border-radius: .4rem; padding: .75rem 1rem; font-size: .85rem; margin-bottom: 1.25rem; }
        .alert-err { border: 1px solid rgba(255,107,107,.4); background: rgba(255,107,107,.1); color: #ffb3b3; border-radius: .4rem; padding: .75rem 1rem; font-size: .85rem; margin-bottom: 1.25rem; }
        .alert-err ul { margin: .3rem 0 0; padding-left: 1.2rem; }
        pre {
            background: rgba(0,0,0,.45); border: 1px solid rgba(220,212,236,.15); border-radius: .4rem;
            padding: .9rem 1rem; font-family: 'Space Mono', monospace; font-size: .78rem; line-height: 1.55;
            white-space: pre-wrap; word-break: break-word; color: #b8ffb8;
        }
        .footnote { color: var(--cream-dim); font-size: .75rem; margin-top: 1.5rem; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <div class="logo">67</div>
        <div>
            <div class="mono" style="font-size:.62rem; letter-spacing:.14em; text-transform:uppercase; color:var(--neon)">SMUN 67 Halim</div>
            <strong>Alumni67 Connect</strong>
        </div>
    </div>

    <div class="kicker" style="margin-top:1.4rem">Web Installer</div>
    <h1>Pasang aplikasi tanpa terminal 🚀</h1>
    <p class="sub">
        Installer untuk shared hosting / cPanel tanpa akses SSH — menjalankan
        <span class="mono">migrate</span>, <span class="mono">db:seed</span>, dan <span class="mono">storage:link</span>
        langsung dari browser. Mengatasi error
        <span class="mono">Table … sessions doesn't exist</span>.
    </p>

    @if ($notice)<div class="alert-ok">{{ $notice }}</div>@endif

    @if ($errors)
        <div class="alert-err">
            <strong>⚠ Gagal menyimpan konfigurasi:</strong>
            <ul>@foreach ($errors as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- ================= LANGKAH 1: REQUIREMENT ================= --}}
    <div class="card">
        <h2><span class="step">1</span> Cek kebutuhan server</h2>
        <table>
            <tr><th>Pemeriksaan</th><th>Status</th><th>Keterangan</th></tr>
            @php $reqOk = true; @endphp
            @foreach ($checks as [$label, $ok, $note])
                @php
                    // pdo_mysql / .env hanya "wajib" kontekstual → tandai gagal hanya jika benar2 fatal
                    if (! $ok && ! in_array($label, ['Ekstensi pdo_mysql', 'APP_KEY ter-set'])) $reqOk = false;
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="{{ $ok ? 'ok' : 'bad' }}">{{ $ok ? '✓' : '✗' }}</td>
                    <td class="mono" style="font-size:.72rem; color:var(--cream-dim)">{{ $note }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    {{-- ================= LANGKAH 2: DATABASE ================= --}}
    <div class="card">
        <h2><span class="step">2</span> Konfigurasi database</h2>
        <p style="font-size:.85rem;color:var(--cream-dim);margin:0 0 .4rem">
            Isi sesuai database MySQL yang sudah dibuat di cPanel
            (menu <em>MySQL® Databases</em>). Nilai tersimpan ke file <span class="mono">.env</span>.
        </p>

        {{-- NB: tanpa @csrf — installer sengaja tanpa session (tabel sessions belum ada) --}}
        <form method="POST" action="{{ url('setup/database') }}">
            <label for="driver">Driver</label>
            <select id="driver" name="driver">
                <option value="mysql" @selected(($form['driver'] ?? $db['driver']) === 'mysql')>MySQL / MariaDB (cPanel)</option>
                <option value="sqlite" @selected(($form['driver'] ?? $db['driver']) === 'sqlite')>SQLite (lokal)</option>
            </select>

            <div class="grid">
                <div>
                    <label for="host">DB Host</label>
                    <input id="host" name="host" value="{{ $form['host'] ?? $db['host'] }}" placeholder="localhost">
                </div>
                <div>
                    <label for="port">DB Port</label>
                    <input id="port" name="port" value="{{ $form['port'] ?? $db['port'] }}" placeholder="3306">
                </div>
            </div>

            <label for="database">Nama Database <span style="text-transform:none">(mis. alux9774_alumni67)</span></label>
            <input id="database" name="database" value="{{ $form['database'] ?? $db['database'] }}" placeholder="cpaneluser_alumni67">

            <label for="username">DB Username</label>
            <input id="username" name="username" value="{{ $form['username'] ?? $db['username'] }}" placeholder="cpaneluser_admin">

            <label for="password">DB Password</label>
            <input id="password" name="password" type="password" value="" placeholder="••••••••">

            <div style="margin-top:1.4rem">
                <button type="submit" class="btn">Simpan &amp; Tes Koneksi →</button>
            </div>
        </form>
    </div>

    {{-- ================= LANGKAH 3: INSTALL ================= --}}
    <div class="card">
        <h2><span class="step">3</span> Migrasi &amp; data awal</h2>
        <p style="font-size:.85rem;color:var(--cream-dim);margin:0 0 .9rem">
            Jalankan <span class="mono">php artisan migrate --seed</span> + <span class="mono">storage:link</span>.
            Tombol aktif setelah koneksi database berhasil diuji pada langkah 2.
        </p>

        @php $dbOk = $dbOk ?? false; @endphp
        <form method="POST" action="{{ url('setup/install') }}">
            <button type="submit" class="btn" @unless($dbOk) disabled title="Selesaikan langkah 2 dulu" @endunless>
                ⚡ Jalankan Instalasi
            </button>
        </form>
    </div>

    <p class="footnote">
        Setelah instalasi sukses, installer terkunci otomatis (file
        <span class="mono">storage/app/setup-installed.lock</span>). Untuk migrate/seed berikutnya,
        login sebagai Super Admin lalu buka menu <strong>Admin → Terminal</strong>.
    </p>
</div>
</body>
</html>
