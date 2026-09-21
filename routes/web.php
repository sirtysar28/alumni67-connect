<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\TerminalController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Alumni67 Connect — Super App Komunitas Alumni SMUN 67 Halim
|--------------------------------------------------------------------------
*/

/* ---------- PUBLIK (guest) ---------- */

/* Fallback penyajian file storage/app/public TANPA symlink — aktif otomatis
 * di shared hosting (cPanel) yang mematikan symlink()/exec() sehingga
 * `php artisan storage:link` gagal. Kalau symlink tersedia, web server
 * menyajikan file lebih dulu dan route ini tidak pernah terpakai. */
Route::get('/storage/{path}', [StorageController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.fallback');

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/berita', [BeritaController::class, 'index'])->name('berita.index');
Route::get('/berita/{berita:slug}', [BeritaController::class, 'show'])->name('berita.show');
Route::get('/direktori', [DirectoryController::class, 'index'])->name('direktori.index');
Route::get('/direktori/{alumni}', [DirectoryController::class, 'show'])->name('direktori.show')->middleware('auth');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show')->whereNumber('job');
Route::get('/donasi', [DonationController::class, 'index'])->name('donasi.index');
Route::get('/donasi/{campaign:slug}', [DonationController::class, 'show'])->name('donasi.show');
Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
Route::get('/forum/{thread}', [ForumController::class, 'show'])->name('forum.show')->whereNumber('thread')->middleware('auth');

/* ---------- LOGIN DULU ---------- */
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Feed sosial
    Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');
    Route::post('/feed', [FeedController::class, 'store'])->name('feed.store');
    Route::post('/feed/{post}/like', [FeedController::class, 'toggleLike'])->name('feed.like');
    Route::post('/feed/{post}/comment', [FeedController::class, 'storeComment'])->name('feed.comment');

    // Forum
    Route::get('/forum/buat', [ForumController::class, 'create'])->name('forum.create');
    Route::post('/forum', [ForumController::class, 'store'])->name('forum.store');
    Route::post('/forum/{thread}/reply', [ForumController::class, 'storeReply'])->name('forum.reply');

    // Event: registrasi + e-ticket
    Route::post('/events/{event}/register', [EventController::class, 'register'])->name('events.register');
    Route::get('/tiket-saya', [EventController::class, 'myTickets'])->name('tickets.index');
    Route::get('/tiket/{kode}', [EventController::class, 'showTicket'])->name('tickets.show');
    Route::get('/tiket/{kode}/pdf', [EventController::class, 'downloadTicketPdf'])->name('tickets.pdf');
    Route::get('/tiket-verify/{kode}', [EventController::class, 'verifyTicket'])->name('tickets.verify');

    // Bursa kerja: pasang lowongan
    Route::get('/jobs/buat', [JobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');

    // Donasi
    Route::post('/donasi/{campaign}/donate', [DonationController::class, 'donate'])->name('donasi.donate');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/verifikasi', [ProfileController::class, 'submitVerification'])->name('profile.verify');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');

    /* ---------- ADMIN (Super Admin / Pengurus) ---------- */
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');

        // Approval akun baru (hasil register — akun tidak aktif sebelum disetujui)
        Route::get('/user-pending', [AdminController::class, 'pendingUsers'])->name('users.pending');
        Route::post('/user/{user}/approve', [AdminController::class, 'approveUser'])->name('users.approve');
        Route::post('/user/{user}/reject', [AdminController::class, 'rejectUser'])->name('users.reject');

        Route::get('/verifikasi', [AdminController::class, 'verifications'])->name('verifications');
        Route::post('/verifikasi/{profile}/approve', [AdminController::class, 'approveProfile'])->name('verifications.approve');
        Route::post('/verifikasi/{profile}/reject', [AdminController::class, 'rejectProfile'])->name('verifications.reject');

        Route::get('/donasi', [AdminController::class, 'donations'])->name('donations');
        Route::post('/donasi/{trx}/verify', [AdminController::class, 'verifyDonation'])->name('donations.verify');
        Route::post('/donasi/{trx}/reject', [AdminController::class, 'rejectDonation'])->name('donations.reject');

        Route::get('/berita/buat', [AdminController::class, 'createBerita'])->name('berita.create');
        Route::post('/berita', [AdminController::class, 'storeBerita'])->name('berita.store');
        Route::get('/berita/{berita}/edit', [AdminController::class, 'editBerita'])->name('berita.edit');
        Route::put('/berita/{berita}', [AdminController::class, 'updateBerita'])->name('berita.update');
        Route::delete('/berita/{berita}', [AdminController::class, 'destroyBerita'])->name('berita.destroy');

        Route::get('/event/buat', [AdminController::class, 'createEvent'])->name('event.create');
        Route::post('/event', [AdminController::class, 'storeEvent'])->name('event.store');
        Route::get('/event/{event}/edit', [AdminController::class, 'editEvent'])->name('event.edit');
        Route::put('/event/{event}', [AdminController::class, 'updateEvent'])->name('event.update');
        Route::delete('/event/{event}', [AdminController::class, 'destroyEvent'])->name('event.destroy');

        Route::get('/event/{event}/checkin', [AdminController::class, 'checkinIndex'])->name('event.checkin');
        Route::post('/event/{event}/checkin', [AdminController::class, 'checkinStore'])->name('event.checkin.store');

        // Pengaturan SMTP email (persiapan notifikasi via email)
        Route::get('/pengaturan', [AdminController::class, 'settings'])->name('settings');
        Route::post('/pengaturan', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::post('/pengaturan/test-mail', [AdminController::class, 'testMail'])->name('settings.testmail');

        // Pengaturan tampilan (logo & tema) — HANYA Super Admin
        Route::post('/pengaturan/tampilan', [AdminController::class, 'updateAppearance'])->name('settings.appearance');

        // Terminal Artisan di browser (migrate, seed, cache, dll.) — HANYA Super Admin
        // berguna di cPanel/shared hosting yang tidak punya akses SSH.
        Route::get('/terminal', [TerminalController::class, 'index'])->name('terminal');
        Route::post('/terminal', [TerminalController::class, 'run'])->name('terminal.run');
    });
});

require __DIR__.'/auth.php';
