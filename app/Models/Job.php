<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Job extends Model
{
    protected $table = 'vacancies';

    protected $fillable = [
        'user_id', 'judul', 'perusahaan', 'lokasi', 'tipe', 'kategori',
        'gaji_min', 'gaji_max', 'deskripsi', 'cara_melamar', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
