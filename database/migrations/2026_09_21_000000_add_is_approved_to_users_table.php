<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Approval akun: user baru hasil register TIDAK langsung aktif —
 * harus disetujui admin dulu (menu Admin → Setujui Akun).
 *
 * default(true) agar user lama (sudah terinstall) tetap aktif;
 * form register yang secara eksplisit mengirim is_approved = false.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_approved')->default(true)->after('remember_token');
            $table->string('approval_note', 255)->nullable()->after('is_approved');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_approved', 'approval_note']);
        });
    }
};
