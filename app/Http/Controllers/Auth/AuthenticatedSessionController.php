<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        // Captcha matematika sederhana: simpan hash jawaban di session.
        $a = random_int(2, 9);
        $b = random_int(1, 9);

        session(['login_captcha' => hash('sha256', (string) ($a + $b))]);

        return view('auth.login', ['captchaA' => $a, 'captchaB' => $b]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        /* Approval admin: akun yang belum disetujui TIDAK boleh masuk.
         *
         * PENTING: hanya cek bila kolom `is_approved` BENAR-BENAR ada di tabel
         * (migration sudah dijalankan). Kalau belum migrate, atribut = null dan
         * tanpa pengecekan ini SEMUA user (termasuk Super Admin) akan terblokir
         * — dead-lock: tidak bisa login → tidak bisa buka Terminal → tidak bisa
         * migrate. Maka: kolom belum ada = anggap approved. */
        $user = auth()->user();
        $kolomAda = $user && array_key_exists('is_approved', $user->getAttributes());

        if ($kolomAda && ! (bool) $user->is_approved) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun kamu masih menunggu persetujuan admin. ⏳ '.
                          'Kami kirim email begitu disetujui — coba login lagi nanti ya.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->forget('login_captcha');

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
