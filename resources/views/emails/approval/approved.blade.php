@extends('emails.layout')

@section('preheader', 'Akun kamu sudah disetujui admin — langsung login dan sambung kembali dengan teman seangkatan!')

@section('content')
    <h2 style="font-family:'Sora','Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:22px;font-weight:700;color:#F6F3EA;margin:0 0 16px;">
        Akun disetujui <span style="color:#01F501;">&#127881;</span>
    </h2>

    <p style="margin:0 0 14px;">Halo <strong style="color:#01F501;">{{ $user->name }}</strong>,</p>

    <p style="margin:0 0 14px;">
        Kabar baik! Pendaftaran akun <strong>Alumni67 Connect</strong> kamu sudah
        <strong style="color:#01F501;">disetujui admin</strong>. Sekarang kamu bisa login
        dan langsung ikut seru-seruan bareng teman seangkatan:
    </p>

    @include('emails.partials.button', ['url' => route('login'), 'label' => 'Login Sekarang &#128274;'])

    <div style="margin:18px 0;padding:14px 16px;border:1px solid rgba(1,245,1,.35);border-radius:10px;background:rgba(1,245,1,.08);">
        <p style="margin:0;font-size:13px;line-height:1.8;color:#D2CBE8;">
            &#127919; <strong style="color:#F6F3EA;">Yang bisa kamu lakukan:</strong><br>
            &#8226; Lengkapi profil &amp; verifikasi badge alumni<br>
            &#8226; Jebol direktori alumni, feed, dan forum<br>
            &#8226; Ikut event reuni &amp; lihat bursa kerja #HiringAlumni
        </p>
    </div>

    <p style="margin:18px 0 0;font-size:12px;line-height:1.8;color:#D2CBE8;">
        &quot;Sekali teman 67, selamanya teman 67.&quot; &#129309;
    </p>
@endsection
