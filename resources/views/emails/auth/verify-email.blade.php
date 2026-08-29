@extends('emails.layout')

@section('preheader', 'Konfirmasi alamat email kamu di komunitas Alumni67 Connect.')

@section('content')
    <h2 style="font-family:'Sora','Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:22px;font-weight:700;color:#F6F3EA;margin:0 0 16px;">
        Verifikasi Email <span style="color:#01F501;">&#9993;&#65039;</span>
    </h2>

    <p style="margin:0 0 14px;">Halo <strong style="color:#01F501;">{{ $user->name }}</strong>,</p>

    <p style="margin:0 0 14px;">
        Selamat bergabung di <strong>Alumni67 Connect</strong> — rumah digital alumni SMUN 67 Halim! &#127881;<br>
        Tinggal satu langkah lagi: konfirmasi alamat email ini milik kamu dengan klik tombol di bawah.
    </p>

    @include('emails.partials.button', ['url' => $url, 'label' => 'Verifikasi Email Sekarang'])

    <p style="margin:22px 0 8px;font-size:12px;line-height:1.8;color:#D2CBE8;">
        Tombol tidak jalan? Salin &amp; tempel tautan ini di browser:<br>
        <a href="{{ $url }}" style="color:#01F501;word-break:break-all;">{{ $url }}</a>
    </p>

    <p style="margin:18px 0 0;font-size:12px;line-height:1.8;color:#D2CBE8;">
        &#128274; Kalau kamu tidak merasa mendaftar, abaikan saja email ini — akun tidak akan aktif.
    </p>
@endsection
