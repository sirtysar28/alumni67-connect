<?php

namespace App\Http\Controllers;

use App\Models\Angkatan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /** Tampilkan form profil + data alumni. */
    public function edit(Request $request): View
    {
        $user = $request->user()->loadMissing(['profile', 'angkatan']);
        $user->profile()->firstOrCreate(['user_id' => $user->id]);
        $user->refresh()->load('profile');

        $angkatanList = Angkatan::orderBy('tahun')->get();
        $bidangList = DirectoryController::BIDANG;

        return view('profile.edit', [
            'user'         => $user,
            'angkatanList' => $angkatanList,
            'bidangList'   => $bidangList,
        ]);
    }

    /** Update info akun + profil alumni. */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'angkatan_id' => ['nullable', 'exists:angkatan,id'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

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
            'foto'            => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            $profileData['foto'] = $request->file('foto')->store('profil', 'public');
        } else {
            unset($profileData['foto']);
        }

        $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        return Redirect::route('profile.edit')->with('status', 'profil-updated');
    }

    /** Ajukan Verified Badge: upload ijazah → menunggu approval admin. */
    public function submitVerification(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis'   => ['required', 'string', 'max:30'],
            'ijazah' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $profile = $request->user()->profile()->firstOrCreate(['user_id' => $request->user()->id]);
        $path = $request->file('ijazah')->store('verifikasi', 'public');

        $profile->update([
            'nis'                 => $validated['nis'],
            'ijazah_path'         => $path,
            'verification_status' => 'pending',
            'catatan_verifikasi'  => null,
        ]);

        return Redirect::route('profile.edit')->with('status', 'verifikasi-diajukan');
    }

    /** Update password (bawaan Breeze). */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'min:8', 'confirmed'],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return back()->with('status', 'password-updated');
    }

    /** Hapus akun (bawaan Breeze). */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        auth()->logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
