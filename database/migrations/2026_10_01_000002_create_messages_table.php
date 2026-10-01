<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chat pribadi antar alumni (sesuai dokumen konsep — modul messages).
 * Widget melayang 💬 di pojok kanan bawah, khusus user login.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['from_id', 'to_id']);        // ambil percakapan
            $table->index(['to_id', 'read_at']);        // hitung belum-dibaca
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
