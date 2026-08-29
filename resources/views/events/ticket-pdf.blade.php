{{--
    E-Ticket versi PDF (dompdf) — desain mengikuti template situs:
    navy-deep, kartu navy-card + border neon, font Sora/SpaceMono/Inter, logo "67".
    CATATAN: dompdf TIDAK merender <svg> inline — QR di-embed sebagai <img> data-URI.
    Warna hardcoded (dompdf tanpa CSS variable), font via @font-face TTF lokal.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tiket {{ $reg->kode_tiket }}</title>
    <style>
        @page { margin: 0; }
        @font-face { font-family: 'Sora';   src: url('{{ public_path('fonts/Sora-Bold.ttf') }}'); }
        @font-face { font-family: 'SoraX';  src: url('{{ public_path('fonts/Sora-ExtraBold.ttf') }}'); }
        @font-face { font-family: 'SpaceMono';  src: url('{{ public_path('fonts/SpaceMono-Regular.ttf') }}'); }
        @font-face { font-family: 'SpaceMonoB'; src: url('{{ public_path('fonts/SpaceMono-Bold.ttf') }}'); }
        @font-face { font-family: 'Inter';  src: url('{{ public_path('fonts/Inter-Regular.ttf') }}'); }

        body { margin: 0; background-color: #150A34; font-family: Inter, Helvetica, sans-serif; }
        .page { padding: 12px; }
        .card {
            background-color: #34206E;
            border: 2px solid #01F501;
            border-radius: 10px;
            padding: 14px 16px 12px;
            text-align: center;
            page-break-inside: avoid;
        }
        .logo67 { font-family: SoraX; font-size: 24px; color: #01F501; line-height: 1; }
        .brand { font-family: SpaceMono; font-size: 8px; letter-spacing: 3px; color: #D2CBE8; margin-top: 2px; }
        .brand span { color: #01F501; }
        .kicker { font-family: SpaceMono; font-size: 7.5px; letter-spacing: 3px; color: #01F501; margin-top: 6px; }
        .event-title { font-family: Sora; font-size: 15px; color: #F6F3EA; line-height: 1.2; margin: 2px 0 2px; }
        .event-meta { font-family: SpaceMono; font-size: 8px; color: #D2CBE8; }
        .dashed { border-top: 1px dashed #01F501; margin: 7px 0; }
        .qr-box {
            background-color: #FFFFFF;
            border: 1px solid #01F501;
            border-radius: 8px;
            padding: 5px;
            width: 140px;
            margin: 0 auto;
        }
        .qr-label { font-family: SpaceMono; font-size: 7.5px; letter-spacing: 3px; color: #D2CBE8; margin-top: 4px; }
        .code { font-family: SpaceMonoB; font-size: 15px; color: #01F501; letter-spacing: 4px; margin-top: 1px; }
        table.owner { width: 100%; border-collapse: collapse; }
        table.owner td { border: 1px solid #4A3A78; padding: 3px 8px; }
        td.lbl {
            font-family: SpaceMono; font-size: 7.5px; letter-spacing: 2px;
            color: #D2CBE8; background-color: #2A1860; width: 42%; text-align: left;
        }
        td.val { font-family: Sora; font-size: 10.5px; color: #F6F3EA; text-align: right; }
        .status {
            display: inline-block; border: 1px solid #01F501; color: #01F501;
            border-radius: 8px; font-family: SpaceMonoB; font-size: 8px;
            padding: 2px 10px; margin-top: 6px; letter-spacing: 2px;
        }
        .footer { font-size: 7.5px; color: #8D84B4; margin-top: 6px; line-height: 1.4; }
    </style>
</head>
<body>
    <div class="page">
        <div class="card">
            {{-- Logo & brand --}}
            <div class="logo67">67</div>
            <div class="brand">ALUMNI<span>67</span> CONNECT &bull; SMUN 67 HALIM</div>

            {{-- Event --}}
            <div class="kicker">E-TICKET &bull; TUNJUKKAN SAAT CHECK-IN</div>
            <div class="event-title">{{ $reg->event->judul }}</div>
            <div class="event-meta">
                {{ $reg->event->mulai->translatedFormat('D, d M Y · H:i') }} &bull; {{ $reg->event->lokasi }}
            </div>

            <div class="dashed"></div>

            {{-- QR (data-URI img — satu-satunya cara dompdf render SVG) --}}
            <div class="qr-box">
                <img src="data:image/svg+xml;base64,{{ base64_encode($qrSvg) }}" width="128" height="128">
            </div>
            <div class="qr-label">KODE TIKET</div>
            <div class="code">{{ $reg->kode_tiket }}</div>

            <div class="dashed"></div>

            {{-- Identitas pemilik --}}
            <table class="owner">
                <tr>
                    <td class="lbl">TIKET MILIK</td>
                    <td class="val">{{ $reg->user->name }}</td>
                </tr>
                <tr>
                    <td class="lbl">ANGKATAN / KELAS</td>
                    <td class="val">
                        {{ $reg->user->angkatan?->nama ?? '—' }}
                        @if ($reg->user->profile?->kelas) &bull; {{ $reg->user->profile->kelas }} @endif
                    </td>
                </tr>
            </table>

            <div class="status">
                {{ strtoupper($reg->status) }}@if($reg->checked_in_at) &bull; {{ $reg->checked_in_at->translatedFormat('d M H:i') }}@endif
            </div>

            <div class="footer">
                Tunjukkan QR code ini kepada panitia saat masuk acara.<br>
                Tiket bersifat personal — tidak untuk dipindahtangankan.
            </div>
        </div>
    </div>
</body>
</html>
