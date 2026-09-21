<?php

namespace Database\Seeders;

use App\Models\Angkatan;
use App\Models\AlumniProfile;
use App\Models\Berita;
use App\Models\DonationCampaign;
use App\Models\DonationTransaction;
use App\Models\Event;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Job;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Seeder utama Alumni67 Connect.
 *
 * Role sesuai dokumen konsep:
 * super_admin | pengurus | ketua_angkatan | alumni
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /* ---------- ROLES ---------- */
        foreach (['super_admin', 'pengurus', 'ketua_angkatan', 'alumni'] as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        /* ---------- ANGKATAN (fokus 2003, plus angkatan lain) ---------- */
        $angkatan2003 = Angkatan::firstOrCreate(
            ['tahun' => 2003],
            ['nama' => 'Angkatan 2003', 'slug' => 'angkatan-2003']
        );
        foreach ([2001, 2002, 2004, 2005] as $th) {
            Angkatan::firstOrCreate(
                ['tahun' => $th],
                ['nama' => "Angkatan $th", 'slug' => "angkatan-$th"]
            );
        }

        /* ---------- AKUN DEMO ---------- */
        // Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@alumnismun67halim2003.id'],
            ['name' => 'Admin Alumni67', 'password' => 'password', 'angkatan_id' => $angkatan2003->id, 'is_approved' => true]
        );
        $admin->assignRole('super_admin');

        // Pengurus
        $pengurus = User::firstOrCreate(
            ['email' => 'pengurus@alumni67.id'],
            ['name' => 'Rina Kartika', 'password' => 'password', 'angkatan_id' => $angkatan2003->id, 'is_approved' => true]
        );
        $pengurus->assignRole('pengurus');

        // Ketua angkatan 2003
        $ketua = User::firstOrCreate(
            ['email' => 'ketua2003@alumni67.id'],
            ['name' => 'Budi Santoso', 'password' => 'password', 'angkatan_id' => $angkatan2003->id, 'is_approved' => true]
        );
        $ketua->assignRole('ketua_angkatan');

        // Alumni member demo
        $demo = [
            ['Andi Wijaya', 'andi@alumni67.id', 'IPA 1', 'Software Engineer', 'Tokopedia', 'IT', 'Jakarta', 'UI/UX, IT'],
            ['Dewi Lestari', 'dewi@alumni67.id', 'IPA 2', 'Dokter Umum', 'RSUD Pasar Rebo', 'Kesehatan', 'Jakarta', 'Kesehatan'],
            ['Fajar Nugroho', 'fajar@alumni67.id', 'IPS 1', 'Analis Keuangan', 'Bank BUMN', 'BUMN', 'Bekasi', 'Keuangan, Excel'],
            ['Sari Puspita', 'sari@alumni67.id', 'IPS 3', 'Owner Katering', 'Sari Rasa Catering', 'Bisnis', 'Bandung', 'Kuliner, Marketing'],
            ['Agus Pratama', 'agus@alumni67.id', 'IPA 4', 'Freelance Designer', 'Studio Kreatif 67', 'Desain', 'Remote', 'Desain, Figma'],
        ];
        $alumniUsers = collect([$pengurus, $ketua]);
        foreach ($demo as [$nama, $email, $kelas, $kerja, $perusahaan, $bidang, $kota, $skill]) {
            $u = User::firstOrCreate(
                ['email' => $email],
                ['name' => $nama, 'password' => 'password', 'angkatan_id' => $angkatan2003->id, 'is_approved' => true]
            );
            $u->assignRole('alumni');
            $alumniUsers->push($u);

            AlumniProfile::firstOrCreate(
                ['user_id' => $u->id],
                [
                    'kelas' => $kelas, 'tahun_lulus' => 2003, 'nis' => (string) random_int(10000, 99999),
                    'pekerjaan' => $kerja, 'perusahaan' => $perusahaan, 'bidang' => $bidang,
                    'kota' => $kota, 'skill' => $skill, 'no_wa' => '0812' . random_int(10000000, 99999999),
                    'bio' => "Alumni SMUN 67 Halim — $kelas, lulus 2003.",
                    'verification_status' => 'approved', 'verified_at' => now(),
                ]
            );
        }

        // profil untuk admin/pengurus/ketua
        foreach ([$admin, $pengurus, $ketua] as $u) {
            AlumniProfile::firstOrCreate(
                ['user_id' => $u->id],
                [
                    'kelas' => 'IPA 1', 'tahun_lulus' => 2003,
                    'verification_status' => 'approved', 'verified_at' => now(),
                ]
            );
        }

        /* ---------- BERITA ---------- */
        $beritas = [
            ['Reuni Akbar 2003 Sukses Digelar di Artotel Senayan', 'Rekap lengkap reuni 23 Mei 2026 — 132 alumni hadir, 5 kelas terwakili.'],
            ['Grup WhatsApp Angkatan 2003 Kini Lebih Aktif', 'Info kumpul rutin & arisan alumni bergulir tiap bulan.'],
            ['Program Beasiswa Anak Alumni Dibuka', 'Pendaftaran beasiswa untuk anak alumni yang membutuhkan.'],
        ];
        foreach ($beritas as $i => [$judul, $ringkas]) {
            Berita::firstOrCreate(
                ['slug' => Str::slug($judul)],
                [
                    'user_id' => $pengurus->id, 'judul' => $judul, 'ringkasan' => $ringkas,
                    'isi' => $ringkas . "\n\n" . 'Sekilas info untuk seluruh alumni: dokumentasi lengkap acara dapat dilihat pada galeri website. Mari terus jaga kebersamaan angkatan 2003 — ikut serta dalam kegiatan alumni berikutnya dan ajak teman seangkatan yang belum tergabung.',
                    'is_pinned' => $i === 0, 'published_at' => now()->subDays(3 - $i),
                ]
            );
        }

        /* ---------- EVENTS ---------- */
        $reuni = Event::firstOrCreate(
            ['slug' => 'reuni-akbar-angkatan-2003'],
            [
                'user_id' => $pengurus->id, 'judul' => 'Reuni Akbar Angkatan 2003',
                'deskripsi' => "Kumpul akbar angkatan 2003 SMUN 67 Halim — sesi foto bersama, games nostalgia, door prize, dan makan siang bareng.",
                'lokasi' => 'Artotel Gelora Senayan, Jakarta',
                'mulai' => now()->setDate(2026, 5, 23)->setTime(10, 0),
                'selesai' => now()->setDate(2026, 5, 23)->setTime(14, 0),
                'kapasitas' => 200, 'harga_tiket' => 0, 'status' => 'selesai',
            ]
        );
        $hangout = Event::firstOrCreate(
            ['slug' => 'arisan-alumni-q3-2026'],
            [
                'user_id' => $pengurus->id, 'judul' => 'Arisan & Ngabuburit Alumni 2003',
                'deskripsi' => "Kumpul santai alumni angkatan 2003: update kabar, diskusi rencana kegiatan sosial, dan voting tema reuni berikutnya.",
                'lokasi' => 'Kopi Kenangan Halim, Jakarta Timur',
                'mulai' => now()->addDays(21)->setTime(16, 0),
                'selesai' => now()->addDays(21)->setTime(19, 0),
                'kapasitas' => 60, 'harga_tiket' => 50000, 'status' => 'publish',
            ]
        );

        /* ---------- JOBS ---------- */
        $jobs = [
            ['Senior Backend Engineer', 'Tokopedia', 'Jakarta', 'fulltime', 'IT', 25000000, 40000000, 'Membangun layanan microservice skala besar. Stack: PHP/Go, MySQL, Redis.'],
            ['UI/UX Designer (Remote)', 'Studio Kreatif 67', 'Remote', 'remote', 'Desain', 8000000, 15000000, 'Merancang antarmuka aplikasi web & mobile untuk klien UMKM.'],
            ['Staff Akuntansi BUMN', 'PT Angkasa Pura', 'Tangerang', 'fulltime', 'BUMN', 9000000, 12000000, 'Menangani jurnal, rekonsiliasi, dan pelaporan keuangan bulanan.'],
            ['Project Freelance Copywriting', 'Komunitas Alumni 67', 'Remote', 'freelance', 'Bisnis', 3000000, 6000000, 'Menulis konten promosi kegiatan alumni & UMKM teman seangkatan.'],
        ];
        foreach ($jobs as [$j, $p, $l, $t, $k, $gmin, $gmax, $d]) {
            Job::firstOrCreate(
                ['judul' => $j, 'perusahaan' => $p],
                [
                    'user_id' => $alumniUsers->random()->id, 'lokasi' => $l, 'tipe' => $t, 'kategori' => $k,
                    'gaji_min' => $gmin, 'gaji_max' => $gmax,
                    'deskripsi' => $d, 'cara_melamar' => 'Kirim CV ke email penerima kerja (lihat detail). Prioritas untuk alumni SMUN 67 Halim!',
                ]
            );
        }

        /* ---------- FEED ---------- */
        $posts = [
            ['Alhamdulillah dapat rezeki baru — mulai bulan ini pindah divisi di kantor. Wish me luck! 🙏', $alumniUsers[3]],
            ['Jualan katering hari raya dibuka ya teman-teman! Menu paket keluarga, bisa COD area Bandung-Jakarta. #BisnisAlumni67', $alumniUsers[6]],
            ['Setelah 23 tahun akhirnya ketemu lagi sama teman IPA 1 kemarin. Kangen banget momen kayak gini. ❤️', $alumniUsers[2]],
        ];
        foreach ($posts as [$isi, $u]) {
            Post::firstOrCreate(['isi' => $isi, 'user_id' => $u->id]);
        }

        /* ---------- FORUM ---------- */
        $thread = ForumThread::firstOrCreate(
            ['judul' => 'Voting lokasi reuni besar 2027'],
            [
                'user_id' => $ketua->id, 'angkatan_id' => $angkatan2003->id,
                'isi' => "Teman-teman, mari vote lokasi reuni besar 2027. Opsi: Bogor (villa), Bandung (hotel), atau staycation Jakarta. Tulis pilihan kalian di balasan ya — boleh anonim.",
                'kategori' => 'ide', 'is_anonymous' => false, 'is_pinned' => true,
            ]
        );
        ForumReply::firstOrCreate(
            ['forum_thread_id' => $thread->id, 'isi' => 'Setuju Bogor! Villa adem, cocok buat anak-anak yang bawa keluarga.'],
            ['user_id' => $alumniUsers[4]->id, 'is_anonymous' => false]
        );
        ForumReply::firstOrCreate(
            ['forum_thread_id' => $thread->id, 'isi' => 'Saya usul Bandung aja, biar yang dari Jakarta seru roadtrip-nya 😄'],
            ['user_id' => $alumniUsers[6]->id, 'is_anonymous' => false]
        );

        /* ---------- DONASI ---------- */
        $campaign = DonationCampaign::firstOrCreate(
            ['slug' => 'santunan-alumni-sakit'],
            [
                'user_id' => $pengurus->id,
                'judul' => 'Santunan Alumni Angkatan 2003 yang Sedang Sakit',
                'deskripsi' => "Penggalangan dana untuk membantu teman seangkatan yang sedang menjalani perawatan. Laporan penggunaan dana akan dipublikasikan secara transparan.",
                'target' => 25000000, 'deadline' => now()->addDays(45)->toDateString(), 'is_active' => true,
            ]
        );
        DonationTransaction::firstOrCreate(
            ['donation_campaign_id' => $campaign->id, 'amount' => 5000000, 'status' => 'verified'],
            ['user_id' => $ketua->id, 'pesan' => 'Semoga lekas membaik ya!']
        );
        DonationTransaction::firstOrCreate(
            ['donation_campaign_id' => $campaign->id, 'amount' => 2000000, 'status' => 'verified'],
            ['user_id' => null, 'nama_donatur' => 'Anonim Baik Hati', 'pesan' => 'Sedikit semoga membantu.']
        );

        /* ---------- TIKET REUNI (e-ticket demo) ---------- */
        foreach ([$alumniUsers[2], $alumniUsers[4]] as $u) {
            \App\Models\EventRegistration::firstOrCreate(
                ['event_id' => $reuni->id, 'user_id' => $u->id],
                ['kode_tiket' => 'R67-' . strtoupper(Str::random(6)), 'status' => 'hadir', 'checked_in_at' => now()]
            );
        }
        \App\Models\EventRegistration::firstOrCreate(
            ['event_id' => $hangout->id, 'user_id' => $alumniUsers[3]->id],
            ['kode_tiket' => 'R67-' . strtoupper(Str::random(6)), 'status' => 'terdaftar']
        );

        $this->command->info('✅ Seed selesai! Login demo:');
        $this->command->table(['Role', 'Email', 'Password'], [
            ['Super Admin', 'admin@alumnismun67halim2003.id', 'password'],
            ['Pengurus', 'pengurus@alumni67.id', 'password'],
            ['Ketua Angkatan', 'ketua2003@alumni67.id', 'password'],
            ['Alumni', 'andi@alumni67.id', 'password'],
        ]);
    }
}
