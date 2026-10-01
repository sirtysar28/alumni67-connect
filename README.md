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
- ~~Notifikasi real-time~~ ✅ **SUDAH JADI** — ikon lonceng 🔔 di pojok kanan atas (lihat di bawah)
- ~~Chat pribadi~~ ✅ **SUDAH JADI** — widget 💬 melayang di pojok kanan bawah (lihat di bawah)
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

## 🚀 Deploy ke cPanel / Shared Hosting (TANPA SSH)

Karena hosting shared tidak punya terminal, aplikasi punya **installer web** dan **terminal artisan di panel admin**.

### 1. Upload & persiapan
1. Upload seluruh file project ke folder hosting, arahkan *document root* domain ke `public/`.
2. Buat database MySQL di menu **cPanel → MySQL® Databases** (catat nama DB, user, password).
3. Pastikan `.env` ada (salin dari `.env.example` bila belum). Isi minimal:
   ```env
   APP_URL=https://domain-anda.com
   SESSION_DRIVER=database
   ```

### 2. Jalankan installer
Buka **`https://domain-anda.com/setup.php`** di browser — file `public/setup.php` adalah installer **mandiri** yang tidak melewati routing Laravel (kebal 404 / route cache):

| Langkah | Isi |
|---|---|
| 1 | Cek otomatis kebutuhan server (PHP ≥ 8.2, ekstensi, folder writable, APP_KEY — dibuat otomatis bila kosong). Cache route/config lama di `bootstrap/cache/` juga dibersihkan otomatis |
| 2 | Isi koneksi database cPanel (host biasanya `localhost`) → **Simpan & Tes Koneksi** (tersimpan ke `.env`) |
| 3 | Klik **⚡ Jalankan Instalasi** → menjalankan `migrate --force`, `db:seed --force`, `storage:link`, `optimize:clear` |

> Alternatif: route **`/setup`** (wizard yang sama, lewat routing Laravel) juga tersedia.
> Installer terkunci otomatis setelah sukses (file `storage/app/setup-installed.lock`) — file `setup.php` boleh dihapus setelah selesai.
> Ini sekaligus mengatasi error `SQLSTATE[42S02] ... sessions doesn't exist` karena tabel dibuat oleh installer.



### 3. Terminal Artisan (khusus Super Admin)
Login sebagai Super Admin → **Admin → Terminal** (`/admin/terminal`) untuk menjalankan perintah artisan dari browser — tombol cepat berkelompok:

- 📚 **Database**: `migrate --force` · `migrate:status` · `db:seed --force`
- 📁 **File & Storage**: `storage:link` (symlink `public/storage`, otomatis fallback ke route `/storage/{path}` bila symlink dimatikan server)
- 🧹 **Cache**: `optimize:clear` · `cache:clear` · `config:clear`
- 🔧 **Sistem**: `route:list` · `setup:roles` (buat role standar + `--user=<id>` untuk promote super_admin)
- ☠️ `migrate:fresh --seed` (konfirmasi ekstra)

Hanya perintah **whitelist** yang diizinkan (aman dari injeksi), perintah destruktif selalu minta konfirmasi, dan halaman hanya bisa diakses role `super_admin`.

### 4. Lupa/perbaiki role Super Admin (menu Terminal tidak muncul?)
Jika roles belum ada di database (mis. seed belum jalan) sehingga tak ada super_admin:

1. Buka **`https://domain-anda.com/setup.php?repair`**
2. Pilih akun kamu → **🔑 Jadikan Super Admin** → roles standar dibuat otomatis
3. **Logout lalu login ulang** (role di-cache per sesi) → menu Terminal muncul

> Mode perbaikan otomatis **nonaktif** begitu sudah ada ≥1 super_admin (aman). Bisa juga lewat terminal: `setup:roles --user=5`.

## 👥 Approval Akun Baru (register → verifikasi admin)

Akun hasil register **tidak langsung aktif** — harus disetujui admin dulu:

1. User daftar → akun `is_approved = false`, **tidak bisa login** (diberi pesan menunggu persetujuan) + email konfirmasi "menunggu persetujuan" (bila SMTP aktif).
2. Admin buka **Admin → ⏳ Setujui Akun** (`/admin/user-pending`) → **✓ Setujui** (email selamat datang terkirim otomatis) atau **✗ Tolak** dengan alasan (email penolakan).
3. User yang disetujui login seperti biasa.

> Migration: `2026_09_21_..._add_is_approved_to_users_table` — kolom `is_approved` default `true` sehingga user lama tetap aktif; hanya register baru yang dikunci. Jalankan `migrate --force` lewat Terminal setelah upload.

## 🛂 Kelola Akun Alumni — khusus Super Admin

Menu **Admin → 🛂 Kelola Akun Alumni** (`/admin/alumni`) — hanya role `super_admin` yang bisa mengakses (role lain → 403):

| Kemampuan | Detail |
|---|---|
| **Daftar & cari** | Semua akun teregistrasi + pencarian (nama, email, kelas, pekerjaan, kota) + filter status & role, pagination 20/halaman |
| **✎ Edit akun** | Nama, email, angkatan, **reset password**, status approval (aktif/nonaktif), **ganti role** (super_admin/pengurus/ketua_angkatan/alumni), dan semua data profil (kelas, NIS, pekerjaan, kota, WA, dll.) |
| **🗑 Hapus permanen** | Akun + seluruh data terkait (profil, post feed, komentar, tiket event, lowongan) — FK cascade/nullify |

**Proteksi keamanan bawaan:**
- Tidak bisa menghapus **akun sendiri**
- Tidak bisa menghapus / mencabut role **Super Admin terakhir** (anti terkunci dari panel)
- Email harus unik; ganti email otomatis me-reset status verifikasi email
- Semua aksi hapus meminta konfirmasi di browser

> Route: `admin/users.index` · `admin/users.edit` · `admin/users.update` · `admin/users.destroy` — diproteksi middleware `role:super_admin`.

## 🔔 Notifikasi (ikon lonceng — pojok kanan atas)

Ikon **🔔 di samping tombol profil** (navbar) — **berlaku untuk semua user login** (semua role). Badge merah menampilkan jumlah belum dibaca, auto-refresh tiap 30 detik + saat tab aktif kembali.

| Pemicu notifikasi | Untuk siapa |
|---|---|
| ❤️ Postingan disukai | pemilik post |
| 💬 Postingan dikomentari | pemilik post |
| 🗣️ Diskusi forum dibalas | pembuat thread (anonimitas reply dijaga → “Seseorang”) |
| 🎉 Registrasi event sukses | peserta |
| ✅ Akun disetujui | pendaftar baru |
| ⭐ Badge terverifikasi / ✗ ditolak | pengaju verifikasi |
| 🤝 Donasi terverifikasi | donatur |
| 📰 Berita baru | **broadcast ke semua alumni** |
| 💬 Pesan chat baru | penerima chat |

**Fitur:** dropdown 6 terbaru (klik item → tandai dibaca + buka halaman terkait) · halaman lengkap `/notifikasi` · tombol *Tandai semua dibaca* · polling JSON `/notifikasi/poll`.

> Migration: `2026_10_01_..._create_notifications_table` (channel `database` bawaan Laravel). Jalankan `migrate --force` setelah deploy.

## 💬 Chat Antar Alumni (widget melayang — pojok kanan bawah)

Tombol **💬 melayang di pojok kanan bawah** semua halaman — khusus **user yang sudah login** (dan akunnya disetujui).

| Fitur | Detail |
|---|---|
| Daftar kontak | Semua alumni disetujui + pencarian nama/kelas + pesan terakhir + badge merah belum-dibaca |
| Percakapan | Bubble chat kiri/kanan, pemisah tanggal, **✓ terkirim · ✓✓ sudah dibaca** |
| Kirim pesan | Enter kirim · Shift+Enter baris baru · auto-grow textarea · maks 2000 karakter |
| Live update | Polling ringan tiap 5 detik (pesan baru) & 15 detik (kontak/unread) — tanpa websocket |
| Notifikasi 🔔 | Penerima otomatis dapat notifikasi lonceng; klik → langsung buka percakapan (`/chat?with={id}`) |
| Privasi | Pesan privat antar 2 orang; chat sendiri ditolak (422) |

> Migration: `2026_10_01_..._create_messages_table` — tabel `messages` (from/to/body/read_at).
> Widget & ikon lonceng memakai asset statis `public/css/widgets.css` + `public/js/widgets.js` (tanpa npm build).

## 🆙 Upgrade Aplikasi di cPanel (TANPA SSH & tanpa downtime error)

Semua fitur baru (kelola akun 🛂, notifikasi 🔔, chat 💬) **aman di-deploy sebelum migrasi dijalankan** — halaman tidak error walau tabelnya belum ada (mode aman): lonceng/chat menampilkan 0 & petunjuk, semua aksi seperti like/komentar/berita tetap jalan.

Langkah upgrade di hosting cPanel:

1. **Upload file kode baru** (app/, database/migrations/, public/css/widgets.css, public/js/widgets.js, resources/views/, routes/) — situs tetap jalan seperti biasa.
2. Login sebagai **Super Admin** → **Admin → 🖥️ Terminal Artisan** (`/admin/terminal`).
3. Klik tombol cepat **📚 Database → `migrate --force`** (atau jalankan perintah manualnya).
4. Selesai — ikon lonceng 🔔 & chat 💬 langsung aktif. Refresh halaman.

> Bila panel Terminal tidak muncul (role super_admin belum terpasang), gunakan `setup.php?repair` untuk mem-promote akun kamu, logout → login ulang.
> Alternatif: buka `https://domain/setup.php` — installer juga menjalankan `migrate --force` otomatis.

## 📧 SMTP & Email HTML

Konfigurasi SMTP lewat **Admin → 🎨 Pengaturan Situs** (mailer, host, port, username, password, encryption, from) — tersimpan di database & menimpa config runtime (tanpa edit `.env`). Ada tombol **Kirim Email Test**.

Email HTML bertema navy+neon sudah tersedia untuk: reset password, verifikasi email, pendaftaran menunggu persetujuan, akun disetujui, dan penolakan (lihat `resources/views/emails/`).

---

Dibuat untuk komunitas alumni SMUN 67 Halim · #SatuAngkatanSatuKompak 🤝
# alumni67-connect
