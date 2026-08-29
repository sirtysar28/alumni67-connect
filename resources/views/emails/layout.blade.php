{{--
    LAYOUT MASTER EMAIL — Alumni67 Connect
    HTML email klasik (table-based + inline CSS) agar aman di Gmail/Outlook/Yahoo.
    Header: logo reuni 67 + brand · Footer: identitas komunitas.
    Semua email (tes SMTP, reset password, verifikasi, dst.) extend layout ini.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="dark light">
    <meta name="supported-color-schemes" content="dark light">
    <title>@yield('subject', 'Alumni67 Connect')</title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->
    {{-- Font opsional — klien email yang mendukung akan pakai Sora/Inter, sisanya fallback sistem --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body style="margin:0;padding:0;background-color:#0D0624;-webkit-text-size-adjust:100%;">

    {{-- Preheader: teks cuplikan di daftar inbox --}}
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#0D0624;">
        @yield('preheader', 'Alumni67 Connect — Komunitas Alumni SMUN 67 Halim')&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#0D0624;">
        <tr>
            <td align="center" style="padding:28px 12px;">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:600px;">

                    {{-- ================= HEADER: LOGO ALUMNI 67 ================= --}}
                    <tr>
                        <td align="center" style="background-color:#150A34;border:1px solid rgba(1,245,1,.35);border-bottom:none;border-radius:14px 14px 0 0;padding:30px 32px 26px;">
                            @php
                                // Email bernuansa gelap → pakai logo varian dark mode (setting Super Admin)
                                $emailLogo = trim((string) \App\Models\Setting::get('site_logo_dark'))
                                    ?: trim((string) \App\Models\Setting::get('site_logo'))
                                    ?: asset('img/logo-reuni67.png');
                            @endphp
                            <img src="{{ $emailLogo }}" width="68" height="68" alt="Logo Alumni 67"
                                 style="width:68px;height:68px;display:block;margin:0 auto 14px;border-radius:16px;border:1px solid rgba(1,245,1,.4);">
                            <div style="font-family:'Sora','Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:24px;font-weight:800;color:#F6F3EA;letter-spacing:.02em;line-height:1.2;">
                                Alumni<span style="color:#01F501;">67</span> Connect
                            </div>
                            <div style="font-family:'Space Mono','Courier New',monospace;font-size:10px;font-weight:700;color:#01F501;letter-spacing:.24em;text-transform:uppercase;margin-top:8px;">
                                SMUN 67 Halim &middot; Angkatan 2003
                            </div>
                        </td>
                    </tr>

                    {{-- ================= KONTEN ================= --}}
                    <tr>
                        <td style="background-color:#241154;border-left:1px solid rgba(1,245,1,.35);border-right:1px solid rgba(1,245,1,.35);padding:34px 36px;">
                            <div style="font-family:'Inter','Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.75;color:#F6F3EA;">
                                @yield('content')
                            </div>
                        </td>
                    </tr>

                    {{-- ================= FOOTER ================= --}}
                    <tr>
                        <td align="center" style="background-color:#150A34;border:1px solid rgba(1,245,1,.35);border-top:2px solid rgba(1,245,1,.35);border-radius:0 0 14px 14px;padding:24px 32px;">
                            <div style="font-family:'Inter','Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:12px;line-height:1.8;color:#D2CBE8;">
                                <strong style="color:#F6F3EA;">Alumni67 Connect</strong> &mdash; Komunitas Alumni SMUN 67 Halim<br>
                                <span style="color:#01F501;font-weight:600;">#SatuAngkatanSatuKompak</span><br>
                                <a href="{{ config('app.url') }}" style="color:#D2CBE8;text-decoration:underline;">{{ preg_replace('#^https?://#', '', rtrim(config('app.url'), '/')) }}</a>
                            </div>
                            <div style="font-family:'Inter','Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:10px;color:#8F84B8;margin-top:12px;line-height:1.6;">
                                @yield('footnote', 'Email ini dikirim otomatis oleh sistem Alumni67 Connect — mohon tidak membalas email ini.')
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
