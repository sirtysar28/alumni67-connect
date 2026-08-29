@extends('emails.layout')

@section('preheader', 'Email percobaan — kalau email ini sampai, SMTP kamu sudah benar ✓')

@section('content')
    <h2 style="font-family:'Sora','Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:22px;font-weight:700;color:#F6F3EA;margin:0 0 16px;">
        Tes Koneksi SMTP <span style="color:#01F501;">&#10003;</span>
    </h2>

    <p style="margin:0 0 14px;">Halo!</p>

    <p style="margin:0 0 14px;">
        Ini <strong style="color:#01F501;">email percobaan</strong> dari <strong>Alumni67 Connect</strong>.
        Kalau email ini sampai ke inbox kamu, artinya konfigurasi SMTP sudah benar
        dan semua notifikasi email (reset password, verifikasi, info event, dan lainnya) siap dipakai. &#127881;
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:100%;margin:18px 0;background-color:rgba(1,245,1,.06);border:1px solid rgba(1,245,1,.25);border-radius:8px;">
        <tr>
            <td style="padding:14px 18px;font-family:'Inter','Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:13px;line-height:1.9;color:#D2CBE8;">
                <strong style="color:#F6F3EA;">Waktu kirim:</strong> {{ now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB<br>
                <strong style="color:#F6F3EA;">Dikirim ke:</strong> {{ $to }}<br>
                <strong style="color:#F6F3EA;">Mail driver:</strong> {{ strtoupper($mailer) }}
            </td>
        </tr>
    </table>

    <p style="margin:0;color:#D2CBE8;">
        Kalau email ini masuk folder spam, tandai sebagai &ldquo;bukan spam&rdquo; biar notifikasi berikutnya masuk inbox. &#128077;
    </p>

    @include('emails.partials.button', ['url' => config('app.url'), 'label' => 'Buka Alumni67 Connect'])
@endsection

@section('footnote', 'Email percobaan dikirim oleh Super Admin dari halaman Pengaturan SMTP.')
