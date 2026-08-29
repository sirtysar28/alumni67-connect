<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use Illuminate\Http\Request;

class BeritaController extends Controller
{
    public function index(Request $request)
    {
        $beritas = Berita::published()->with('user')
            ->when($request->filled('q'), fn ($q) => $q->where('judul', 'like', '%'.$request->string('q').'%'))
            ->orderByDesc('is_pinned')->latest('published_at')
            ->paginate(9)->withQueryString();

        return view('berita.index', compact('beritas'));
    }

    public function show(Berita $berita)
    {
        $berita->load('user');
        $lain = Berita::published()->where('id', '!=', $berita->id)->latest('published_at')->limit(4)->get();

        return view('berita.show', compact('berita', 'lain'));
    }
}
