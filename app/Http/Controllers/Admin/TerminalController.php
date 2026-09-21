<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Artisan;

/**
 * Terminal Artisan di browser — KHUSUS Super Admin.
 *
 * Karena cPanel/shared hosting tidak punya SSH, halaman ini menjalankan
 * perintah artisan (migrate, db:seed, cache:clear, dst.) lewat Artisan::call().
 *
 * Keamanan:
 * - Hanya role super_admin (middleware).
 * - Hanya perintah dalam WHITELIST yang boleh dijalankan (tanpa eksekusi shell).
 * - Argumen hanya boleh flag sederhana (--force, --seed, dsb.), tanpa karakter aneh.
 * - `db:seed --class=...` dibatasi hanya seeder yang benar-benar ada di database/seeders.
 */
class TerminalController extends Controller implements HasMiddleware
{
    /** Perintah artisan yang diizinkan. */
    private const ALLOWED = [
        'info'            => 'about',
        'daftar'          => 'list',
        'environment'     => 'env',
        'migrate'         => 'migrate',
        'fresh'           => 'migrate:fresh',
        'refresh'         => 'migrate:refresh',
        'reset'           => 'migrate:reset',
        'rollback'        => 'migrate:rollback',
        'status'          => 'migrate:status',
        'install'         => 'migrate:install',
        'seed'            => 'db:seed',
        'db-show'         => 'db:show',
        'db-table'        => 'db:table',
        'cache-clear'     => 'cache:clear',
        'config-clear'    => 'config:clear',
        'route-clear'     => 'route:clear',
        'route-list'      => 'route:list',
        'view-clear'      => 'view:clear',
        'event-clear'     => 'event:clear',
        'optimize'        => 'optimize',
        'optimize-clear'  => 'optimize:clear',
        'storage-link'    => 'storage:link',
        'queue-restart'   => 'queue:restart',
        'queue-failed'    => 'queue:failed',
        'queue-retry'     => 'queue:retry',
        'queue-flush'     => 'queue:flush',
        'schedule-list'   => 'schedule:list',
        'schedule-run'    => 'schedule:run',
        'maintenance-on'  => 'down',
        'maintenance-off' => 'up',
        'clear-compiled'  => 'clear-compiled',
        'buat-roles'      => 'setup:roles',
    ];

    /** Perintah destruktif — butuh konfirmasi ekstra di UI. */
    private const DESTRUCTIVE = [
        'migrate:fresh', 'migrate:reset', 'migrate:refresh', 'migrate:rollback',
        'queue:flush', 'down',
    ];

    /** Perintah produksi yang otomatis ditambah --force agar tidak memblokir konfirmasi interaktif. */
    private const AUTO_FORCE = [
        'migrate', 'migrate:fresh', 'migrate:refresh', 'migrate:reset', 'migrate:rollback', 'db:seed',
    ];

    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('role:super_admin'),
        ];
    }

    /* ---------- Halaman terminal ---------- */
    public function index()
    {
        return view('admin.terminal', [
            'groups' => [
                ['📚 Database', [
                    ['migrate --force',      '▶ migrate',      false],
                    ['migrate:status',       'status migrasi', false],
                    ['db:seed --force',      'seed data',      false],
                ]],
                ['📁 File & Storage', [
                    ['storage:link',         'symlink storage → public', false],
                ]],
                ['🧹 Cache & Optimasi', [
                    ['optimize:clear',       'optimize:clear', false],
                    ['cache:clear',          'cache:clear',    false],
                    ['config:clear',         'config:clear',   false],
                ]],
                ['🔧 Sistem', [
                    ['route:list',           'route:list',     false],
                    ['setup:roles',          '🔑 buat roles standar', false],
                ]],
                ['☠️ Berbahaya (konfirmasi)', [
                    ['migrate:fresh --seed', '⚠ fresh + seed (hapus semua data)', true],
                ]],
            ],
            'commands'   => array_values(self::ALLOWED),
            'destructive'=> self::DESTRUCTIVE,
        ]);
    }

    /* ---------- Jalankan perintah ---------- */
    public function run(Request $request)
    {
        $raw = trim((string) $request->input('command', ''));

        // izinkan tempel "php artisan migrate --force" — buang prefix-nya
        $raw = trim((string) preg_replace('/^php\s+artisan\s+/i', '', $raw));

        if ($raw === '') {
            return back()->with('terminal_error', 'Perintah kosong. Contoh: migrate --force');
        }

        /* ---- parse token: nama perintah + flag ---- */
        $tokens = preg_split('/\s+/', $raw) ?: [];
        $name   = array_shift($tokens);

        $params = [];
        foreach ($tokens as $token) {
            if (! preg_match('/^[a-zA-Z0-9_\-.:=]+$/', $token)) {
                return back()->with('terminal_error', "Argumen tidak diizinkan: «{$token}». Hanya flag sederhana (--force, --seed, dst.).");
            }

            if (str_starts_with($token, '--')) {
                [$key, $value] = array_pad(explode('=', substr($token, 2), 2), 2, null);
                $params[$key] = $value ?? true;
            } elseif (str_starts_with($token, '-')) {
                $params[$token] = true;
            }
        }

        /* ---- whitelist perintah ---- */
        if (! in_array($name, self::ALLOWED, true)) {
            return back()->with('terminal_error', "Perintah «{$name}» tidak ada di whitelist.\nDiizinkan: ".implode(', ', array_values(self::ALLOWED)));
        }

        /* ---- whitelist seeder untuk db:seed --class=... ---- */
        if (($class = $params['class'] ?? null) && is_string($class) && ! $this->seederExists($class)) {
            return back()->with('terminal_error', "Seeder «{$class}» tidak ditemukan di database/seeders.");
        }

        /* ---- tambah --force otomatis untuk perintah migrasi/seed di produksi ---- */
        if (in_array($name, self::AUTO_FORCE, true)) {
            $params['force'] = true;
        }

        /* ---- storage:link ditangani khusus: cPanel mematikan symlink()/exec()
           sehingga artisan gagal — pakai helper + route fallback /storage/{path} ---- */
        if ($name === 'storage:link') {
            [$ok, $msg] = \App\Support\PublicStorage::link();

            return back()->with('terminal_output',
                "\$ php artisan storage:link\n\n".($ok ? '✓ ' : '⚠ ').$msg);
        }

        /* ---- setup:roles: buat roles standar + (opsional) promote user
           contoh: setup:roles --user=5 ---- */
        if ($name === 'setup:roles') {
            $lines = [];
            foreach (['super_admin', 'pengurus', 'ketua_angkatan', 'alumni'] as $r) {
                $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
                $lines[] = ($role->wasRecentlyCreated ? '+ role DIBUAT: ' : '· role ada: ').$r;
            }

            $uid = $params['user'] ?? null;
            if ($uid && ! is_bool($uid)) {
                $u = \App\Models\User::find((int) $uid);
                if ($u) {
                    $u->assignRole('super_admin');

                    // promote tanpa approve = akun tak bisa login — wajib sekalian aktifkan
                    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_approved')) {
                        $u->forceFill(['is_approved' => true, 'approval_note' => null])->save();
                    }
                    $u->refresh();
                    $lines[] = $u->hasRole('super_admin') && (! \Illuminate\Support\Facades\Schema::hasColumn('users', 'is_approved') || $u->is_approved)
                        ? "+ user #{$uid} ({$u->email}) = SUPER_ADMIN & AKTIF"
                        : "⚠ user #{$uid} gagal diaktifkan";
                } else {
                    $lines[] = "⚠ user #{$uid} tidak ditemukan";
                }
            }

            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            try { Artisan::call('cache:clear'); } catch (\Throwable) {}

            $lines[] = '· cache permission dibersihkan';

            return back()->with('terminal_output',
                "\$ php artisan {$raw}\n\n".implode("\n", $lines));
        }

        /* ---- eksekusi via Artisan (bukan shell → bebas injeksi shell) ---- */
        $started = microtime(true);
        try {
            $exit   = Artisan::call($name, $params);
            $output = rtrim((string) Artisan::output());
            $secs   = round(microtime(true) - $started, 2);
            $head   = $exit === 0 ? '✓' : '⚠ exit code '.$exit;

            $log = "\$ php artisan {$raw}\n\n".($output !== '' ? $output : '(tidak ada output)')."\n\n— {$head} · {$secs}s";
        } catch (\Throwable $e) {
            $log = "\$ php artisan {$raw}\n\n❌ ".$e->getMessage();
        }

        return back()->with('terminal_output', $log);
    }

    private function seederExists(string $class): bool
    {
        $files = glob(database_path('seeders/*.php')) ?: [];
        $names = array_map(fn (string $p) => basename($p, '.php'), $files);

        return in_array($class, $names, true);
    }
}
