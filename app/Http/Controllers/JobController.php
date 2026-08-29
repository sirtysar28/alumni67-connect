<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public const KATEGORI = ['IT', 'Desain', 'Pemerintahan', 'BUMN', 'Kesehatan', 'Bisnis', 'Lainnya'];
    public const TIPE = ['fulltime', 'kontrak', 'freelance', 'magang', 'remote'];

    /** Bursa Kerja Alumni — "Hiring alumni prioritas alumni". */
    public function index(Request $request)
    {
        $jobs = Job::active()->with('user.profile')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $s = '%'.$request->string('q').'%';
                $w->where('judul', 'like', $s)->orWhere('perusahaan', 'like', $s);
            }))
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->kategori))
            ->when($request->filled('tipe'), fn ($q) => $q->where('tipe', $request->tipe))
            ->latest()->paginate(8)->withQueryString();

        return view('jobs.index', compact('jobs'));
    }

    public function show(Job $job)
    {
        $job->load('user.profile');

        return view('jobs.show', compact('job'));
    }

    public function create()
    {
        return view('jobs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'        => 'required|string|max:150',
            'perusahaan'   => 'nullable|string|max:150',
            'lokasi'       => 'nullable|string|max:100',
            'tipe'         => 'required|in:'.implode(',', self::TIPE),
            'kategori'     => 'required|in:'.implode(',', self::KATEGORI),
            'gaji_min'     => 'nullable|integer|min:0',
            'gaji_max'     => 'nullable|integer|min:0|gte:gaji_min',
            'deskripsi'    => 'required|string|max:5000',
            'cara_melamar' => 'nullable|string|max:2000',
        ]);

        $job = auth()->user()->jobsPosted()->create($data + ['is_active' => true]);

        return redirect()->route('jobs.show', $job)->with('success', 'Lowongan terbit! Semoga cepat dapat kandidat alumni.');
    }
}
