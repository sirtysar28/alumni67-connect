<?php

namespace App\Http\Controllers;

use App\Support\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Notifikasi pengguna (ikon lonceng 🔔 di navbar).
 * Berlaku untuk semua user login — like, komentar, balasan forum,
 * approval akun, verifikasi badge, donasi, event, berita, chat.
 *
 * AMAN SEBELUM MIGRASI: bila tabel `notifications` belum dibuat
 * (mis. deploy baru di cPanel), semua method menampilkan kondisi kosong
 * + petunjuk menjalankan `migrate --force` via Admin → Terminal —
 * bukan error, agar situs & terminal tetap bisa diakses.
 */
class NotificationController extends Controller
{
    /** Halaman lengkap semua notifikasi. */
    public function index(Request $request): View
    {
        $notifications = null;
        $ready = Feature::notifications();

        if ($ready) {
            try {
                $notifications = $request->user()->notifications()->latest()->paginate(20);
            } catch (\Throwable) {
                $ready = false;
            }
        }

        return view('notifications.index', compact('notifications', 'ready'));
    }

    /** Poll JSON: jumlah belum dibaca + 6 terbaru (untuk update live ikon lonceng). */
    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! Feature::notifications()) {
            return response()->json(['unread' => 0, 'latest' => [], 'ready' => false]);
        }

        try {
            return response()->json([
                'unread' => $user->unreadNotifications()->count(),
                'latest' => $user->notifications()->latest()->limit(6)->get()
                    ->map(fn ($n) => [
                        'id'      => $n->id,
                        'title'   => $n->data['title'] ?? 'Notifikasi',
                        'message' => $n->data['message'] ?? '',
                        'url'     => $n->data['url'] ?? '/dashboard',
                        'icon'    => $n->data['icon'] ?? '🔔',
                        'read'    => $n->read_at !== null,
                        'when'    => $n->created_at?->translatedFormat('d M H:i'),
                    ]),
                'ready' => true,
            ]);
        } catch (\Throwable) {
            return response()->json(['unread' => 0, 'latest' => [], 'ready' => false]);
        }
    }

    /** Tandai satu notifikasi dibaca → redirect ke URL tujuannya. */
    public function markRead(Request $request, string $id)
    {
        if (! Feature::notifications()) {
            return back()->with('error', 'Fitur notifikasi belum aktif — jalankan migrate lewat Admin → Terminal.');
        }

        $n = $request->user()->notifications()->where('id', $id)->first();

        if ($n) {
            $n->markAsRead();
            $url = $n->data['url'] ?? '/dashboard';

            return redirect($url);
        }

        return back()->with('error', 'Notifikasi tidak ditemukan.');
    }

    /** Tandai satu notifikasi dibaca via AJAX (tanpa redirect). */
    public function markReadAjax(Request $request, string $id): JsonResponse
    {
        $n = Feature::notifications()
            ? $request->user()->notifications()->where('id', $id)->first()
            : null;

        if ($n) {
            $n->markAsRead();
        }

        return response()->json(['ok' => true, 'unread' => $request->user()->unreadNotificationsCountSafe()]);
    }

    /** Tandai SEMUA notifikasi dibaca. */
    public function markAllRead(Request $request)
    {
        if (! Feature::notifications()) {
            return back()->with('error', 'Fitur notifikasi belum aktif — jalankan migrate lewat Admin → Terminal.');
        }

        try {
            $request->user()->unreadNotifications()->update(['read_at' => now()]);
        } catch (\Throwable) {
            return back()->with('error', 'Fitur notifikasi belum aktif — jalankan migrate lewat Admin → Terminal.');
        }

        return back()->with('success', 'Semua notifikasi ditandai dibaca ✓');
    }
}
