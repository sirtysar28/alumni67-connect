<?php

namespace App\Http\Controllers;

use App\Models\DonationCampaign;
use App\Models\DonationTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DonationController extends Controller
{
    /** Donasi & Sosial — campaign, progress bar, bukti transfer, laporan transparan. */
    public function index()
    {
        $campaigns = DonationCampaign::where('is_active', true)
            ->withCount(['transactions' => fn ($q) => $q->where('status', 'verified')])
            ->with('user')->latest()->paginate(6);

        return view('donasi.index', compact('campaigns'));
    }

    public function show(DonationCampaign $campaign)
    {
        $campaign->load('user');
        $transactions = $campaign->transactions()->where('status', 'verified')
            ->latest()->limit(20)->get();

        return view('donasi.show', compact('campaign', 'transactions'));
    }

    /** Simpan donasi (tamu boleh) + upload bukti transfer → menunggu verifikasi. */
    public function donate(Request $request, DonationCampaign $campaign)
    {
        $data = $request->validate([
            'amount'       => 'required|numeric|min:1000',
            'nama_donatur' => 'nullable|string|max:100',
            'pesan'        => 'nullable|string|max:300',
            'bukti'        => 'nullable|image|max:2048',
        ]);

        $trx = new DonationTransaction();
        $trx->donation_campaign_id = $campaign->id;
        $trx->user_id = auth()->id();
        $trx->amount = $data['amount'];
        $trx->nama_donatur = $data['nama_donatur'] ?? null;
        $trx->pesan = $data['pesan'] ?? null;

        if ($request->hasFile('bukti')) {
            $trx->bukti_path = $request->file('bukti')->store('donasi', 'public');
        }

        $trx->save();

        return back()->with('success', 'Terima kasih! Donasi kamu menunggu verifikasi pengurus.');
    }
}
