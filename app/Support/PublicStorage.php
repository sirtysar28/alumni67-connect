<?php

namespace App\Support;

/**
 * Helper penyambungan public/storage.
 *
 * Shared hosting (cPanel) umumnya mematikan symlink() & exec() lewat
 * php.ini `disable_functions`, sehingga `php artisan storage:link`
 * gagal dengan error "Call to undefined function ... exec()".
 *
 * Kalau symlink tidak bisa dibuat, aplikasi otomatis memakai route
 * fallback /storage/{path} (lihat routes/web.php → StorageController)
 * yang menyajikan file storage/app/public langsung lewat PHP.
 */
class PublicStorage
{
    /** @return array{0: bool, 1: string} [sukses?, pesan] */
    public static function link(): array
    {
        $target = storage_path('app/public');
        $link   = public_path('storage');

        if (is_link($link) || is_dir($link)) {
            return [true, 'public/storage sudah tersedia (symlink/folder sudah ada).'];
        }

        if (! is_dir($target)) {
            @mkdir($target, 0775, true);
        }

        try {
            if (function_exists('symlink') && @symlink($target, $link)) {
                return [true, 'Symlink dibuat: public/storage → storage/app/public.'];
            }
        } catch (\Throwable) {
            // symlink() di-disable server → jatuh ke fallback di bawah
        }

        return [false,
            "Server ini mematikan fungsi symlink()/exec() — php artisan storage:link tidak bisa dijalankan.\n".
            'Tidak masalah: file dari storage/app/public tetap tersaji otomatis lewat route fallback /storage/{path}.'];
    }
}
