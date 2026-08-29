<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use App\Models\Angkatan;
use App\Models\Berita;
use App\Models\DonationTransaction;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Panel Admin (Super Admin & Pengurus Alumni):
 * verifikasi badge alumni, verifikasi donasi, kelola berita & event,
 * check-in QR event.
 */
class AdminController extends Controller implements HasMiddleware
{
    /** Middleware role: umum untuk Super Admin & Pengurus,
     *  tapi pengaturan tampilan (logo & tema) HANYA Super Admin. */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('role:super_admin|pengurus', except: ['updateAppearance']),
            new Middleware('role:super_admin', only: ['updateAppearance']),
        ];
    }

    public function index()
    {
        $pendingVerify = AlumniProfile::where('verification_status', 'pending')->with('user')->count();
        $pendingDonasi = DonationTransaction::where('status', 'pending')->count();
        $events        = Event::upcoming()->count();
        $alumniTotal   = \App\Models\User::whereHas('profile')->count();
        $beritas       = Berita::published()->with('user')->latest('published_at')->limit(5)->get();

        return view('admin.index', compact('pendingVerify', 'pendingDonasi', 'events', 'alumniTotal', 'beritas'));
    }

    /* ---------- VERIFIKASI BADGE ALUMNI ---------- */
    public function verifications()
    {
        $profiles = AlumniProfile::where('verification_status', 'pending')->with(['user.angkatan'])->oldest()->get();

        return view('admin.verifications', compact('profiles'));
    }

    public function approveProfile(AlumniProfile $profile)
    {
        $profile->update([
            'verification_status' => 'approved',
            'verified_at'         => now(),
            'catatan_verifikasi'  => null,
        ]);

        return back()->with('success', 'Alumni '.$profile->user->name.' terverifikasi ✓');
    }

    public function rejectProfile(Request $request, AlumniProfile $profile)
    {
        $profile->update([
            'verification_status' => 'rejected',
            'catatan_verifikasi'  => $request->string('catatan', 'Data tidak sesuai.'),
        ]);

        return back()->with('success', 'Pengajuan verifikasi ditolak.');
    }

    /* ---------- VERIFIKASI DONASI ---------- */
    public function donations()
    {
        $trx = DonationTransaction::with(['campaign', 'user'])->where('status', 'pending')->oldest()->get();

        return view('admin.donations', compact('trx'));
    }

    public function verifyDonation(DonationTransaction $trx)
    {
        $trx->update(['status' => 'verified']);

        return back()->with('success', 'Donasi Rp '.number_format($trx->amount, 0, ',', '.').' diverifikasi ✓');
    }

    public function rejectDonation(DonationTransaction $trx)
    {
        $trx->update(['status' => 'rejected']);

        return back()->with('success', 'Donasi ditolak (bukti tidak valid).');
    }

    /* ---------- CRUD BERITA ---------- */
    public function createBerita()
    {
        return view('admin.berita-form', ['berita' => new Berita()]);
    }

    public function editBerita(Berita $berita)
    {
        return view('admin.berita-form', compact('berita'));
    }

    public function storeBerita(Request $request)
    {
        $data = $this->validateBerita($request);
        $berita = Berita::create($data + ['user_id' => auth()->id(), 'published_at' => now()]);

        return redirect()->route('berita.show', $berita)->with('success', 'Berita terbit!');
    }

    public function updateBerita(Request $request, Berita $berita)
    {
        $berita->update($this->validateBerita($request, $berita));

        return redirect()->route('berita.show', $berita)->with('success', 'Berita diperbarui.');
    }

    public function destroyBerita(Berita $berita)
    {
        $berita->delete();

        return redirect()->route('berita.index')->with('success', 'Berita dihapus.');
    }

    private function validateBerita(Request $request, ?Berita $berita = null): array
    {
        $data = $request->validate([
            'judul'     => 'required|string|max:200',
            'ringkasan' => 'nullable|string|max:300',
            'isi'       => 'required|string|max:20000',
            'is_pinned' => 'nullable|boolean',
            'gambar'    => 'nullable|image|max:2048',
        ]);

        $data['slug'] = Str::slug($data['judul']).($berita ? '-'.$berita->id : '-'.Str::random(4));
        $data['is_pinned'] = $request->boolean('is_pinned');

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('berita', 'public');
        } else {
            unset($data['gambar']);
        }

        return $data;
    }

    /* ---------- CRUD EVENT ---------- */
    public function createEvent()
    {
        return view('admin.event-form', ['event' => new Event()]);
    }

    public function editEvent(Event $event)
    {
        return view('admin.event-form', compact('event'));
    }

    public function storeEvent(Request $request)
    {
        $event = Event::create($this->validateEvent($request) + ['user_id' => auth()->id()]);

        return redirect()->route('events.show', $event)->with('success', 'Event dibuat!');
    }

    public function updateEvent(Request $request, Event $event)
    {
        $event->update($this->validateEvent($request));

        return redirect()->route('events.show', $event)->with('success', 'Event diperbarui.');
    }

    public function destroyEvent(Event $event)
    {
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event dihapus.');
    }

    private function validateEvent(Request $request): array
    {
        $data = $request->validate([
            'judul'       => 'required|string|max:200',
            'deskripsi'   => 'required|string|max:10000',
            'lokasi'      => 'required|string|max:200',
            'mulai'       => 'required|date',
            'selesai'     => 'nullable|date|after_or_equal:mulai',
            'kapasitas'   => 'nullable|integer|min:1',
            'harga_tiket' => 'nullable|numeric|min:0',
            'poster'      => 'nullable|image|max:2048',
            'status'      => 'required|in:draft,publish,selesai',
        ]);

        $data['slug'] = Str::slug($data['judul']).'-'.Str::random(4);
        $data['harga_tiket'] = $data['harga_tiket'] ?? 0;

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('events', 'public');
        } else {
            unset($data['poster']);
        }

        return $data;
    }

    /* ---------- PENGATURAN SMTP EMAIL ---------- */

    /** Form pengaturan SMTP — nilai terisi dari database / .env. */
    public function settings()
    {
        $values = [
            'mail_mailer'       => Setting::get('mail_mailer', config('mail.default')),
            'mail_host'         => Setting::get('mail_host', config('mail.mailers.smtp.host')),
            'mail_port'         => Setting::get('mail_port', config('mail.mailers.smtp.port')),
            'mail_username'     => Setting::get('mail_username', config('mail.mailers.smtp.username')),
            'mail_encryption'   => Setting::get('mail_encryption', config('mail.mailers.smtp.encryption')),
            'mail_from_address' => Setting::get('mail_from_address', config('mail.from.address')),
            'mail_from_name'    => Setting::get('mail_from_name', config('mail.from.name')),
            'mail_has_password' => Setting::get('mail_password') !== null,

            // Tampilan situs (logo dark/light & tema) — hanya diubah Super Admin
            'site_logo_dark'     => Setting::get('site_logo_dark') ?: Setting::get('site_logo'), // fallback setting lama
            'site_logo_light'    => Setting::get('site_logo_light'),
            'theme_mode'        => Setting::get('theme_mode', 'dark'),
        ];

        return view('admin.settings', $values);
    }

    /** Simpan pengaturan tampilan: logo varian dark-mode & light-mode + tema.
     *  HANYA Super Admin (lihat middleware()). */
    public function updateAppearance(Request $request)
    {
        $urlRule = function (string $attribute, mixed $value, \Closure $fail) {
            if ($value === null || trim((string) $value) === '') {
                return; // kosong = pakai default/badge
            }
            if (! filter_var(trim((string) $value), FILTER_VALIDATE_URL)) {
                $fail('Logo harus berupa link/URL gambar yang valid (cth: https://contoh.com/logo.png).');
            }
        };

        $data = $request->validate([
            'site_logo_dark'  => ['nullable', 'string', 'max:500', $urlRule],
            'site_logo_light' => ['nullable', 'string', 'max:500', $urlRule],
            'theme_mode'      => ['required', 'in:dark,light'],
        ]);

        Setting::set('site_logo_dark', trim((string) ($data['site_logo_dark'] ?? '')) ?: null);
        Setting::set('site_logo_light', trim((string) ($data['site_logo_light'] ?? '')) ?: null);
        Setting::set('theme_mode', $data['theme_mode']);

        return back()->with('success', 'Tampilan situs tersimpan ✓ Logo dark/light & tema langsung aktif di semua halaman.');
    }

    /** Simpan pengaturan SMTP ke database (password dienkripsi). */
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'mail_mailer'       => ['required', 'in:smtp,log,sendmail,mailgun,ses,postmark'],
            'mail_host'         => ['nullable', 'string', 'max:255'],
            'mail_port'         => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username'     => ['nullable', 'string', 'max:255'],
            'mail_password'     => ['nullable', 'string', 'max:255'],
            'mail_encryption'   => ['nullable', 'in:tls,ssl,none'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name'    => ['nullable', 'string', 'max:100'],
        ]);

        Setting::set('mail_mailer', $data['mail_mailer']);
        Setting::set('mail_host', $data['mail_host'] ?: null);
        Setting::set('mail_port', $data['mail_port'] ?: null);
        Setting::set('mail_username', $data['mail_username'] ?: null);
        Setting::set('mail_encryption', $data['mail_encryption'] ?: null);
        Setting::set('mail_from_address', $data['mail_from_address'] ?: null);
        Setting::set('mail_from_name', $data['mail_from_name'] ?: null);

        // Password: kosong = pertahankan yang lama
        if (! empty($data['mail_password'])) {
            Setting::setEncrypted('mail_password', $data['mail_password']);
        }

        return back()->with('success', 'Pengaturan SMTP tersimpan ✓ ');
    }

    /** Kirim email percobaan untuk memastikan SMTP aktif. */
    public function testMail(Request $request)
    {
        $data = $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        try {
            // Email percobaan dengan template HTML brand (header logo + footer)
            Mail::send('emails.test', [
                'to'     => $data['test_email'],
                'mailer' => config('mail.default'),
            ], function ($message) use ($data) {
                $message->to($data['test_email'])
                    ->subject('['.config('app.name').'] Tes Koneksi SMTP ✓');
            });

            $mailer = config('mail.default');

            return back()->with('success', "Email percobaan terkirim ke {$data['test_email']} via driver “{$mailer}” ✓ Cek inbox (dan folder spam).");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengirim: '.$e->getMessage());
        }
    }

    /* ---------- CHECK-IN EVENT (QR) ---------- */
    public function checkinIndex(Event $event)
    {
        $regs = $event->registrations()->with('user.profile')->oldest()->get();

        return view('admin.checkin', compact('event', 'regs'));
    }

    public function checkinStore(Request $request, Event $event)
    {
        $kode = (string) $request->string('kode_tiket')->trim();

        // QR e-ticket berisi URL verifikasi — ambil segmen terakhir sebagai kode tiket.
        // Input manual kode mentah (R67-XXXXXX) tetap didukung.
        if (str_contains($kode, '/')) {
            $path = parse_url($kode, PHP_URL_PATH) ?: $kode;
            $kode = basename($path);
        }
        $kode = strtoupper(trim($kode));

        $reg = $event->registrations()->where('kode_tiket', $kode)->with('user.profile')->first();

        if (! $reg) {
            $msg = "Kode \"{$kode}\" tidak ditemukan untuk event ini.";

            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => $msg], 404)
                : back()->with('error', $msg);
        }

        if ($reg->status === 'hadir') {
            $msg = $reg->user->name.' sudah check-in sebelumnya ('.$reg->checked_in_at?->translatedFormat('H:i').').';

            return $request->expectsJson()
                ? response()->json([
                    'ok' => false, 'message' => $msg, 'already' => true,
                    'peserta' => $reg->user->name, 'kode_tiket' => $reg->kode_tiket,
                ])
                : back()->with('error', $msg);
        }

        $reg->update(['status' => 'hadir', 'checked_in_at' => now()]);
        $msg = '✓ '.$reg->user->name.' check-in berhasil!';

        return $request->expectsJson()
            ? response()->json([
                'ok' => true, 'message' => $msg,
                'peserta' => $reg->user->name,
                'kelas' => $reg->user->profile?->kelas,
                'kode_tiket' => $reg->kode_tiket,
                'checked_in_at' => now()->translatedFormat('H:i'),
            ])
            : back()->with('success', $msg);
    }
}
