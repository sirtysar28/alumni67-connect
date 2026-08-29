<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

/**
 * QR → SVG "flat" khusus dompdf.
 *
 * Package simple-qrcode menghasilkan SVG ber-prolog <?xml?> + nested <g transform>
 * yang TIDAK dirender dompdf. Class ini men-generate SVG sederhana berisi <rect>
 * per baris modul (run-length merged): tanpa transform, tanpa namespace trick —
 * 100% didukung renderer SVG dompdf.
 */
class QrTicketSvg
{
    /**
     * @param  string $data   Isi QR (biasanya URL verifikasi tiket).
     * @param  int    $module Ukuran 1 modul QR dalam px.
     * @param  int    $margin Quiet zone (modul) — minimal 4 sesuai standar.
     * @param  string $dark   Warna modul gelap.
     * @param  string $light  Warna latar.
     */
    public static function generate(
        string $data,
        int $module = 5,
        int $margin = 4,
        string $dark = '#150A34',
        string $light = '#FFFFFF',
    ): string {
        $matrix = Encoder::encode(
            $data,
            ErrorCorrectionLevel::forBits(0), // 0 = level M
            'UTF-8'
        )->getMatrix();

        $w = $matrix->getWidth();
        $h = $matrix->getHeight();
        $size = ($w + $margin * 2) * $module;

        $rects = sprintf(
            '<rect x="0" y="0" width="%d" height="%d" fill="%s"/>',
            $size, $size, $light
        );

        // Modul gelap dirun-length per baris → jumlah rect jauh lebih sedikit.
        for ($y = 0; $y < $h; $y++) {
            $x = 0;
            while ($x < $w) {
                if ($matrix->get($x, $y) === 1) {
                    $start = $x;
                    while ($x < $w && $matrix->get($x, $y) === 1) {
                        $x++;
                    }
                    $rects .= sprintf(
                        '<rect x="%d" y="%d" width="%d" height="%d" fill="%s"/>',
                        ($start + $margin) * $module,
                        ($y + $margin) * $module,
                        ($x - $start) * $module,
                        $module,
                        $dark
                    );
                } else {
                    $x++;
                }
            }
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">%s</svg>',
            $size, $size, $size, $size, $rects
        );
    }
}
