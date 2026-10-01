<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'angkatan_id',
        'is_approved',
        'approval_note',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function angkatan(): BelongsTo
    {
        return $this->belongsTo(Angkatan::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(AlumniProfile::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function jobsPosted(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    /** Inisial profil + nama kelas untuk avatar, mis. "Rina — IPA 2". */
    public function labelWithKelas(): string
    {
        $kelas = $this->profile?->kelas;

        return $kelas ? "{$this->name} — {$kelas}" : $this->name;
    }

    /* ---------- NOTIFIKASI AMAN (tabel boleh belum ada) ---------- */

    /** Kirim notifikasi tanpa risiko error walau tabel `notifications` belum dibuat
     *  (mis. setelah deploy ke cPanel sebelum `migrate` dijalankan lewat Terminal admin). */
    public function notifySafe(object $notification): void
    {
        try {
            $this->notify($notification);
        } catch (\Throwable) {
            // Tabel belum ada / DB bermasalah → abaikan diam-diam agar aksi utama tetap sukses.
        }
    }

    /** Jumlah belum-dibaca — 0 bila tabel belum ada. */
    public function unreadNotificationsCountSafe(): int
    {
        try {
            return $this->unreadNotifications()->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /** Notifikasi terbaru — koleksi kosong bila tabel belum ada. */
    public function recentNotificationsSafe(int $limit = 6): \Illuminate\Support\Collection
    {
        try {
            return $this->notifications()->latest()->limit($limit)->get();
        } catch (\Throwable) {
            return collect();
        }
    }
}
