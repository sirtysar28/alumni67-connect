# Alumni67 Connect 🎓

**Super App Komunitas Alumni SMUN 67 Halim** — dibangun dengan sepenuh hati sesuai dokumen konsep kebersamaan antar alumni.

> Bukan cuma tempat data alumni — tempat semua angkatan bisa **ngobrol, cari relasi, bantu teman, cari kerja, bikin acara, galang dana sosial, sampai kolaborasi bisnis.**

---

## 🚀 Cara Menjalankan

```bash
cd alumni67-app

# 1. Install dependency
composer install
# CSS & JS sudah jadi (statis) di public/css & public/js — TIDAK perlu npm build 👍
# (npm install && npm run build hanya kalau mau edit ulang resources/css/app.css)

# 2. Konfigurasi
cp .env.example .env        # (sudah tersedia .env siap pakai)
php artisan key:generate    # kalau belum ada APP_KEY

# 3. Database (default: SQLite — tanpa setup MySQL)
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link

# 4. Jalankan
php artisan serve
# → buka http://127.0.0.1:8000
```

### Ganti ke MySQL/PostgreSQL

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alumni67
DB_USERNAME=root
DB_PASSWORD=
```

Lalu `php artisan migrate --seed`.

---


Role mengacu dokumen konsep: **Super Admin · Pengurus Alumni · Ketua Angkatan · Alumni Member · Guest** (menggunakan [spatie/laravel-permission](https://spatie.be/docs/laravel-permission)).

---

## ✨ Fitur (sesuai konsep di dokumen)

### Phase 1 — ✅ Sudah jadi
| Fitur | Detail |
|---|---|
| **Dashboard Alumni** | Berita terbaru, agenda reuni, lowongan kerja, postingan terbaru, alumni ulang tahun bulan ini |
| **Direktori Alumni** | Pencarian + filter **angkatan / bidang profesi / kota**; kontak WA hanya terlihat member |
| **Bursa Kerja Alumni** | Lowongan fulltime/freelance/magang/remote, kategori IT·Desain·BUMN·Kesehatan·Bisnis, range gaji — alumni bisa pasang sendiri. #HiringAlumni |
| **Event & Reuni** | Registrasi → **e-ticket dengan QR code** → check-in oleh panitia via kode |
| **Berita/Pengumuman** | Publish + pin berita penting, dikelola pengurus |
| **Verified Badge** | Upload NIS + ijazah → approval admin → badge ✓ di direktori & feed |
| **Donasi & Sosial** | Campaign dengan **progress bar**, upload bukti transfer, verifikasi panitia, **laporan transparan** |
| **Social Feed** | Posting teks+foto, like ❤️, komentar |
| **Forum Aspirasi** | Diskusi + balasan dengan **mode anonim** 🎭, kategori aspirasi/ide/saran |
| **Profil Lengkap** | Pekerjaan, perusahaan, skill, kampus, kota, sosmed, **usaha/bisnis** (#BisnisAlumni67) |
| **Mobile-first UI** | Tema navy + neon identik situs reuni, **menu bawah mobile**: Home · Feed · Jobs · Event · Profile |

### Phase 2 — 🎯 Next (fondasi sudah siap)
- Notifikasi real-time, chat pribadi (tabel `messages` tinggal dibuat)
- Voting/polling di forum (dokumen: pilih lokasi reuni, voting kaos)
- Booking meja & doorprize event

### Phase 3 — 🔮 Rencana
- Mobile app (API Sanctum sudah terpasang)
- AI assistant (rekomendasi koneksi, rangkum aspirasi)
- Marketplace alumni & crowdfunding

---

## 📧 Pengaturan SMTP Email

Menu **Admin → Pengaturan SMTP** (`/admin/pengaturan`) — siap untuk notifikasi email (verifikasi akun, info event, donasi, dsb):

- Host, port, username, **password (disimpan terenkripsi di database)**, enkripsi TLS/SSL
- Alamat & nama pengirim (From)
- Driver: SMTP / Log (dev) / Sendmail / Mailgun / SES / Postmark
- **Tombol "Kirim Email Tes"** untuk memastikan koneksi jalan
- Nilai di database **menimpa konfigurasi `.env`** secara runtime — tanpa perlu edit file / restart

Contoh Gmail: `smtp.gmail.com` · port `587` · TLS · App Password.
Untuk hosting sendiri (cPanel): `mail.domain.com` · port `465` · SSL.

## 🗂️ Struktur Penting

```
app/
├── Http/Controllers/
│   ├── DashboardController.php    # Dashboard alumni
│   ├── LandingController.php      # Halaman depan guest
│   ├── DirectoryController.php    # Direktori alumni + filter
│   ├── BeritaController.php       # Berita & pengumuman
│   ├── EventController.php        # Event + registrasi + e-ticket QR
│   ├── JobController.php          # Bursa kerja
│   ├── FeedController.php         # Social feed (post/like/comment)
│   ├── ForumController.php        # Forum aspirasi (anonim)
│   ├── DonationController.php     # Donasi sosial
│   ├── Admin/AdminController.php  # Panel admin (verifikasi, CRUD, check-in, SMTP)
├── Models/Setting.php            # Key-value settings (SMTP tersimpan di sini)
└── Providers/AppServiceProvider.php # Override config mail dari DB saat runtime
├── Models/                        # 14 model (Angkatan, AlumniProfile, Event, dst)
database/migrations/               # users, alumni_profiles, angkatan, vacancies,
                                  # forum_threads, donation_*, dst — sesuai modul dokumen
resources/views/                   # Blade bertema navy+neon, responsif mobile
routes/web.php                     # Semua route
```

## 🧰 Tech Stack

- **Laravel 11.31** · **PHP ^8.2** · Breeze (Blade + Tailwind CSS v3)
- **spatie/laravel-permission v6** — role & permission
- **simple-qrcode** — QR e-ticket check-in
- **SQLite** (dev default) / MySQL / PostgreSQL
- Laravel Sanctum (siap untuk API/mobile app Phase 3)

---

Dibuat untuk komunitas alumni SMUN 67 Halim · #SatuAngkatanSatuKompak 🤝
# alumni67-connect
