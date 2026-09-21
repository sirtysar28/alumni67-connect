<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/*
|--------------------------------------------------------------------------
| STANDALONE WEB INSTALLER — Alumni67 Connect
|--------------------------------------------------------------------------
| Untuk cPanel / shared hosting TANPA akses SSH.
|
| Akses langsung:  https://domain-anda.com/setup.php
|
| File ini TIDAK melewati routing Laravel sama sekali (kebal 404 /
| route-cache / .htaccess), tidak memakai session & CSRF — jadi tetap
| jalan walau tabel `sessions` belum ada (belum di-migrate).
|
| Setelah instalasi sukses file ini terkunci otomatis lewat
| storage/app/setup-installed.lock. Untuk keamanan, hapus file ini
| setelah selesai (opsional — sudah terkunci pun tidak berbahaya).
*/

$BASE = dirname(__DIR__);
$LOCK = $BASE.'/storage/app/setup-installed.lock';

/* ---------- helper ---------- */
function h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function env_set(string $base, array $values): void
{
    $path = $base.'/.env';
    if (! is_file($path)) {
        $example = $base.'/.env.example';
        is_file($example) ? copy($example, $path) : file_put_contents($path, '');
    }
    $content = (string) file_get_contents($path);

    foreach ($values as $key => $value) {
        $value  = (string) $value;
        $quoted = ($value === '' || preg_match('/^[A-Za-z0-9_.\-\/]+$/', $value)) ? $value : '"'.str_replace('"', '\\"', $value).'"';
        $line   = $key.'='.$quoted;

        if (preg_match('/^'.preg_quote($key, '/').'\s*=.*$/m', $content)) {
            $content = (string) preg_replace('/^'.preg_quote($key, '/').'\s*=.*$/m', $line, $content, 1);
        } elseif (preg_match('/^\s*#\s*'.preg_quote($key, '/').'\s*=.*$/m', $content)) {
            $content = (string) preg_replace('/^\s*#\s*'.preg_quote($key, '/').'\s*=.*$/m', $line, $content, 1);
        } else {
            $content = rtrim($content)."\n\n".$line."\n";
        }
    }
    file_put_contents($path, $content);
}

function clear_bootstrap_cache(string $base): void
{
    // route & config cache lama = penyebab utama 404 di route baru (/setup)
    foreach (glob($base.'/bootstrap/cache/routes-*.php') ?: [] as $f) { @unlink($f); }
    @unlink($base.'/bootstrap/cache/config.php');
}

function exit_page(string $titleHtml, string $bodyHtml): never
{
    header('Content-Type: text/html; charset=utf-8');
    exit('<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'.
        '<meta name="robots" content="noindex,nofollow"><title>Setup · Alumni67 Connect</title>'.
        '<style>body{margin:0;background:#150A34;color:#F6F3EA;font-family:system-ui,sans-serif;padding:3rem 1rem}'.
        '.w{max-width:36rem;margin:0 auto}.k{font-family:monospace;font-size:.7rem;letter-spacing:.18em;text-transform:uppercase;color:#01F501;margin-bottom:.3rem}'.
        'h1{font-size:1.4rem;margin:.6rem 0 .8rem}.sub{color:#D2CBE8;font-size:.9rem;line-height:1.6}</style></head><body><div class="w">'.
        '<div class="k">Standalone Installer</div>'.$titleHtml.'<div class="sub">'.$bodyHtml.'</div></div></body></html>');
}

/* ---------- SUDAH TERPASANG? ---------- */
$REPAIR = isset($_GET['repair']);
if (is_file($LOCK) && ! $REPAIR) {
    http_response_code(403);
    exit('<!doctype html><meta charset="utf-8"><title>Installer terkunci</title>
<body style="margin:0;background:#150A34;color:#F6F3EA;font-family:system-ui;display:grid;place-items:center;min-height:100vh;padding:1rem">
<div style="max-width:34rem;background:rgba(52,32,110,.6);border:1px solid rgba(1,245,1,.35);border-radius:.6rem;padding:2rem">
<h1 style="color:#01F501;font-size:1.2rem;margin:0 0 .5rem">🔒 Aplikasi sudah terpasang</h1>
<p style="color:#D2CBE8;font-size:.9rem;line-height:1.6">Installer sudah pernah dijalankan dan terkunci. Untuk perintah artisan lain
(<code>migrate</code>, <code>db:seed</code>, <code>cache:clear</code>, dst.) login sebagai <b>Super Admin</b> lalu buka <b>Admin → Terminal</b>.</p>
<p style="color:#D2CBE8;font-size:.85rem">Butuh jalankan ulang installer? Hapus file <code>storage/app/setup-installed.lock</code>
lewat File Manager cPanel, lalu buka <code>/setup.php</code> lagi.</p>
</div></body>');
}

/* ---------- BOOT LARAVEL (tanpa routing) ---------- */
require $BASE.'/vendor/autoload.php';
$app = require $BASE.'/bootstrap/app.php';
/** @var ConsoleKernel $kernel */
$kernel = $app->make(ConsoleKernel::class);
$kernel->bootstrap();

clear_bootstrap_cache($BASE);

/* =========================================================
 * MODE PERBAIKAN — /setup.php?repair
 * Untuk kondisi "chicken-and-egg": menu Terminal hanya muncul untuk
 * super_admin, tapi roles belum ada di database. Fitur ini membuat
 * roles standar + mempromosikan user jadi Super Admin TANPA perlu role.
 *
 * Keamanan: otomatis NONAKTIF begitu sudah ada ≥1 user super_admin.
 * ======================================================= */
if ($REPAIR) {
    $rMsg = null;
    $rErr = null;

    if (! Schema::hasTable('users')) {
        exit_page('<h1 class="bad">Database belum ter-migrate</h1>',
            'Tabel <b>users</b> belum ada. Buka <code>/setup.php</code> (tanpa ?repair) dan jalankan instalasi dulu.');
    }

    $rolesOk = Schema::hasTable('roles') && Schema::hasTable('model_has_roles');
    $hasApprovedCol = Schema::hasColumn('users', 'is_approved');

    // Super admin "aktif" = punya role DAN sudah approved (kolom belum ada dianggap aktif).
    // Gate dikunci hanya oleh admin AKTIF — admin yang terkunci (belum approved)
    // tetap bisa diperbaiki lewat halaman ini.
    $fetchAdmins = function () use ($rolesOk) {
        return $rolesOk && Role::where('name', 'super_admin')->where('guard_name', 'web')->exists()
            ? User::role('super_admin')->orderBy('id')->get()
            : collect();
    };
    $superAdmins = $fetchAdmins();
    $activeAdmins  = $superAdmins->filter(fn ($u) => ! $hasApprovedCol || (bool) $u->is_approved);
    $lockedAdmins  = $superAdmins->filter(fn ($u) => $hasApprovedCol && ! $u->is_approved)->values();
    $locked = $activeAdmins->isNotEmpty();

    // helper: approve paksa (forceFill — tahan bila model lama belum punya fillable)
    $approveUser = function (User $u) use ($hasApprovedCol): void {
        if ($hasApprovedCol) {
            $u->forceFill(['is_approved' => true, 'approval_note' => null])->save();
        }
    };

    /* ---- aksi POST ---- */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ! $locked) {
        $action = $_POST['action'] ?? '';

        if ($action === 'migrate') {
            try {
                Artisan::call('migrate', ['--force' => true]);
                $rMsg = '$ php artisan migrate --force'."\n\n".trim((string) Artisan::output());
                $hasApprovedCol = Schema::hasColumn('users', 'is_approved');
                $rolesOk = Schema::hasTable('roles') && Schema::hasTable('model_has_roles');
                $superAdmins = $fetchAdmins();
                $activeAdmins  = $superAdmins->filter(fn ($u) => ! $hasApprovedCol || (bool) $u->is_approved);
                $lockedAdmins  = $superAdmins->filter(fn ($u) => $hasApprovedCol && ! $u->is_approved)->values();
                $locked = $activeAdmins->isNotEmpty();
            } catch (\Throwable $e) {
                $rErr = 'migrate gagal: '.$e->getMessage();
            }
        }

        if ($action === 'approve') {
            $u = User::find((int) ($_POST['user_id'] ?? 0));
            if (! $u) {
                $rErr = 'User tidak ditemukan.';
            } else {
                $approveUser($u);
                app(PermissionRegistrar::class)->forgetCachedPermissions();
                try { Artisan::call('cache:clear'); } catch (\Throwable) {}

                $rMsg = '✅ Akun <b>'.h($u->name).'</b> ('.h($u->email).') diaktifkan (is_approved = 1).<br>Silakan <b>login ulang</b>.';
                $superAdmins = $fetchAdmins();
                $activeAdmins  = $superAdmins->filter(fn ($u) => ! $hasApprovedCol || (bool) $u->is_approved);
                $lockedAdmins  = $superAdmins->filter(fn ($u) => $hasApprovedCol && ! $u->is_approved)->values();
                $locked = $activeAdmins->isNotEmpty();
            }
        }

        if ($action === 'promote') {
            $u = User::find((int) ($_POST['user_id'] ?? 0));
            if (! $u) {
                $rErr = 'User tidak ditemukan.';
            } elseif (! Schema::hasTable('roles')) {
                $rErr = 'Tabel roles belum ada — jalankan migrate dulu.';
            } else {
                // buat role standar dulu supaya scope role() tidak throw
                foreach (['super_admin', 'pengurus', 'ketua_angkatan', 'alumni'] as $r) {
                    Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
                }
                // gate: pastikan belum ada super admin AKTIF lain
                if (User::role('super_admin')
                    ->whereKeyNot($u->getKey())
                    ->get()
                    ->filter(fn ($x) => ! $hasApprovedCol || (bool) $x->is_approved)
                    ->isNotEmpty()) {
                    $rErr = 'Sudah ada Super Admin aktif lain — fitur ini nonaktif. Minta super admin tersebut menambahkan role lewat panel.';
                } else {
                    $u->assignRole('super_admin');
                    $approveUser($u); // WAJIB: promote tanpa approve = akun tak bisa login!
                    app(PermissionRegistrar::class)->forgetCachedPermissions();
                    try { Artisan::call('cache:clear'); } catch (\Throwable) {}

                    $rMsg = '✅ '.h($u->name).' ('.h($u->email).') sekarang <b>Super Admin</b> &amp; akunnya <b>aktif</b>.<br><br>'.
                            '⚠ Penting: <b>LOGOUT dulu lalu login ulang</b> agar role terbaca &amp; menu <b>Admin → &gt;_ Terminal Artisan</b> muncul.';
                    $superAdmins = $fetchAdmins();
                    $activeAdmins  = $superAdmins->filter(fn ($u) => ! $hasApprovedCol || (bool) $u->is_approved);
                    $lockedAdmins  = $superAdmins->filter(fn ($u) => $hasApprovedCol && ! $u->is_approved)->values();
                    $locked = $activeAdmins->isNotEmpty();
                }
            }
        }
    }

    /* ---- render halaman repair ---- */
    $users = User::orderBy('id')->limit(200)->get();
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>Perbaikan Roles · Alumni67 Connect</title>
<style>body{margin:0;background:#150A34;color:#F6F3EA;font-family:'Sora',system-ui,sans-serif;padding:2.5rem 1rem 4rem}
.w{max-width:44rem;margin:0 auto}.k{font-family:'Space Mono',monospace;font-size:.68rem;letter-spacing:.18em;text-transform:uppercase;color:#01F501;margin-bottom:.3rem}
h1{font-size:1.5rem;margin:.8rem 0 .4rem}.sub{color:#D2CBE8;font-size:.9rem}
.c{background:rgba(52,32,110,.6);border:1px solid rgba(1,245,1,.35);border-radius:.6rem;padding:1.25rem 1.4rem;margin:1.2rem 0}
.m{font-family:'Space Mono',monospace}.bad{color:#ff6b6b}.ok{color:#01F501}
.aok{border:1px solid rgba(1,245,1,.35);background:rgba(1,245,1,.1);color:#01F501;border-radius:.4rem;padding:.75rem 1rem;font-size:.85rem;margin-bottom:1.2rem}
.aerr{border:1px solid rgba(255,107,107,.4);background:rgba(255,107,107,.1);color:#ffb3b3;border-radius:.4rem;padding:.75rem 1rem;font-size:.85rem;margin-bottom:1.2rem}
label{display:block;font-family:'Space Mono',monospace;font-size:.68rem;text-transform:uppercase;letter-spacing:.1em;color:#D2CBE8;margin:.9rem 0 .35rem}
select{width:100%;padding:.6rem .75rem;border-radius:.4rem;color:#F6F3EA;background:rgba(21,10,52,.7);border:1px solid rgba(220,212,236,.25);font:inherit}
.btn{display:inline-flex;border:0;cursor:pointer;background:#01F501;color:#150A34;font:inherit;font-weight:700;padding:.7rem 1.4rem;border-radius:.4rem}
.btn:hover{filter:brightness(1.15)}
pre{background:rgba(0,0,0,.45);border:1px solid rgba(220,212,236,.15);border-radius:.4rem;padding:.9rem 1rem;font-family:'Space Mono',monospace;font-size:.78rem;line-height:1.55;white-space:pre-wrap;color:#b8ffb8}
.f{color:#D2CBE8;font-size:.75rem;margin-top:1.2rem}</style></head><body><div class="w">
<div class="k">Mode Perbaikan — setup.php?repair</div>
<h1>Perbaiki roles &amp; Super Admin 🔧</h1>
<p class="sub">Membuat roles standar (super_admin, pengurus, ketua_angkatan, alumni) dan mempromosikan akun menjadi Super Admin — berguna saat menu Terminal tidak muncul karena role belum ada.</p>
<?php if ($rMsg): ?><div class="aok"><?= $rMsg ?></div><?php endif; ?>
<?php if ($rErr): ?><div class="aerr">⚠ <?= $rErr ?></div><?php endif; ?>

<?php if (! $rolesOk || ! $hasApprovedCol): ?>
<div class="c"><h2 class="bad"><?= ! $rolesOk ? 'Tabel roles belum ada' : 'Kolom is_approved belum ada' ?></h2>
<p class="sub"><?= ! $rolesOk
    ? 'Migration permission belum jalan.'
    : 'Migration <code>add_is_approved_to_users_table</code> belum dijalankan — fitur approval akun belum aktif dan berbahaya bila kode baru sudah ter-upload.' ?>
Jalankan dulu:</p>
<form method="POST" action=""><input type="hidden" name="action" value="migrate">
<button type="submit" class="btn">▶ php artisan migrate --force</button></form></div>
<?php endif; ?>

<?php if ($lockedAdmins->isNotEmpty()): ?>
<div class="c"><h2 class="bad">🔒 Ada Super Admin yang terkunci (belum approved)</h2>
<p class="sub">Akun di bawah punya role super_admin tapi <code>is_approved = 0</code> sehingga <b>tidak bisa login</b>. Aktifkan sekarang:</p>
<?php foreach ($lockedAdmins as $la): ?>
<form method="POST" action="" class="m" style="display:flex;align-items:center;justify-content:space-between;gap:.8rem;padding:.5rem 0;border-bottom:1px solid rgba(220,212,236,.1)">
<input type="hidden" name="action" value="approve">
<input type="hidden" name="user_id" value="<?= $la->id ?>">
<span style="font-size:.8rem">#<?= $la->id ?> — <?= h($la->name) ?> &lt;<?= h($la->email) ?>&gt;</span>
<button type="submit" class="btn" style="padding:.4rem 1rem;font-size:.8rem">🔓 Aktifkan</button>
</form>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($locked): ?>
<div class="c"><h2 class="ok">Sudah ada Super Admin aktif ✓</h2>
<p class="sub">Fitur perbaikan ini <b>nonaktif</b> demi keamanan. Super Admin aktif saat ini:</p>
<ul class="m" style="font-size:.8rem"><?php foreach ($activeAdmins as $sa): ?><li>#<?= $sa->id ?> — <?= h($sa->name) ?> &lt;<?= h($sa->email) ?>&gt;</li><?php endforeach; ?></ul>
<p class="f">Kalau menu Terminal belum muncul padahal sudah login dengan akun di atas: <b>logout dulu lalu login ulang</b> (role di-cache per sesi).</p></div>
<?php else: ?>
<div class="c"><h2>Promosikan jadi Super Admin</h2>
<p class="sub">Pilih akun kamu (register/login biasa), lalu klik tombol. Role standar dibuat otomatis, akun langsung diaktifkan, lalu <b>logout &amp; login ulang</b>.</p>
<form method="POST" action="">
<input type="hidden" name="action" value="promote">
<label for="user_id">Pilih akun (<?= $users->count() ?> user)</label>
<select id="user_id" name="user_id" required>
<?php foreach ($users as $u): ?><option value="<?= $u->id ?>">#<?= $u->id ?> — <?= h($u->name) ?> &lt;<?= h($u->email) ?>&gt;</option><?php endforeach; ?>
</select>
<div style="margin-top:1.2rem"><button type="submit" class="btn">🔑 Jadikan Super Admin</button></div>
</form></div>
<?php endif; ?>
<p class="f">Setelah selesai, hapus file <code>setup.php</code> dari server (opsional — mode perbaikan mati sendiri begitu ada Super Admin).</p>
</div></body></html>
    <?php
    exit;
}

/* ---------- STATE ---------- */
$step   = $_POST['step'] ?? '';
$errors = [];
$notice = null;
$dbOk   = false;

/* ---------- AUTO-FIX APP_KEY ---------- */
if (! config('app.key') && is_writable($BASE.'/.env')) {
    env_set($BASE, ['APP_KEY' => 'base64:'.base64_encode(random_bytes(32))]);
    $notice = 'APP_KEY berhasil dibuat otomatis di file .env.';
}

/* =========================================================
 * STEP 2 — simpan & tes koneksi database
 * ======================================================= */
if ($step === 'database') {
    $driver   = ($_POST['driver'] ?? 'mysql') === 'sqlite' ? 'sqlite' : 'mysql';
    $host     = trim((string) ($_POST['host'] ?? ''));
    $port     = trim((string) ($_POST['port'] ?? '3306'));
    $database = trim((string) ($_POST['database'] ?? ''));
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $v = compact('driver', 'host', 'port', 'database', 'username');

    if ($driver === 'mysql') {
        if ($host === '')     $errors[] = 'DB host wajib diisi (di cPanel biasanya localhost).';
        if ($database === '') $errors[] = 'Nama database wajib diisi.';
        if ($username === '') $errors[] = 'Username database wajib diisi.';
    } elseif ($database === '') {
        $errors[] = 'Path file SQLite wajib diisi.';
    }

    if (! $errors) {
        if ($driver === 'mysql') {
            config([
                'database.default'                   => 'mysql',
                'database.connections.mysql.host'     => $host,
                'database.connections.mysql.port'     => $port !== '' ? (int) $port : 3306,
                'database.connections.mysql.database' => $database,
                'database.connections.mysql.username' => $username,
                'database.connections.mysql.password' => $password,
            ]);
        } else {
            if (! is_file($BASE.'/'.$database) && str_starts_with($database, 'database/')) {
                @touch($BASE.'/'.$database);
            }
            config([
                'database.default'                    => 'sqlite',
                'database.connections.sqlite.database' => $BASE.'/'.$database,
            ]);
        }
        DB::purge($driver);

        try {
            DB::connection($driver)->getPdo();
            DB::connection($driver)->statement('select 1');
        } catch (\Throwable $e) {
            $errors[] = 'Koneksi database gagal: '.$e->getMessage();
        }
    }

    if (! $errors) {
        if ($driver === 'mysql') {
            env_set($BASE, [
                'DB_CONNECTION' => 'mysql',
                'DB_HOST'       => $host,
                'DB_PORT'       => $port !== '' ? $port : '3306',
                'DB_DATABASE'   => $database,
                'DB_USERNAME'   => $username,
                'DB_PASSWORD'   => $password,
            ]);
        } else {
            env_set($BASE, [
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE'   => $database,
            ]);
        }
        clear_bootstrap_cache($BASE);
        try { Artisan::call('config:clear'); } catch (\Throwable) {}

        $dbOk   = true;
        $notice = '✅ Koneksi database berhasil — konfigurasi tersimpan di .env. Lanjut ke langkah 3.';
    }

    /* simpan nilai form utk refill */
    $form = $v ?? compact('driver', 'host', 'port', 'database', 'username');
}

/* =========================================================
 * STEP 3 — migrate + seed + storage:link
 * ======================================================= */
if ($step === 'install') {
    $log    = [];
    $failed = false;

    try {
        DB::connection()->getPdo();
    } catch (\Throwable $e) {
        $failed = true;
        $log['koneksi database'] = '❌ '.$e->getMessage()."\n\nPeriksa pengaturan DB di file .env, ulangi langkah 2.";
    }

    if (! $failed) {
        $commands = [
            'config:clear' => [],
            'migrate'      => ['--force' => true],
            'db:seed'      => ['--force' => true],
        ];
        foreach ($commands as $cmd => $params) {
            try {
                $exit = Artisan::call($cmd, $params);
                $out  = trim((string) Artisan::output());
                $log[$cmd] = ($exit === 0 ? '' : "⚠ exit code {$exit}\n").($out !== '' ? $out : '(tidak ada output)');
            } catch (\Throwable $e) {
                $failed = true;
                $log[$cmd] = '❌ '.$e->getMessage();
                break;
            }
        }
    }

    /* storage:link TIDAK fatal — cPanel sering mematikan symlink()/exec().
       Kalau symlink tak bisa dibuat, file tetap tersaji lewat route
       fallback /storage/{path} (StorageController). */
    if (! $failed) {
        [$slOk, $slMsg] = \App\Support\PublicStorage::link();
        $log['storage:link'] = ($slOk ? '✓ ' : '⚠ ').$slMsg;
    }

    if (! $failed) {
        try {
            Artisan::call('optimize:clear');
            $log['optimize:clear'] = trim((string) Artisan::output()) ?: '(tidak ada output)';
        } catch (\Throwable $e) {
            $log['optimize:clear'] = '⚠ '.($e->getMessage() ?: 'dilewati');
        }
    }

    if (! $failed) {
        if (! is_dir($BASE.'/storage/app')) { @mkdir($BASE.'/storage/app', 0775, true); }
        @file_put_contents($LOCK, date('Y-m-d H:i:s'));
    }

    /* ---- HALAMAN HASIL ---- */
    $creds = [[
        ['Super Admin', 'admin@alumnismun67halim2003.id', 'password'],
        ['Pengurus', 'pengurus@alumni67.id', 'password'],
        ['Ketua Angkatan', 'ketua2003@alumni67.id', 'password'],
        ['Alumni', 'andi@alumni67.id', 'password'],
    ], null][$failed ? 1 : 0];

    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>Instalasi · Alumni67 Connect</title>
<style>body{margin:0;background:#150A34;color:#F6F3EA;font-family:'Sora',system-ui,sans-serif;padding:2.5rem 1rem 4rem}
.w{max-width:52rem;margin:0 auto}.k{font-family:monospace;font-size:.68rem;letter-spacing:.18em;text-transform:uppercase;color:#01F501;margin-bottom:.3rem}
h1{font-size:1.6rem;margin:.8rem 0 .4rem}h1.ok{color:#01F501}h1.bad{color:#ff6b6b}.sub{color:#D2CBE8;font-size:.9rem}
.c{background:rgba(52,32,110,.6);border:1px solid rgba(1,245,1,.35);border-radius:.6rem;padding:1.25rem 1.4rem;margin:1.4rem 0}
pre{background:rgba(0,0,0,.45);border:1px solid rgba(220,212,236,.15);border-radius:.4rem;padding:.9rem 1rem;font-family:'Space Mono',monospace;font-size:.78rem;line-height:1.55;white-space:pre-wrap;word-break:break-word;color:#b8ffb8;margin:.6rem 0}
table{width:100%;border-collapse:collapse;font-size:.85rem}th{font-family:monospace;font-size:.68rem;text-transform:uppercase;color:#D2CBE8;padding:.45rem .5rem;border-bottom:1px solid rgba(220,212,236,.15)}
td{padding:.45rem .5rem;border-bottom:1px solid rgba(220,212,236,.08)}.btn{display:inline-flex;background:#01F501;color:#150A34;font-weight:700;padding:.7rem 1.4rem;border-radius:.4rem;text-decoration:none;border:0;font:inherit}.f{color:#D2CBE8;font-size:.75rem;margin-top:1.2rem}</style></head><body><div class="w">
<div class="k">Standalone Installer — setup.php</div>
<?php if ($failed): ?>
<h1 class="bad">❌ Instalasi gagal</h1><p class="sub">Perbaiki masalah di bawah lalu buka <b>/setup.php</b> ulang.</p>
<?php else: ?>
<h1 class="ok">✅ Instalasi selesai!</h1><p class="sub">Semua tabel dibuat &amp; data awal dimasukkan. Error <span style="font-family:monospace">sessions doesn't exist</span> teratasi.</p>
<?php endif; ?>
<div class="c">
<?php foreach ($log as $cmd => $out): ?>
<div style="font-family:monospace;font-size:.72rem;color:#D2CBE8;margin-top:.8rem">$ php artisan <?= h($cmd) ?></div>
<pre><?= h($out) ?></pre>
<?php endforeach; ?>
</div>
<?php if (! $failed): ?>
<div class="c"><table><tr><th>Role</th><th>Email</th><th>Password</th></tr>
<?php foreach ($creds as [$r, $e, $p]): ?><tr><td><?= h($r) ?></td><td style="font-family:monospace"><?= h($e) ?></td><td style="font-family:monospace"><?= h($p) ?></td></tr><?php endforeach; ?>
</table><p class="f">⚠ Segera ganti password setelah login pertama — terutama Super Admin.</p></div>
<a href="../" class="btn">Buka Website →</a>&nbsp;<a href="../login" class="btn" style="background:transparent;color:#F6F3EA;border:1px solid rgba(246,243,234,.3)">Login</a>
<?php endif; ?>
<p class="f">Installer terkunci otomatis. Untuk artisan lain (migrate, db:seed, cache:clear, dst.):<br>
login Super Admin → <b>Admin → Terminal</b> (menu <b>&gt;_ Terminal Artisan</b>).File setup.php boleh dihapus.</p>
</div></body></html>
    <?php
    exit;
}

/* =========================================================
 * HALAMAN UTAMA — requirement + form database
 * ======================================================= */
$conn   = config('database.default', 'mysql');
$db     = in_array($conn, ['mysql', 'sqlite'], true) ? $conn : 'mysql';
$checks = [
    ['PHP >= 8.2', version_compare(PHP_VERSION, '8.2.0', '>='), PHP_VERSION],
    ['Ekstensi openssl', extension_loaded('openssl'), extension_loaded('openssl') ? 'aktif' : 'nonaktif'],
    ['Ekstensi pdo', extension_loaded('pdo'), extension_loaded('pdo') ? 'aktif' : 'nonaktif'],
    ['Ekstensi pdo_mysql', extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'aktif' : 'wajib jika DB MySQL'],
    ['Ekstensi mbstring', extension_loaded('mbstring'), extension_loaded('mbstring') ? 'aktif' : 'nonaktif'],
    ['Ekstensi tokenizer', extension_loaded('tokenizer'), extension_loaded('tokenizer') ? 'aktif' : 'nonaktif'],
    ['Ekstensi xml', extension_loaded('xml'), extension_loaded('xml') ? 'aktif' : 'nonaktif'],
    ['Ekstensi ctype', extension_loaded('ctype'), extension_loaded('ctype') ? 'aktif' : 'nonaktif'],
    ['Ekstensi fileinfo', extension_loaded('fileinfo'), extension_loaded('fileinfo') ? 'aktif' : 'nonaktif'],
    ['storage/ writable', is_writable($BASE.'/storage'), is_writable($BASE.'/storage') ? 'OK' : 'chmod 775 storage -R'],
    ['bootstrap/cache writable', is_writable($BASE.'/bootstrap/cache'), is_writable($BASE.'/bootstrap/cache') ? 'OK' : 'chmod 775 bootstrap/cache -R'],
    ['File .env ada', is_file($BASE.'/.env'), is_file($BASE.'/.env') ? 'ada' : 'akan dibuat otomatis'],
    ['APP_KEY ter-set', (bool) config('app.key'), config('app.key') ? 'ada' : 'kosong'],
];

$val = fn (string $key, string $fallback = '') => h($form[$key] ?? $fallback);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>Installer · Alumni67 Connect</title>
<style>:root{--nd:#150A34;--nc:#34206E;--n:#01F501;--cd:#D2CBE8}
*{box-sizing:border-box}body{margin:0;background:var(--nd);color:#F6F3EA;font-family:'Sora',system-ui,sans-serif;padding:2.5rem 1rem 4rem}
.w{max-width:52rem;margin:0 auto}.m{font-family:'Space Mono',monospace}.k{font-size:.68rem;letter-spacing:.18em;text-transform:uppercase;color:var(--n);margin-bottom:.3rem}
h1{font-size:1.6rem;margin:.8rem 0 .2rem}.sub{color:var(--cd);font-size:.9rem;margin:0 0 1.8rem}
.c{background:rgba(52,32,110,.6);border:1px solid rgba(1,245,1,.35);border-radius:.6rem;padding:1.25rem 1.4rem;margin-bottom:1.25rem}
.c h2{font-size:.95rem;margin:0 0 1rem;display:flex;align-items:center;gap:.5rem}
.s{width:1.5rem;height:1.5rem;flex:none;border-radius:999px;display:grid;place-items:center;background:var(--n);color:var(--nd);font-family:'Space Mono',monospace;font-size:.75rem;font-weight:700}
table{width:100%;border-collapse:collapse;font-size:.85rem}th{text-align:left;font-family:'Space Mono',monospace;font-size:.68rem;text-transform:uppercase;letter-spacing:.1em;color:var(--cd);padding:.45rem .5rem;border-bottom:1px solid rgba(220,212,236,.15)}
td{padding:.45rem .5rem;border-bottom:1px solid rgba(220,212,236,.08)}.ok{color:var(--n)}.bad{color:#ff6b6b}
label{display:block;font-family:'Space Mono',monospace;font-size:.68rem;text-transform:uppercase;letter-spacing:.1em;color:var(--cd);margin:.9rem 0 .35rem}
input,select{width:100%;padding:.6rem .75rem;border-radius:.4rem;color:#F6F3EA;background:rgba(21,10,52,.7);border:1px solid rgba(220,212,236,.25);font:inherit}
input:focus,select:focus{outline:none;border-color:var(--n)}
.g{display:grid;grid-template-columns:1fr 1fr;gap:0 1rem}@media(max-width:640px){.g{grid-template-columns:1fr}}
.btn{display:inline-flex;border:0;cursor:pointer;background:var(--n);color:var(--nd);font:inherit;font-weight:700;padding:.7rem 1.4rem;border-radius:.4rem;text-decoration:none}
.btn:hover{filter:brightness(1.15)}.btn[disabled]{opacity:.35;cursor:not-allowed}
.aok{border:1px solid rgba(1,245,1,.35);background:rgba(1,245,1,.1);color:var(--n);border-radius:.4rem;padding:.75rem 1rem;font-size:.85rem;margin-bottom:1.25rem}
.aerr{border:1px solid rgba(255,107,107,.4);background:rgba(255,107,107,.1);color:#ffb3b3;border-radius:.4rem;padding:.75rem 1rem;font-size:.85rem;margin-bottom:1.25rem}
.aerr ul{margin:.3rem 0 0;padding-left:1.2rem}.fn{color:var(--cd);font-size:.75rem;margin-top:1.5rem}</style></head><body><div class="w">
<div style="display:flex;align-items:center;gap:.65rem">
<div style="width:2.6rem;height:2.6rem;border-radius:.55rem;display:grid;place-items:center;background:var(--nc);border:1px solid rgba(1,245,1,.35);color:var(--n);font-family:'Space Mono',monospace;font-weight:700">67</div>
<div><div class="m" style="font-size:.62rem;letter-spacing:.14em;text-transform:uppercase;color:var(--n)">SMUN 67 Halim</div><strong>Alumni67 Connect</strong></div></div>
<div class="k" style="margin-top:1.4rem">Standalone Installer — setup.php</div>
<h1>Pasang aplikasi tanpa terminal 🚀</h1>
<p class="sub">Installer mandiri untuk cPanel tanpa SSH — menjalankan <span class="m">migrate</span>, <span class="m">db:seed</span>, dan <span class="m">storage:link</span> langsung dari browser. Mengatasi error <span class="m">Table … sessions doesn't exist</span>.</p>

<?php if ($notice): ?><div class="aok"><?= h($notice) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="aerr"><strong>⚠ Gagal menyimpan konfigurasi:</strong><ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<div class="c"><h2><span class="s">1</span> Cek kebutuhan server</h2>
<table><tr><th>Pemeriksaan</th><th>Status</th><th>Keterangan</th></tr>
<?php $reqOk = true; foreach ($checks as [$label, $ok, $note]):
if (! $ok && ! in_array($label, ['Ekstensi pdo_mysql', 'APP_KEY ter-set'])) $reqOk = false; ?>
<tr><td><?= h($label) ?></td><td class="<?= $ok ? 'ok' : 'bad' ?>"><?= $ok ? '✓' : '✗' ?></td><td class="m" style="font-size:.72rem;color:var(--cd)"><?= h($note) ?></td></tr>
<?php endforeach; ?></table></div>

<div class="c"><h2><span class="s">2</span> Konfigurasi database</h2>
<p style="font-size:.85rem;color:var(--cd);margin:0 0 .4rem">Isi sesuai database MySQL yang sudah dibuat di cPanel (menu <em>MySQL® Databases</em>). Nilai tersimpan ke file <span class="m">.env</span>.</p>
<form method="POST" action="">
<input type="hidden" name="step" value="database">
<label for="driver">Driver</label>
<select id="driver" name="driver">
<option value="mysql" <?= ($form['driver'] ?? $db) === 'mysql' ? 'selected' : '' ?>>MySQL / MariaDB (cPanel)</option>
<option value="sqlite" <?= ($form['driver'] ?? $db) === 'sqlite' ? 'selected' : '' ?>>SQLite (lokal)</option>
</select>
<div class="g"><div><label for="host">DB Host</label><input id="host" name="host" value="<?= $val('host', (string) config("database.connections.{$db}.host", 'localhost')) ?>" placeholder="localhost"></div>
<div><label for="port">DB Port</label><input id="port" name="port" value="<?= $val('port', (string) (config("database.connections.{$db}.port") ?? 3306)) ?>" placeholder="3306"></div></div>
<label for="database">Nama Database <span style="text-transform:none">(mis. alux9774_alumni67-app)</span></label>
<input id="database" name="database" value="<?= $val('database', (string) config("database.connections.{$db}.database", 'database/database.sqlite')) ?>" placeholder="cpaneluser_alumni67">
<label for="username">DB Username</label>
<input id="username" name="username" value="<?= $val('username', (string) config("database.connections.{$db}.username", '')) ?>" placeholder="cpaneluser_admin">
<label for="password">DB Password</label>
<input id="password" name="password" type="password" value="" placeholder="••••••••">
<div style="margin-top:1.4rem"><button type="submit" class="btn">Simpan &amp; Tes Koneksi →</button></div>
</form></div>

<div class="c"><h2><span class="s">3</span> Migrasi &amp; data awal</h2>
<p style="font-size:.85rem;color:var(--cd);margin:0 0 .9rem">Jalankan <span class="m">php artisan migrate --seed</span> + <span class="m">storage:link</span>. Tombol aktif setelah koneksi database berhasil diuji di langkah 2.</p>
<form method="POST" action=""><input type="hidden" name="step" value="install">
<button type="submit" class="btn" <?= $dbOk ? '' : 'disabled title="Selesaikan langkah 2 dulu"' ?>>⚡ Jalankan Instalasi</button>
</form></div>

<p class="fn">Setelah instalasi sukses, installer terkunci otomatis (file <span class="m">storage/app/setup-installed.lock</span>). Untuk migrate/seed berikutnya: login Super Admin → <b>Admin → Terminal</b>.</p>
</div></body></html>
