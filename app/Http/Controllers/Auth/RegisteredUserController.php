<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegisterPending;
use App\Models\Angkatan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /** Pilihan kelas tetap saat daftar — angkatan 2003 (IPA/IPS) + Bestie 67. */
    public const KELAS = ['IPA 1', 'IPA 2', 'IPA 3', 'IPA 4', 'IPS 1', 'IPS 2', 'IPS 3', 'IPS 4', 'IPS 5', 'Bestie 67'];

    /** Form daftar: cukup pilih kelas / Bestie 67. */
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'kelas'    => ['required', 'string', Rule::in(self::KELAS)],
        ]);

        // Angkatan 2003 di-set otomatis; Bestie 67 = teman dari luar angkatan
        $isBestie    = $request->kelas === 'Bestie 67';
        $angkatan2003 = Angkatan::firstOrCreate(
            ['tahun' => 2003],
            ['nama' => 'Angkatan 2003', 'slug' => 'angkatan-2003'],
        );

        $user = User::create([
            'name'        => $request->string('name')->trim(),
            'email'       => $request->string('email')->lower(),
            'password'    => Hash::make($request->password),
            'angkatan_id' => $isBestie ? null : $angkatan2003->id,
            // Approval admin: akun BARU tidak langsung aktif — disetujui lewat
            // menu Admin → Setujui Akun (kolom default-nya true untuk user lama).
            'is_approved' => false,
        ]);

        // Otomatis jadi alumni + profil awal dibuat.
        // Role dibuat ulang bila belum ada (self-healing — hindari 500 saat DB baru).
        $roleAlumni = \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'alumni', 'guard_name' => 'web']
        );
        $user->assignRole($roleAlumni);
        $user->profile()->create([
            'kelas'       => $request->kelas,
            'tahun_lulus' => $isBestie ? null : $angkatan2003->tahun,
        ]);

        // Email konfirmasi "menunggu persetujuan" — best-effort:
        // kalau SMTP belum terpasang, pendaftaran tetap sukses (email dicatat di log).
        try {
            Mail::to($user->email)->send(new RegisterPending($user));
        } catch (\Throwable) {
            // SMTP belum dikonfigurasi — biarkan admin menyetujui tanpa email.
        }

        // TIDAK auto-login: akun menunggu persetujuan admin dulu.
        return redirect()->route('login')->with('status',
            'Pendaftaran berhasil! 🎉 Akun kamu menunggu persetujuan admin — '.
            'kami kirim email begitu disetujui. Coba login lagi nanti ya.');
    }
}
