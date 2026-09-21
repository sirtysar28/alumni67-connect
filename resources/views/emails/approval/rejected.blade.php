@extends('emails.layout')

@section('preheader', 'Mohon maaf, pendaftaran akun kamu belum bisa disetujui.')

@section('content')
    <h2 style="font-family:'Sora','Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:22px;font-weight:700;color:#F6F3EA;margin:0 0 16px;">
        Pendaftaran belum disetujui <span style="color:#ff8f8f;">&#128533;</span>
    </h2>

    <p style="margin:0 0 14px;">Halo <strong style="color:#01F501;">{{ $user->name }}</strong>,</p>

    <p style="margin:0 0 14px;">
        Setelah ditinjau, mohon maaf pendaftaran akun <strong>Alumni67 Connect</strong> kamu
        <strong style="color:#ff8f8f;">belum bisa disetujui</strong> dengan alasan berikut:
    </p>

    <div style="margin:18px 0;padding:14px 16px;border:1px solid rgba(255,143,143,.4);border-radius:10px;background:rgba(255,143,143,.08);">
        <p style="margin:0;font-size:13px;line-height:1.8;color:#F6F3EA;">
            &#128172; {{ $alasan }}
        </p>
    </div>

    <p style="margin:18px 0 0;font-size:12px;line-height:1.8;color:#D2CBE8;">
        &#9997;&#127397; Yakin kamu alumni SMUN 67 Halim angkatan 2003? Hubungi pengurus
        komunitas untuk klarifikasi data — dengan senang hati kami bantu.
    </p>
@endsection
