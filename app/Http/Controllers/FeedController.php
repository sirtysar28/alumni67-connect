<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Notifications\AlumniNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FeedController extends Controller
{
    /** Timeline / Social Feed ala mini Facebook. */
    public function index()
    {
        $posts = Post::with(['user.profile', 'likes', 'comments.user.profile'])
            ->withCount('likes', 'comments')
            ->latest()->paginate(10);

        return view('feed.index', compact('posts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'isi'    => 'required|string|max:2000',
            'gambar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('feed', 'public');
        }

        auth()->user()->posts()->create($data);

        return back()->with('success', 'Postingan terkirim!');
    }

    public function toggleLike(Post $post)
    {
        $like = $post->likes()->where('user_id', auth()->id())->first();
        if ($like) {
            $like->delete();
        } else {
            $post->likes()->create(['user_id' => auth()->id()]);

            // 🔔 Notifikasi untuk pemilik post (jangan notif diri sendiri)
            if ($post->user_id !== auth()->id() && $post->user) {
                $post->user->notifySafe(new AlumniNotification(
                    title: '❤️ Postinganmu disukai',
                    message: auth()->user()->name.' menyukai postinganmu di feed.',
                    url: '/feed',
                    icon: '❤️',
                ));
            }
        }

        return back();
    }

    public function storeComment(Request $request, Post $post)
    {
        $data = $request->validate(['isi' => 'required|string|max:1000']);

        $post->comments()->create($data + ['user_id' => auth()->id()]);

        // 🔔 Notifikasi untuk pemilik post (jangan notif diri sendiri)
        if ($post->user_id !== auth()->id() && $post->user) {
            $post->user->notifySafe(new AlumniNotification(
                title: '💬 Postinganmu dikomentari',
                message: auth()->user()->name.': '.\Illuminate\Support\Str::limit($data['isi'], 80),
                url: '/feed',
                icon: '💬',
            ));
        }

        return back()->with('success', 'Komentar terkirim!');
    }
}
