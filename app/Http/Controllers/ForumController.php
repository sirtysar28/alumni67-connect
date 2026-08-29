<?php

namespace App\Http\Controllers;

use App\Models\ForumThread;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public const KATEGORI = ['aspirasi', 'ide', 'saran', 'lainnya'];

    /** Forum Aspirasi & Diskusi — ada mode anonim. */
    public function index(Request $request)
    {
        $threads = ForumThread::withCount('replies')
            ->with(['user.profile', 'angkatan'])
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->kategori))
            ->orderByDesc('is_pinned')->latest()
            ->paginate(10)->withQueryString();

        return view('forum.index', compact('threads'));
    }

    public function create()
    {
        return view('forum.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'        => 'required|string|max:150',
            'isi'          => 'required|string|max:5000',
            'kategori'     => 'required|in:'.implode(',', self::KATEGORI),
            'is_anonymous' => 'nullable|boolean',
        ]);

        $thread = new ForumThread($data);
        $thread->user_id = auth()->id();
        $thread->angkatan_id = auth()->user()->angkatan_id;
        $thread->is_anonymous = $request->boolean('is_anonymous');
        $thread->save();

        return redirect()->route('forum.show', $thread)->with('success', 'Thread dibuat!');
    }

    public function show(ForumThread $thread)
    {
        $thread->load(['replies.user.profile', 'angkatan']);

        return view('forum.show', compact('thread'));
    }

    public function storeReply(Request $request, ForumThread $thread)
    {
        $data = $request->validate(['isi' => 'required|string|max:3000']);

        $thread->replies()->create($data + [
            'user_id'      => auth()->id(),
            'is_anonymous' => $request->boolean('is_anonymous'),
        ]);

        return back()->with('success', 'Balasan terkirim!');
    }
}
