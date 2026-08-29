<?php

namespace App\Http\Controllers;

use App\Models\AlumniProfile;
use App\Models\User;
use Illuminate\Http\Request;

class DirectoryController extends Controller
{
    public const BIDANG = ['IT', 'Desain', 'Pemerintahan', 'BUMN', 'Kesehatan', 'Bisnis', 'Pendidikan', 'Lainnya'];

    /** Pilihan kelas tetap — khusus angkatan 2003 & bestie 67 (IPA 1–4, IPS 1–5). */
    public const KELAS = ['IPA 1', 'IPA 2', 'IPA 3', 'IPA 4', 'IPS 1', 'IPS 2', 'IPS 3', 'IPS 4', 'IPS 5'];

    /** Direktori Alumni — bisa difilter kelas / profesi / kota (khusus angkatan 2003 & bestie 67). */
    public function index(Request $request)
    {
        $users = User::query()
            ->with(['profile', 'angkatan'])
            ->whereHas('profile')
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = '%'.$request->string('q').'%';
                $q->where(function ($w) use ($s) {
                    $w->where('name', 'like', $s)
                        ->orWhereHas('profile', fn ($p) => $p->where('perusahaan', 'like', $s)
                            ->orWhere('pekerjaan', 'like', $s)
                            ->orWhere('skill', 'like', $s));
                });
            })
            ->when($request->filled('kelas'), fn ($q) => $q->whereHas('profile', fn ($p) => $p->where('kelas', $request->kelas)))
            ->when($request->filled('bidang'), fn ($q) => $q->whereHas('profile', fn ($p) => $p->where('bidang', $request->bidang)))
            ->when($request->filled('kota'), fn ($q) => $q->whereHas('profile', fn ($p) => $p->where('kota', $request->kota)))
            ->orderBy('name')
            ->paginate(12)->withQueryString();

        $kotaList = AlumniProfile::whereNotNull('kota')->distinct()->orderBy('kota')->pluck('kota');

        return view('direktori.index', compact('users', 'kotaList'));
    }

    public function show(User $alumni)
    {
        $alumni->loadMissing(['profile', 'angkatan', 'posts' => fn ($q) => $q->latest()->limit(3)]);

        return view('direktori.show', ['alumni' => $alumni]);
    }
}
