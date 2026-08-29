<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /** Event mendatang dulu, yang lalu di tab terpisah. */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'mendatang');

        $events = Event::where('status', 'publish')
            ->withCount('registrations')
            ->when($tab === 'mendatang', fn ($q) => $q->upcoming(), fn ($q) => $q->past())
            ->when($tab === 'mendatang', fn ($q) => $q->orderBy('mulai'), fn ($q) => $q->latest('mulai'))
            ->paginate(8)->withQueryString();

        return view('events.index', compact('events', 'tab'));
    }

    public function show(Event $event)
    {
        $event->loadCount('registrations');
        $myReg = auth()->user()
            ? $event->registrations()->where('user_id', auth()->id())->first()
            : null;

        return view('events.show', compact('event', 'myReg'));
    }

    /** Registrasi event → dapat e-ticket berisi QR code. */
    public function register(Request $request, Event $event)
    {
        $request->validateWithBag('register', []);

        if ($event->status !== 'publish' || $event->mulai->isPast()) {
            return back()->with('error', 'Pendaftaran event ini sudah ditutup.');
        }

        $existing = $event->registrations()->where('user_id', auth()->id())->first();
        if ($existing) {
            return redirect()->route('tickets.show', $existing);
        }

        if ($event->sisaKapasitas() !== null && $event->sisaKapasitas() <= 0) {
            return back()->with('error', 'Kapasitas penuh! Ikutan event lainnya ya.');
        }

        $reg = $event->registrations()->create([
            'user_id'    => auth()->id(),
            'kode_tiket' => 'R67-'.strtoupper(Str::random(6)),
        ]);

        return redirect()->route('tickets.show', $reg)
            ->with('success', 'Pendaftaran berhasil! Simpan e-ticket kamu.');
    }

    /** Daftar tiket milik user login. */
    public function myTickets()
    {
        $regs = \App\Models\EventRegistration::where('user_id', auth()->id())
            ->with('event')->latest()->get();

        return view('events.tickets', compact('regs'));
    }

    public function showTicket($kode)
    {
        $reg = \App\Models\EventRegistration::where('kode_tiket', $kode)
            ->where('user_id', auth()->id())->with('event')->firstOrFail();

        return view('events.ticket', compact('reg'));
    }

    /** Download e-ticket sebagai PDF (dompdf). */
    public function downloadTicketPdf($kode)
    {
        $reg = \App\Models\EventRegistration::where('kode_tiket', $kode)
            ->where('user_id', auth()->id())->with(['event', 'user.profile', 'user.angkatan'])->firstOrFail();

        // dompdf tidak merender <svg> inline → QR dibuat flat lalu di-embed sebagai <img> data-URI
        $qrSvg = \App\Support\QrTicketSvg::generate(route('tickets.verify', $reg->kode_tiket));

        $pdf = Pdf::loadView('events.ticket-pdf', compact('reg', 'qrSvg'))
            ->setPaper('a6', 'portrait');

        return $pdf->download('Tiket-'.$reg->kode_tiket.'.pdf');
    }

    /** Tujuan link di dalam QR e-ticket: nama acara + pemilik tiket + status.
     *  Bisa dibuka pemilik tiket sendiri ATAU panitia (admin/pengurus). */
    public function verifyTicket($kode)
    {
        $reg = \App\Models\EventRegistration::where('kode_tiket', $kode)
            ->with(['event', 'user.profile', 'user.angkatan'])->firstOrFail();

        abort_unless(
            auth()->user()->hasAnyRole(['super_admin', 'pengurus']) || $reg->user_id === auth()->id(),
            403,
            'Tiket ini bukan milik kamu.'
        );

        return view('events.verify', compact('reg'));
    }
}
