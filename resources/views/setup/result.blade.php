<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Instalasi · Alumni67 Connect</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-deep: #150A34; --navy-card: #34206E; --neon: #01F501;
            --neon-dim: rgba(1, 245, 1, .35); --cream: #F6F3EA; --cream-dim: #D2CBE8;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--navy-deep); color: var(--cream); font-family: 'Sora', system-ui, sans-serif; padding: 2.5rem 1rem 4rem; }
        .wrap { max-width: 52rem; margin: 0 auto; }
        .mono { font-family: 'Space Mono', ui-monospace, monospace; }
        .kicker { font-family: 'Space Mono', monospace; font-size: .68rem; letter-spacing: .18em; text-transform: uppercase; color: var(--neon); margin-bottom: .3rem; }
        h1 { font-size: 1.6rem; margin: .8rem 0 .4rem; }
        .sub { color: var(--cream-dim); font-size: .9rem; }
        .card { background: rgba(52, 32, 110, .6); border: 1px solid var(--neon-dim); border-radius: .6rem; padding: 1.25rem 1.4rem; margin: 1.4rem 0; }
        .card h2 { font-size: .9rem; margin: 0 0 .75rem; font-family: 'Space Mono', monospace; }
        pre { background: rgba(0,0,0,.45); border: 1px solid rgba(220,212,236,.15); border-radius: .4rem; padding: .9rem 1rem; font-family: 'Space Mono', monospace; font-size: .78rem; line-height: 1.55; white-space: pre-wrap; word-break: break-word; color: #b8ffb8; margin: .6rem 0; }
        table { width: 100%; border-collapse: collapse; font-size: .85rem; }
        th { text-align: left; font-family: 'Space Mono', monospace; font-size: .68rem; text-transform: uppercase; letter-spacing: .1em; color: var(--cream-dim); padding: .45rem .5rem; border-bottom: 1px solid rgba(220,212,236,.15); }
        td { padding: .45rem .5rem; border-bottom: 1px solid rgba(220,212,236,.08); }
        .btn { display: inline-flex; align-items: center; gap: .5rem; background: var(--neon); color: var(--navy-deep); font: inherit; font-weight: 700; padding: .7rem 1.4rem; border-radius: .4rem; text-decoration: none; border: 0; cursor: pointer; }
        .btn:hover { filter: brightness(1.15); }
        .fail { color: #ff6b6b; }
        .footnote { color: var(--cream-dim); font-size: .75rem; margin-top: 1.2rem; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="kicker">Web Installer</div>

    @if ($failed)
        <h1 class="fail">❌ Instalasi gagal</h1>
        <p class="sub">Perbaiki masalah di bawah, lalu jalankan ulang langkah instalasi dari halaman <a href="{{ url('setup') }}" style="color:var(--neon)">/setup</a>.</p>
    @else
        <h1 style="color:var(--neon)">✅ Instalasi selesai!</h1>
        <p class="sub">Semua tabel berhasil dibuat &amp; data awal (seed) dimasukkan. Error <span class="mono">sessions doesn't exist</span> kini teratasi.</p>
    @endif

    <div class="card">
        <h2>$ output instalasi</h2>
        @foreach ($log as $cmd => $out)
            <div class="mono" style="font-size:.72rem;color:var(--cream-dim);margin-top:.8rem">$ php artisan {{ $cmd }}</div>
            <pre>{{ $out }}</pre>
        @endforeach
    </div>

    @unless ($failed)
        <div class="card">
            <h2>🔑 Akun demo (password default: <span class="mono">password</span>)</h2>
            <table>
                <tr><th>Role</th><th>Email</th><th>Password</th></tr>
                @foreach ($credentials as [$role, $email, $pass])
                    <tr>
                        <td>{{ $role }}</td>
                        <td class="mono">{{ $email }}</td>
                        <td class="mono">{{ $pass }}</td>
                    </tr>
                @endforeach
            </table>
            <p class="footnote">
                ⚠ Segera ganti password setelah login pertama — terutama akun Super Admin.
            </p>
        </div>

        <a href="{{ url('/') }}" class="btn">Buka Website →</a>
        <a href="{{ url('login') }}" class="btn" style="background:transparent;color:var(--cream);border:1px solid rgba(246,243,234,.3)">Login</a>
    @endunless

    <p class="footnote">
        Installer sudah terkunci otomatis. Untuk perintah artisan lain (migrate, db:seed, cache:clear, dst.)
        login sebagai Super Admin → menu <strong>Admin → Terminal</strong>.
    </p>
</div>
</body>
</html>
