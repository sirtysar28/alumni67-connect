<x-app-layout>
    <div class="wrap max-w-3xl py-8">
        <a href="{{ route('events.show', $event) }}" class="font-mono text-xs text-neon hover:underline">← detail event</a>
        <h1 class="mt-2 font-display text-2xl text-cream">Check-in: {{ $event->judul }} 📱</h1>
        <p class="mt-1 text-sm text-creamDim">Scan/masukkan kode tiket peserta — contoh: R67-XXXXXX.</p>

        @if (session('success'))<div class="flash-ok mt-4">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="flash-err mt-4">{{ session('error') }}</div>@endif

        <form method="POST" action="{{ route('admin.event.checkin.store', $event) }}" class="card mt-6 flex gap-2">
            @csrf
            <input name="kode_tiket" class="input font-mono uppercase" placeholder="R67-XXXXXX" required>
            <button class="btn-neon shrink-0">Check-in</button>
        </form>

        {{-- ==== SCAN QR VIA KAMERA ==== --}}
        <div class="card mt-6">
            <div class="kicker !mb-0">📷 Scan QR via kamera</div>
            <p class="mt-2 text-xs text-creamDim">
                Arahkan kamera ke QR e-ticket peserta — check-in otomatis, bisa scan peserta berikutnya
                tanpa reload. Butuh izin kamera (HTTPS).
            </p>

            <div id="qr-reader" class="mx-auto mt-4 w-full max-w-xs overflow-hidden rounded border border-neonDim/40 bg-black/70"></div>

            <button type="button" id="btn-scan" class="btn-neon mt-3 w-full">📷 Mulai Scan</button>

            <div id="scan-result" class="mt-3 space-y-2"></div>
            <p id="scan-error" class="mt-3 hidden text-xs leading-relaxed" style="color:#fecaca"></p>
        </div>

        <div class="card mt-6 overflow-x-auto p-0">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-line/20">
                        <th class="th">Peserta</th><th class="th">Kode tiket</th><th class="th">Status</th><th class="th">Check-in</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($regs as $r)
                        <tr class="border-b border-line/10 {{ $r->status === 'hadir' ? 'bg-neon/5' : '' }}">
                            <td class="td">{{ $r->user->name }}<span class="ml-2 font-mono text-[10px] text-creamDim/60">{{ $r->user->profile->kelas }}</span></td>
                            <td class="td font-mono text-neon">{{ $r->kode_tiket }}</td>
                            <td class="td"><span class="chip-{{ $r->status === 'hadir' ? 'neon' : 'line' }}">{{ $r->status }}</span></td>
                            <td class="td font-mono text-[10px] text-creamDim">{{ $r->checked_in_at?->translatedFormat('d M H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td class="td text-creamDim" colspan="4">Belum ada pendaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==== JS SCANNER: html5-qrcode (lazy-load) + auto check-in via fetch ==== --}}
    <script>
        (function () {
            const btn = document.getElementById('btn-scan');
            const resultEl = document.getElementById('scan-result');
            const errorEl = document.getElementById('scan-error');
            const CHECKIN_URL = {{ json_encode(route('admin.event.checkin.store', $event)) }};
            const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

            let scanner = null, running = false, lastCode = null, lastAt = 0;

            function showError(msg) {
                errorEl.textContent = '⚠ ' + msg;
                errorEl.classList.remove('hidden');
            }
            function hideError() { errorEl.classList.add('hidden'); }

            function row(ok, html) {
                const div = document.createElement('div');
                div.className = ok ? 'flash-ok' : 'flash-err';
                div.innerHTML = html;
                resultEl.prepend(div);
                // Maksimal 5 hasil terakhir agar layar tetap rapi
                while (resultEl.children.length > 5) resultEl.lastChild.remove();
            }

            function loadLib(cb) {
                if (window.Html5Qrcode) return cb();
                const s = document.createElement('script');
                s.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
                s.onload = cb;
                s.onerror = () => showError('Gagal memuat library scanner — periksa koneksi internet, atau pakai input manual di atas.');
                document.head.appendChild(s);
            }

            async function onDecode(text) {
                // Abaikan scan ganda dari kode yang sama dalam 6 detik
                const now = Date.now();
                if (text === lastCode && now - lastAt < 6000) return;
                lastCode = text; lastAt = now;

                try {
                    const res = await fetch(CHECKIN_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                        },
                        body: JSON.stringify({ kode_tiket: text }),
                    });
                    const data = await res.json();
                    row(!!data.ok, data.message);
                    hideError();
                } catch (e) {
                    row(false, 'Gagal terhubung ke server — coba lagi.');
                }
            }

            async function stopScan() {
                if (scanner) { try { await scanner.stop(); scanner.clear(); } catch (e) {} }
                running = false;
                btn.textContent = '📷 Mulai Scan';
            }

            btn.addEventListener('click', () => {
                if (running) { stopScan(); return; }
                btn.disabled = true;
                btn.textContent = 'Memuat kamera…';
                loadLib(async () => {
                    try {
                        scanner = new Html5Qrcode('qr-reader');
                        await scanner.start(
                            { facingMode: 'environment' },
                            { fps: 10, qrbox: { width: 220, height: 220 } },
                            onDecode
                        );
                        running = true;
                        btn.disabled = false;
                        btn.textContent = '⏹ Stop Scan';
                    } catch (e) {
                        btn.disabled = false;
                        btn.textContent = '📷 Mulai Scan';
                        showError('Tidak bisa mengakses kamera (' + e + '). Pastikan izin kamera diberikan dan situs diakses via HTTPS — atau gunakan input manual.');
                    }
                });
            });
        })();
    </script>
</x-app-layout>
