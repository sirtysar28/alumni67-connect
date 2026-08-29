<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumniProfile extends Model
{
    protected $fillable = [
        'user_id', 'nis', 'kelas', 'tahun_lulus', 'tgl_lahir', 'no_wa', 'bio',
        'pekerjaan', 'perusahaan', 'bidang', 'kota', 'kampus', 'skill',
        'instagram', 'linkedin', 'foto', 'usaha_nama', 'usaha_deskripsi',
        'verification_status', 'ijazah_path', 'verified_at', 'catatan_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tgl_lahir'   => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSkillArrayAttribute(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->skill))));
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'approved';
    }
}
