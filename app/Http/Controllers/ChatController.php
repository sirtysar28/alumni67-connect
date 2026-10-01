<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Notifications\AlumniNotification;
use App\Support\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Chat pribadi antar alumni (widget 💬 melayang di pojok kanan bawah).
 * Hanya untuk user yang sudah login & akunnya disetujui.
 */
class ChatController extends Controller
{
    /** Pesan error standar bila tabel `messages` belum dibuat migrasi. */
    private const BELUM_MIGRASI = 'Fitur chat belum aktif — minta Super Admin menjalankan perintah migrate lewat menu Admin → Terminal.';

    /** Halaman chat (widget auto-terbuka — dipakai juga sebagai URL target notifikasi). */
    public function page(Request $request)
    {
        $peerId = (int) $request->query('with', 0);
        $peer = $peerId ? User::find($peerId) : null;

        // Pastikan peer valid & bukan diri sendiri
        if (! $peer || $peer->id === $request->user()->id || ! $peer->is_approved) {
            $peer = null;
        }

        return view('chat.index', ['peer' => $peer, 'ready' => Feature::chat()]);
    }

    /** Daftar kontak: semua alumni disetujui (kecuali sendiri) + pesan terakhir + belum-dibaca. */
    public function contacts(Request $request): JsonResponse
    {
        if (! Feature::chat()) {
            return response()->json(['ok' => false, 'message' => self::BELUM_MIGRASI, 'contacts' => []]);
        }

        $me = $request->user()->id;

        $contacts = User::query()
            ->where('id', '!=', $me)
            ->where('is_approved', true)
            ->with('profile:id,user_id,kelas')
            ->orderBy('name')
            ->get(['id', 'name', 'angkatan_id'])
            ->map(function (User $u) use ($me) {
                $last = Message::query()
                    ->where(fn ($q) => $q->where(fn ($w) => $w->where('from_id', $me)->where('to_id', $u->id))
                        ->orWhere(fn ($w) => $w->where('from_id', $u->id)->where('to_id', $me)))
                    ->latest('id')->first();

                return [
                    'id'          => $u->id,
                    'name'        => $u->name,
                    'kelas'       => $u->profile?->kelas,
                    'initial'     => mb_strtoupper(mb_substr($u->name, 0, 1)),
                    'last'        => $last?->body ? \Illuminate\Support\Str::limit($last->body, 42) : null,
                    'last_at'     => $last?->created_at?->translatedFormat('d M H:i'),
                    'unread'      => Message::where('from_id', $u->id)->where('to_id', $me)->whereNull('read_at')->count(),
                ];
            })
            // Kontak dengan chat / belum dibaca selalu di atas, sisanya alfabetis
            ->sortByDesc(fn ($c) => [$c['unread'] > 0 ? 1 : 0, $c['last'] !== null ? 1 : 0])
            ->values();

        return response()->json(['me' => $me, 'contacts' => $contacts]);
    }

    /** Percakapan dengan satu alumni + tandai pesan masuk sudah dibaca. */
    public function conversation(Request $request, User $peer): JsonResponse
    {
        if (! Feature::chat()) {
            return response()->json(['ok' => false, 'message' => self::BELUM_MIGRASI, 'messages' => []]);
        }

        $me = $request->user()->id;

        // Tandai dibaca pesan dari peer
        Message::where('from_id', $peer->id)->where('to_id', $me)->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $this->threadQuery($me, $peer->id)
            ->orderBy('id')
            ->limit(200)
            ->get()
            ->map(fn (Message $m) => $this->messageJson($m, $me));

        return response()->json([
            'peer' => [
                'id'      => $peer->id,
                'name'    => $peer->name,
                'kelas'   => $peer->profile?->kelas,
                'initial' => mb_strtoupper(mb_substr($peer->name, 0, 1)),
            ],
            'messages' => $messages,
        ]);
    }

    /** Kirim pesan (AJAX). Juga memicu notifikasi lonceng untuk penerima. */
    public function send(Request $request, User $peer): JsonResponse
    {
        if (! Feature::chat()) {
            return response()->json(['ok' => false, 'message' => self::BELUM_MIGRASI], 503);
        }

        if ($peer->id === $request->user()->id) {
            return response()->json(['ok' => false, 'message' => 'Tidak bisa chat dengan diri sendiri.'], 422);
        }

        $data = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $msg = Message::create([
            'from_id' => $request->user()->id,
            'to_id'   => $peer->id,
            'body'    => trim($data['body']),
        ]);

        // Notifikasi lonceng untuk penerima (best-effort)
        try {
            $peer->notifySafe(new AlumniNotification(
                title: '💬 Pesan baru dari '.$request->user()->name,
                message: \Illuminate\Support\Str::limit($msg->body, 80),
                url: '/chat?with='.$request->user()->id,
                icon: '💬',
            ));
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['ok' => true, 'message' => $this->messageJson($msg, $request->user()->id)]);
    }

    /** Poll pesan baru sejak ID tertentu (live-update ringan tanpa websocket). */
    public function poll(Request $request, User $peer): JsonResponse
    {
        if (! Feature::chat()) {
            return response()->json(['messages' => []]);
        }

        $me = $request->user()->id;
        $afterId = (int) $request->query('after', 0);

        // Tandai dibaca pesan masuk baru dari peer
        Message::where('from_id', $peer->id)->where('to_id', $me)->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $this->threadQuery($me, $peer->id)
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->get()
            ->map(fn (Message $m) => $this->messageJson($m, $me));

        return response()->json(['messages' => $messages]);
    }

    /* ---------- helper ---------- */

    private function threadQuery(int $me, int $peer)
    {
        return Message::query()
            ->where(function ($q) use ($me, $peer) {
                $q->where(function ($w) use ($me, $peer) {
                    $w->where('from_id', $me)->where('to_id', $peer);
                })->orWhere(function ($w) use ($me, $peer) {
                    $w->where('from_id', $peer)->where('to_id', $me);
                });
            });
    }

    private function messageJson(Message $m, int $me): array
    {
        return [
            'id'      => $m->id,
            'body'    => $m->body,
            'mine'    => $m->from_id === $me,
            'when'    => $m->created_at?->translatedFormat('H:i'),
            'date'    => $m->created_at?->translatedFormat('d M Y'),
            'read'    => $m->read_at !== null,
        ];
    }
}
