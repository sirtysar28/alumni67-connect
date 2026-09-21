<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Installer web-based untuk shared hosting / cPanel (tanpa SSH/terminal).
 *
 * PENTING: rute /setup didaftarkan TANPA middleware `web` (lihat bootstrap/app.php),
 * sehingga tidak memakai session/CSRF — tetap bisa diakses walau tabel
 * `sessions` belum ada (error SQLSTATE[42S02] sebelum migrate).
 *
 * Setelah instalasi sukses, file lock dibuat di storage/app/setup-installed.lock
 * agar installer tidak bisa dijalankan ulang (hapus file itu untuk re-install).
 */
class SetupController extends Controller
{
    private const LOCK_FILE = 'app/setup-installed.lock';

    /* ================================================================
     *  STEP 1 — halaman utama: cek requirement + form database
     * ================================================================ */
    public function index()
    {
        if ($this->isInstalled()) {
            return view('setup.installed');
        }

        // Auto-fix: jika APP_KEY belum ada, coba generate & simpan ke .env
        $keyFixed = false;
        if (! config('app.key') && is_writable(base_path('.env'))) {
            $this->setEnvKeys(['APP_KEY' => 'base64:'.base64_encode(random_bytes(32))]);
            config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
            $keyFixed = true;
        }

        return view('setup.index', [
            'checks'   => $this->requirements($keyFixed),
            'db'       => $this->currentDbConfig(),
            'form'     => [], // NB: tanpa old() — session DB belum tersedia sebelum migrate
            'errors'   => [],
            'notice'   => $keyFixed ? 'APP_KEY berhasil dibuat otomatis di file .env' : null,
        ]);
    }

    /* ================================================================
     *  STEP 2 — simpan konfigurasi database ke .env + tes koneksi
     * ================================================================ */
    public function saveDatabase(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect()->route('setup');
        }

        // Validasi manual (TANPA $request->validate() — redirect error butuh session).
        $driver   = (string) $request->input('driver', 'mysql');
        $host     = trim((string) $request->input('host', ''));
        $port     = trim((string) $request->input('port', '3306'));
        $database = trim((string) $request->input('database', ''));
        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');

        $form = compact('driver', 'host', 'port', 'database', 'username');
        $errors = [];

        if (! in_array($driver, ['mysql', 'sqlite'], true)) {
            $errors[] = 'Driver database tidak dikenal.';
        }
        if ($driver === 'mysql') {
            if ($host === '')     $errors[] = 'DB host wajib diisi (di cPanel biasanya localhost).';
            if ($database === '') $errors[] = 'Nama database wajib diisi.';
            if ($username === '') $errors[] = 'Username database wajib diisi.';
        } else {
            if ($database === '') $errors[] = 'Path file SQLite wajib diisi.';
        }

        if ($errors) {
            return view('setup.index', [
                'checks' => $this->requirements(false),
                'db'     => $this->currentDbConfig(),
                'form'   => $form,
                'errors' => $errors,
                'notice' => null,
            ]);
        }

        // --- set konfigurasi runtime lalu tes koneksi (belum menulis .env) ---
        if ($driver === 'mysql') {
            config([
                'database.default'                => 'mysql',
                'database.connections.mysql.host'     => $host,
                'database.connections.mysql.port'     => $port !== '' ? (int) $port : 3306,
                'database.connections.mysql.database' => $database,
                'database.connections.mysql.username' => $username,
                'database.connections.mysql.password' => $password,
            ]);
        } else {
            if (! is_file(base_path($database)) && str_starts_with($database, 'database/')) {
                @touch(base_path($database)); // buat file sqlite jika belum ada
            }
            config([
                'database.default'                    => 'sqlite',
                'database.connections.sqlite.database' => base_path($database),
            ]);
        }

        DB::purge($driver);

        try {
            DB::connection($driver)->getPdo();
            DB::connection($driver)->statement('select 1');
        } catch (\Throwable $e) {
            return view('setup.index', [
                'checks' => $this->requirements(false),
                'db'     => $this->currentDbConfig(),
                'form'   => $form,
                'errors' => ['Koneksi database gagal: '.$e->getMessage()],
                'notice' => null,
            ]);
        }

        // --- koneksi OK → persist ke .env + bersihkan config cache ---
        if ($driver === 'mysql') {
            $this->setEnvKeys([
                'DB_CONNECTION' => 'mysql',
                'DB_HOST'       => $host,
                'DB_PORT'       => $port !== '' ? $port : '3306',
                'DB_DATABASE'   => $database,
                'DB_USERNAME'   => $username,
                'DB_PASSWORD'   => $password,
            ]);
        } else {
            $this->setEnvKeys([
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE'   => $database,
            ]);
        }

        try { Artisan::call('config:clear'); } catch (\Throwable) {}

        return view('setup.index', [
            'checks'  => $this->requirements(false),
            'db'      => $this->currentDbConfig(),
            'form'    => $form,
            'errors'  => [],
            'notice'  => '✅ Koneksi database berhasil — konfigurasi tersimpan di .env. Lanjut ke langkah 3.',
            'dbOk'    => true,
        ]);
    }

    /* ================================================================
     *  STEP 3 — jalankan migrate + seed + storage:link
     * ================================================================ */
    public function install()
    {
        if ($this->isInstalled()) {
            return redirect()->route('setup');
        }

        // Pastikan koneksi database hidup sebelum migrate
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            return view('setup.result', [
                'failed' => true,
                'log'    => ['koneksi database' => '❌ '.$e->getMessage()."\n\nPeriksa kembali pengaturan DB di file .env lalu ulangi langkah 2."],
                'credentials' => null,
            ]);
        }

        $log = [];
        $failed = false;

        // Perintah inti — gagal di sini = instalasi gagal
        $commands = [
            'config:clear' => [],
            'migrate'      => ['--force' => true],
            'db:seed'      => ['--force' => true],
        ];

        foreach ($commands as $cmd => $params) {
            try {
                $exit = Artisan::call($cmd, $params);
                $out  = trim(Artisan::output());
                $log[$cmd] = ($exit === 0 ? '' : "⚠ exit code {$exit}\n").($out !== '' ? $out : '(tidak ada output)');
            } catch (\Throwable $e) {
                $failed = true;
                $log[$cmd] = '❌ '.$e->getMessage();
                break;
            }
        }

        // storage:link TIDAK fatal — di cPanel symlink()/exec() sering dimatikan;
        // bila begitu, file tetap tersaji lewat route fallback /storage/{path}.
        if (! $failed) {
            [$slOk, $slMsg] = \App\Support\PublicStorage::link();
            $log['storage:link'] = ($slOk ? '✓ ' : '⚠ ').$slMsg;
        }

        if (! $failed) {
            try {
                Artisan::call('optimize:clear');
                $log['optimize:clear'] = trim(Artisan::output()) ?: '(tidak ada output)';
            } catch (\Throwable $e) {
                $log['optimize:clear'] = '⚠ '.($e->getMessage() ?: 'dilewati');
            }
        }

        if (! $failed) {
            if (! is_dir(storage_path('app'))) {
                @mkdir(storage_path('app'), 0775, true);
            }
            @file_put_contents(storage_path(self::LOCK_FILE), now()->toDateTimeString());
        }

        return view('setup.result', [
            'failed'      => $failed,
            'log'         => $log,
            'credentials' => $failed ? null : [
                ['Super Admin', 'admin@alumnismun67halim2003.id', 'password'],
                ['Pengurus', 'pengurus@alumni67.id', 'password'],
                ['Ketua Angkatan', 'ketua2003@alumni67.id', 'password'],
                ['Alumni', 'andi@alumni67.id', 'password'],
            ],
        ]);
    }

    /* ================================================================
     *  Helpers
     * ================================================================ */

    private function isInstalled(): bool
    {
        return is_file(storage_path(self::LOCK_FILE));
    }

    private function requirements(bool $keyFixed): array
    {
        $phpOk = version_compare(PHP_VERSION, '8.2.0', '>=');

        $checks = [
            ['PHP >= 8.2', $phpOk, PHP_VERSION],
            ['Ekstensi openssl', extension_loaded('openssl'), extension_loaded('openssl') ? 'aktif' : 'nonaktif'],
            ['Ekstensi pdo', extension_loaded('pdo'), extension_loaded('pdo') ? 'aktif' : 'nonaktif'],
            ['Ekstensi pdo_mysql', extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'aktif' : 'wajib jika DB MySQL'],
            ['Ekstensi mbstring', extension_loaded('mbstring'), extension_loaded('mbstring') ? 'aktif' : 'nonaktif'],
            ['Ekstensi tokenizer', extension_loaded('tokenizer'), extension_loaded('tokenizer') ? 'aktif' : 'nonaktif'],
            ['Ekstensi xml', extension_loaded('xml'), extension_loaded('xml') ? 'aktif' : 'nonaktif'],
            ['Ekstensi ctype', extension_loaded('ctype'), extension_loaded('ctype') ? 'aktif' : 'nonaktif'],
            ['Ekstensi fileinfo', extension_loaded('fileinfo'), extension_loaded('fileinfo') ? 'aktif' : 'nonaktif'],
            ['Folder storage/ writable', is_writable(storage_path()), is_writable(storage_path()) ? 'OK' : 'chmod 775 storage -R'],
            ['bootstrap/cache writable', is_writable(base_path('bootstrap/cache')), is_writable(base_path('bootstrap/cache')) ? 'OK' : 'chmod 775 bootstrap/cache -R'],
            ['File .env ada', is_file(base_path('.env')), is_file(base_path('.env')) ? 'ada' : 'salin dari .env.example'],
            ['APP_KEY ter-set', (bool) config('app.key'), $keyFixed ? 'baru dibuat otomatis' : ((bool) config('app.key') ? 'ada' : 'kosong')],
        ];

        return $checks;
    }

    private function currentDbConfig(): array
    {
        $conn = config('database.default', 'mysql');

        return [
            'driver'   => in_array($conn, ['mysql', 'sqlite'], true) ? $conn : 'mysql',
            'host'     => (string) config("database.connections.{$conn}.host", 'localhost'),
            'port'     => (string) (config("database.connections.{$conn}.port") ?? 3306),
            'database' => (string) config("database.connections.{$conn}.database", 'database/database.sqlite'),
            'username' => (string) config("database.connections.{$conn}.username", ''),
        ];
    }

    /**
     * Tulis pasangan key=value ke file .env.
     * Menangani key aktif, key yang masih dikomentari (# KEY=...), maupun key baru.
     */
    private function setEnvKeys(array $values): void
    {
        $path = base_path('.env');
        if (! is_file($path)) {
            $example = base_path('.env.example');
            if (is_file($example)) {
                copy($example, $path);
            } else {
                file_put_contents($path, '');
            }
        }

        $content = (string) file_get_contents($path);

        foreach ($values as $key => $value) {
            $line = $key.'='.$this->envValue((string) $value);
            $pattern = '/^'.preg_quote($key, '/').'\s*=.*$/m';

            if (preg_match($pattern, $content)) {
                // ganti baris aktif yang sudah ada
                $content = (string) preg_replace($pattern, $line, $content, 1);
            } elseif (preg_match('/^\s*#\s*'.preg_quote($key, '/').'\s*=.*$/m', $content)) {
                // aktifkan baris yang masih dikomentari
                $content = (string) preg_replace('/^(\s*)#\s*'.preg_quote($key, '/').'\s*=.*$/m', $line, $content, 1);
            } else {
                // tambah baris baru di akhir
                $content = rtrim($content)."\n\n".$line."\n";
            }
        }

        file_put_contents($path, $content);
    }

    private function envValue(string $value): string
    {
        if ($value === '' || preg_match('/^[A-Za-z0-9_.\-\/]+$/', $value)) {
            return $value;
        }

        return '"'.str_replace('"', '\\"', $value).'"';
    }
}
