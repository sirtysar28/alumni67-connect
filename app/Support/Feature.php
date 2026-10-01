<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Cek ketersediaan tabel fitur — aman dipakai SEBELUM migrasi jalan
 * (mis. deploy ke cPanel yang belum menjalankan `migrate`).
 *
 * Semua fitur opsional (notifikasi 🔔, chat 💬) harus memanggil kelas ini
 * agar halaman tetap tampil walau tabelnya belum ada — super admin lalu
 * bisa membuka Admin → Terminal untuk menjalankan `migrate --force`.
 */
class Feature
{
    /** @var array<string, bool> cache per-request */
    private static array $cache = [];

    /** Apakah tabel sudah dibuat migrasi? (result di-cache per request) */
    public static function hasTable(string $table): bool
    {
        if (! isset(self::$cache[$table])) {
            try {
                self::$cache[$table] = Schema::hasTable($table);
            } catch (\Throwable) {
                // DB sama sekali belum tersambung/migrasi → anggap belum ada
                self::$cache[$table] = false;
            }
        }

        return self::$cache[$table];
    }

    /** Tabel notifikasi 🔔 siap? */
    public static function notifications(): bool
    {
        return self::hasTable('notifications');
    }

    /** Tabel chat 💬 siap? */
    public static function chat(): bool
    {
        return self::hasTable('messages');
    }
}
