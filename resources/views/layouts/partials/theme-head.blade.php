{{--
    Partial tema (dark/light) — include di <head> semua layout.
    - Class "light" di <html> di-set server-side dari setting `theme_mode`
      (default dark), lalu ditimpa preferensi localStorage pengunjung.
    - Dipasang sebelum CSS agar tidak ada flash warna.
    - Juga memasang handler untuk semua tombol [data-theme-toggle].
--}}
<script>
    try {
        var t = localStorage.getItem('theme');
        if (t === 'light' || t === 'dark') {
            document.documentElement.classList.toggle('light', t === 'light');
        }
    } catch (e) {}
</script>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        function sync(btn) {
            var isLight = document.documentElement.classList.contains('light');
            var icons = btn.querySelectorAll('svg');
            if (icons.length < 2) return;
            icons[0].style.display = isLight ? 'none' : 'block'; // matahari saat dark
            icons[1].style.display = isLight ? 'block' : 'none'; // bulan saat light
        }

        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            sync(btn);
            btn.addEventListener('click', function () {
                var isLight = document.documentElement.classList.toggle('light');
                try { localStorage.setItem('theme', isLight ? 'light' : 'dark'); } catch (e) {}
                document.querySelectorAll('[data-theme-toggle]').forEach(sync);
            });
        });
    });
</script>
