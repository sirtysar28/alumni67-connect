<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Event;
use App\Models\Job;
use App\Models\Post;

class DashboardController extends Controller
{
    /** Dashboard Alumni: berita, agenda, lowongan, feed, ulang tahun. */
    public function index()
    {
        $beritas = Berita::published()->with('user')
            ->orderByDesc('is_pinned')->latest('published_at')->limit(3)->get();

        $events = Event::where('status', 'publish')->upcoming()->with('registrations')->limit(3)->get();

        $jobs = Job::active()->with('user')->latest()->limit(4)->get();

        $posts = Post::with(['user.profile', 'likes', 'comments.user'])->latest()->limit(5)->get();

        // Alumni yang ulang tahun bulan ini 🎂
        $bulanIni = now()->month;
        $birthday = \App\Models\User::whereHas('profile', fn ($q) => $q->whereMonth('tgl_lahir', $bulanIni))
            ->with('profile')->limit(5)->get();

        return view('dashboard', compact('beritas', 'events', 'jobs', 'posts', 'birthday'));
    }
}
