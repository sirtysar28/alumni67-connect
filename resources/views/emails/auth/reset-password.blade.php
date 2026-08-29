@extends('emails.layout')

@section('preheader', 'Tautan untuk mengatur ulang password akun Alumni67 Connect kamu.')

@section('content')
    <h2 style="font-family:'Sora','Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:22px;font-weight:700;color:#F6F3EA;margin:0 0 16px;">
        Atur Ulang Password <span style="color:#01F501;">&#128273;</span>
    </h2>

    <p style="margin:0 0 14px;">Halo <strong style="color:#01F501;">{{ $user->name }}</strong>,</p>

    <p style="margin:0 0 14px;">
        Kami menerima permintaan untuk <strong>mengatur ulang password</strong> akun Alumni67 Connect kamu.
        Klik tombol di bawah untuk membuat password baru:
    </p>

    @include('emails.partials.button', ['url' => $url, 'label' => 'Reset Password Sekarang'])

    <p style="margin:22px 0 8px;font-size:12px;line-height:1.8;color:#D2CBE8;">
        Tombol tidak jalan? Salin &amp; tempel tautan ini di browser:<br>
        <a href="{{ $url }}" style="color:#01F501;word-break:break-all;">{{ $url }}</a>
    </p>

    <p style="margin:18px 0 0;font-size:12px;line-height:1.8;color:#D2CBE8;">
        &#9200; Tautan ini berlaku <strong>{{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} menit</strong> saja.<br>
        &#128274; Kalau kamu tidak meminta reset password, abaikan saja email ini — password kamu tidak akan berubah.
    </p>
@endsection
