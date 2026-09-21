<?php

use App\Http\Controllers\SetupController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            /* Installer web untuk cPanel tanpa SSH — TANPA middleware `web`
             * (tanpa session/CSRF) agar tetap bisa diakses walau tabel `sessions`
             * belum ada / belum di-migrate. Terkunci lewat lock file setelah selesai. */
            Route::get('/setup', [SetupController::class, 'index'])->name('setup');
            Route::post('/setup/database', [SetupController::class, 'saveDatabase'])->name('setup.database');
            Route::post('/setup/install', [SetupController::class, 'install'])->name('setup.install');
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
