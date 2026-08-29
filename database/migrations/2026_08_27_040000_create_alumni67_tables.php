<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi tabel aplikasi Alumni67 Connect.
 * Mengikuti "Modul Database Penting" pada dokumen konsep:
 * users, alumni_profiles, angkatan, jobs, forums, comments,
 * events, donations, businesses (usaha di alumni_profiles), messages.
 */
return new class extends Migration
{
    public function up(): void
    {
        /* ---------- ANGKATAN ---------- */
        Schema::create('angkatan', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun')->unique();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        /* ---------- USERS: relasi ke angkatan ---------- */
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('angkatan_id')->nullable()
                ->constrained('angkatan')->nullOnDelete();
        });

        /* ---------- ALUMNI PROFILES ---------- */
        Schema::create('alumni_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            // Data sekolah
            $table->string('nis', 30)->nullable();
            $table->string('kelas', 30)->nullable();          // IPA 1 / IPS 3 / Bestie 67
            $table->unsignedSmallInteger('tahun_lulus')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('no_wa', 30)->nullable();
            $table->text('bio')->nullable();
            // Data profesional (untuk direktori alumni)
            $table->string('pekerjaan')->nullable();
            $table->string('perusahaan')->nullable();
            $table->string('bidang', 40)->nullable();          // IT / Desain / BUMN / dst
            $table->string('kota', 60)->nullable();
            $table->string('kampus', 120)->nullable();
            $table->string('skill')->nullable();               // dipisah koma
            $table->string('instagram', 100)->nullable();
            $table->string('linkedin', 150)->nullable();
            $table->string('foto')->nullable();
            // Usaha/bisnis alumni (#BisnisAlumni67)
            $table->string('usaha_nama')->nullable();
            $table->string('usaha_deskripsi')->nullable();
            // Verified badge (upload ijazah + approval admin)
            $table->string('verification_status', 20)->default('none'); // none|pending|approved|rejected
            $table->string('ijazah_path')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('catatan_verifikasi')->nullable();
            $table->timestamps();
        });

        /* ---------- BERITA / PENGUMUMAN ---------- */
        Schema::create('beritas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('ringkasan', 300)->nullable();
            $table->text('isi');
            $table->string('gambar')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        /* ---------- EVENT / REUNI ---------- */
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('deskripsi');
            $table->string('lokasi');
            $table->dateTime('mulai');
            $table->dateTime('selesai')->nullable();
            $table->unsignedInteger('kapasitas')->nullable();
            $table->decimal('harga_tiket', 12, 2)->default(0);
            $table->string('poster')->nullable();
            $table->string('status', 20)->default('publish');   // draft|publish|selesai
            $table->timestamps();
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kode_tiket', 20)->unique();          // untuk QR check-in
            $table->string('status', 20)->default('terdaftar');  // terdaftar|hadir|batal
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'user_id']);
        });

        /* ---------- BURSA KERJA (tabel: vacancies, karena "jobs" dipakai queue Laravel) ---------- */
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('judul');
            $table->string('perusahaan')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('tipe', 20)->default('fulltime');     // fulltime|kontrak|freelance|magang|remote
            $table->string('kategori', 40)->default('Lainnya');  // IT|Desain|Pemerintahan|BUMN|Kesehatan|Bisnis|Lainnya
            $table->unsignedBigInteger('gaji_min')->nullable();
            $table->unsignedBigInteger('gaji_max')->nullable();
            $table->text('deskripsi');
            $table->text('cara_melamar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* ---------- SOCIAL FEED ---------- */
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('isi');
            $table->string('gambar')->nullable();
            $table->timestamps();
        });

        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['post_id', 'user_id']);
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('isi');
            $table->timestamps();
        });

        /* ---------- FORUM ASPIRASI ---------- */
        Schema::create('forum_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('angkatan_id')->nullable()->constrained('angkatan')->nullOnDelete();
            $table->string('judul');
            $table->text('isi');
            $table->string('kategori', 30)->default('aspirasi'); // aspirasi|ide|saran|lainnya
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });

        Schema::create('forum_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('isi');
            $table->boolean('is_anonymous')->default(false);
            $table->timestamps();
        });

        /* ---------- DONASI & SOSIAL ---------- */
        Schema::create('donation_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('deskripsi');
            $table->decimal('target', 14, 2)->default(0);
            $table->string('poster')->nullable();
            $table->date('deadline')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('donation_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama_donatur')->nullable();          // untuk donasi anonim/tamu
            $table->decimal('amount', 14, 2);
            $table->string('pesan', 300)->nullable();
            $table->string('bukti_path')->nullable();            // bukti transfer
            $table->string('status', 20)->default('pending');    // pending|verified|rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_transactions');
        Schema::dropIfExists('donation_campaigns');
        Schema::dropIfExists('forum_replies');
        Schema::dropIfExists('forum_threads');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('likes');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('vacancies');
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
        Schema::dropIfExists('beritas');
        Schema::dropIfExists('alumni_profiles');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('angkatan_id');
        });
        Schema::dropIfExists('angkatan');
    }
};
