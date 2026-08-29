<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Event;

class LandingController extends Controller
{
    /** Halaman depan untuk guest (belum login). */
    public function index()
    {
        $beritas = Berita::published()->latest('published_at')->limit(3)->get();
        $events  = Event::where('status', 'publish')->upcoming()->limit(2)->get();

        return view('landing', compact('beritas', 'events'));
    }
}
