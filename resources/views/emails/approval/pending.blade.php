@extends('emails.layout')

@section('preheader', 'Pendaftaran kamu sudah kami terima dan sedang menunggu persetujuan admin.')

@section('content')
    <h2 style="font-family:'Sora','Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:22px;font-weight:700;color:#F6F3EA;margin:0 0 16px;">
        Pendaftaran diterima <span style="color:#01F501;">&#9203;</span>
    </h2>

    <p style="margin:0 0 14px;">Halo <strong style="color:#01F501;">{{ $user->name }}</strong>,</p>

    <p style="margin:0 0 14px;">
        Terima kasih sudah mendaftar di <strong>Alumni67 Connect</strong> — komunitas alumni
        SMUN 67 Halim. Pendaftaran kamu sudah kami terima dan sedang
        <strong>menunggu persetujuan admin</strong> dulu ya.
    </p>

    <div style="margin:18px 0;padding:14px 16px;border:1px solid rgba(1,245,1,.35);border-radius:10px;background:rgba(1,245,1,.08);">
        <p style="margin:0;font-size:13px;line-height:1.8;color:#D2CBE8;">
            &#128230; <strong style="color:#F6F3EA;">Detail pendaftaran:</strong><br>
            Nama: {{ $user->name }}<br>
            Email: {{ $user->email }}<br>
            Kelas: {{ $user->profile?->kelas ?? '—' }}{{ $user->angkatan?->tahun ? ' · Angkatan '.$user->angkatan->tahun : '' }}
        </p>
    </div>

    <p style="margin:18px 0 0;font-size:12px;line-height:1.8;color:#D2CBE8;">
        &#8987; Biasanya peninjauan cepat kok. Begitu disetujui, kamu akan menerima email
        lagi dan langsung bisa login.<br>
        &#9997;&#127397; Verifikasi data alumni membantu menjaga komunitas tetap eksklusif
        untuk keluarga besar SMUN 67 Halim.
    </p>
@endsection
