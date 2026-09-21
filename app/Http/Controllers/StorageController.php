<?php

namespace App\Http\Controllers;

/**
 * Fallback penyajian file public storage TANPA symlink.
 *
 * Di shared hosting (cPanel) yang mematikan symlink()/exec(),
 * `php artisan storage:link` gagal — route /storage/{path} ini
 * menggantikannya: file dibaca dari storage/app/public lalu
 * di-stream lewat PHP dengan header yang benar.
 */
class StorageController extends Controller
{
    public function show(string $path)
    {
        $base = realpath(storage_path('app/public'));
        abort_unless($base !== false, 404);

        // realpath me-resolve "../" dsb → aman dari path traversal
        $full = realpath($base.DIRECTORY_SEPARATOR.$path);

        abort_unless(
            $full !== false
            && str_starts_with($full, $base.DIRECTORY_SEPARATOR)
            && is_file($full),
            404
        );

        return response()->file($full, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
