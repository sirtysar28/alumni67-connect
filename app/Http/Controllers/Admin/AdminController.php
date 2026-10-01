<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DirectoryController;
use App\Mail\AccountApproved;
use App\Mail\AccountRejected;
use App\Models\AlumniProfile;
use App\Models\Angkatan;
use App\Models\Berita;
use App\Models\DonationTransaction;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AlumniNotification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Panel Admin (Super Admin & Pengurus Alumni):
 * verifikasi badge alumni, verifikasi donasi, kelola berita & event,
 * check-in QR event. Kelola akun alumni (edit & hapus) khusus Super Admin.
 */
class AdminController extends Controller implements HasMiddleware
{
    /** Role standar komunitas (sesuai dokumen konsep). */
    private const ROLES = ['super_admin', 'pengurus', 'ketua_angkatan', 'alumni'];

    /** Middleware role: umum untuk Super Admin & Pengurus,
     *  tapi pengaturan tampilan (logo & tema) + kelola akun alumni
     *  (edit & hapus) HANYA Super Admin. */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('role:super_admin|pengurus', except: ['updateAppearance']),
            new Middleware('role:super_admin', only: ['updateAppearance']),
            new Middleware('role:super_admin', only: ['users', 'editUser', 'updateUser', 'destroyUser']),
        ];
    }

    public function index()
    {
        $pendingVerify = AlumniProfile::where('verification_status', 'pending')->with('user')->count();
        $pendingDonasi = DonationTransaction::where('status', 'pending')->count();
        $pendingAkun   = User::where('is_approved', false)->count();
        $events        = Event::upcoming()->count();
        $alumniTotal   = User::whereHas('profile')->count();
        $beritas       = Berita::published()->with('user')->latest('published_at')->limit(5)->get();

        return view('admin.index', compact('pendingVerify', 'pendingDonasi', 'pendingAkun', 'events', 'alumniTotal', 'beritas'));
    }

    /* ---------- APPROVAL AKUN BARU (hasil register) ---------- */
    public function pendingUsers()
    {
        $users = User::where('is_approved', false)
            ->with(['profile', 'angkatan'])
            ->latest('id')
            ->get();

        return view('admin.users-pending', compact('users'));
    }

    public function approveUser(Request $request, User $user)
    {
        $user->update(['is_approved' => true, 'approval_note' => null]);

        $user->notifySafe(new AlumniNotification(
            title: '✅ Akun kamu disetujui',
            message: 'Selamat bergabung di komunitas Alumni SMUN 67 Halim! Silakan login dan lengkapi profilmu.',
            url: '/dashboard',
            icon: '✅',
        ));

        $mailInfo = $this->sendSafely(new AccountApproved($user), $user);

        return back()->with('success', "Akun {$user->name} disetujui ✓ · {$mailInfo}");
    }

    public function rejectUser(Request $request, User $user)
    {
        $alasan = $request->string('alasan', 'Data pendaftaran belum sesuai — silakan hubungi pengurus komunitas.')->trim();

        $user->update(['is_approved' => false, 'approval_note' => $alasan]);

        $mailInfo = $this->sendSafely(new AccountRejected($user, $alasan), $user);

        return back()->with('success', "Pendaftaran {$user->name} ditolak · {$mailInfo}");
    }

    /** Kirim email best-effort — jangan gagalkan aksi admin walau SMTP bermasalah. */
    private function sendSafely(\Illuminate\Mail\Mailable $mailable, User $user): string
    {
        try {
            Mail::to($user->email)->send($mailable);

            return 'email notifikasi terkirim ✓';
        } catch (\Throwable $e) {
            report($e);

            return 'email GAGAL dikirim (cek pengaturan SMTP): '.\Illuminate\Support\Str::limit($e->getMessage(), 120);
        }
    }

    /* ---------- KELOLA AKUN ALUMNI (edit & hapus) — HANYA SUPER ADMIN ---------- */

    /** Daftar semua akun teregistrasi + pencarian & filter status/role. */
    public function users(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $role   = (string) $request->query('role', '');

        $users = User::with(['profile', 'angkatan', 'roles'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhereHas('profile', fn ($p) => $p
                            ->where('kelas', 'like', "%{$q}%")
                            ->orWhere('pekerjaan', 'like', "%{$q}%")
                            ->orWhere('kota', 'like', "%{$q}%"));
                });
            })
            ->when($status === 'approved', fn ($query) => $query->where('is_approved', true))
            ->when($status === 'pending', fn ($query) => $query->where('is_approved', false))
            ->when($role !== '' && in_array($role, self::ROLES), fn ($query) => $query->role($role))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', compact('users', 'q', 'status', 'role'));
    }

    /** Form edit akun alumni (data akun, role, status approval, profil). */
    public function editUser(User $user)
    {
        $user->loadMissing(['profile', 'angkatan', 'roles']);
        $user->profile()->firstOrCreate(['user_id' => $user->id]);
        $user->refresh()->load('profile');

        return view('admin.user-form', [
            'user'         => $user,
            'angkatanList' => Angkatan::orderBy('tahun')->get(),
            'bidangList'   => DirectoryController::BIDANG,
            'rolesList'    => self::ROLES,
        ]);
    }

    /** Simpan perubahan akun + profil + role (HANYA Super Admin). */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'angkatan_id' => ['nullable', 'exists:angkatan,id'],
            'is_approved' => ['nullable', 'boolean'],
            'password'    => ['nullable', 'string', 'min:8'], // kosong = tidak diubah
            'roles'       => ['nullable', 'array'],
            'roles.*'     => ['string', 'in:'.implode(',', self::ROLES)],
        ]);

        // Safety: jangan sampai super_admin mencabut role super_admin dari akun sendiri
        // (atau dari super_admin terakhir) → terkunci dari panel admin.
        $rolesBaru = array_values($request->input('roles', []));
        if ($user->hasRole('super_admin')
            && ! in_array('super_admin', $rolesBaru)
            && User::role('super_admin')->count() <= 1) {
            return back()->with('error', 'Tidak bisa mencabut role super_admin — ini satu-satunya Super Admin. Jadikan super_admin lain dulu.');
        }

        $user->fill([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'angkatan_id' => $validated['angkatan_id'] ?? null,
            'is_approved' => $request->boolean('is_approved'),
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null; // email berubah → wajib verifikasi ulang
        }

        if (! empty($validated['password'])) {
            $user->password = $validated['password']; // otomatis di-hash (cast)
        }

        $user->save();
        $user->syncRoles($rolesBaru ?: ['alumni']); // minimal role: alumni

        // Profil alumni
        $profileData = $request->validate([
            'nis'             => ['nullable', 'string', 'max:30'],
            'kelas'           => ['nullable', 'string', 'max:30'],
            'tahun_lulus'     => ['nullable', 'integer', 'min:1990', 'max:2030'],
            'tgl_lahir'       => ['nullable', 'date'],
            'no_wa'           => ['nullable', 'string', 'max:30'],
            'bio'             => ['nullable', 'string', 'max:1000'],
            'pekerjaan'       => ['nullable', 'string', 'max:100'],
            'perusahaan'      => ['nullable', 'string', 'max:150'],
            'bidang'          => ['nullable', 'string', 'max:40'],
            'kota'            => ['nullable', 'string', 'max:60'],
            'kampus'          => ['nullable', 'string', 'max:120'],
            'skill'           => ['nullable', 'string', 'max:300'],
            'instagram'       => ['nullable', 'string', 'max:100'],
            'linkedin'        => ['nullable', 'string', 'max:150'],
            'usaha_nama'      => ['nullable', 'string', 'max:100'],
            'usaha_deskripsi' => ['nullable', 'string', 'max:300'],
        ]);

        $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil diperbarui ✓");
    }

    /** Hapus permanen akun alumni + semua data terkait (HANYA Super Admin). */
    public function destroyUser(Request $request, User $user)
    {
        // Safety: tidak bisa hapus akun sendiri & super_admin terakhir
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kamu tidak bisa menghapus akun sendiri.');
        }

        if ($user->hasRole('super_admin') && User::role('super_admin')->count() <= 1) {
            return back()->with('error', 'Tidak bisa menghapus Super Admin terakhir.');
        }

        $nama = $user->name;
        $user->delete(); // profil, post, tiket ikut terhapus (FK cascade/nullify)

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$nama} beserta data terkait telah dihapus permanen 🗑️");
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

        if ($profile->user) {
            $profile->user->notifySafe(new AlumniNotification(
                title: '⭐ Badge terverifikasi!',
                message: 'Selamat! Profilmu kini berbadge ✓ Terverifikasi di direktori alumni.',
                url: '/direktori/'.$profile->user_id,
                icon: '⭐',
            ));
        }

        return back()->with('success', 'Alumni '.$profile->user->name.' terverifikasi ✓');
    }

    public function rejectProfile(Request $request, AlumniProfile $profile)
    {
        $catatan = $request->string('catatan', 'Data tidak sesuai.');

        $profile->update([
            'verification_status' => 'rejected',
            'catatan_verifikasi'  => $catatan,
        ]);

        if ($profile->user) {
            $profile->user->notifySafe(new AlumniNotification(
                title: '✗ Verifikasi badge ditolak',
                message: 'Catatan admin: '.$catatan,
                url: '/profile',
                icon: '✗',
            ));
        }

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

        if ($trx->user) {
            $trx->user->notifySafe(new AlumniNotification(
                title: '🤝 Donasi terverifikasi',
                message: 'Donasi Rp '.number_format($trx->amount, 0, ',', '.').' untuk «'.$trx->campaign?->judul.'» sudah diverifikasi panitia. Terima kasih!',
                url: '/donasi/'.$trx->campaign?->slug,
                icon: '🤝',
            ));
        }

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

        // 🔔 Broadcast ke semua alumni (berlaku untuk semua user)
        User::where('is_approved', true)->whereKeyNot(auth()->id())
            ->each(fn (User $u) => $u->notifySafe(new AlumniNotification(
                title: '📰 Berita baru: '.$berita->judul,
                message: \Illuminate\Support\Str::limit($berita->ringkasan ?? $berita->isi, 90),
                url: '/berita/'.$berita->slug,
                icon: '📰',
            )));

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
