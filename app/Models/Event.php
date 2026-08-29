<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'user_id', 'judul', 'slug', 'deskripsi', 'lokasi', 'mulai', 'selesai',
        'kapasitas', 'harga_tiket', 'poster', 'status',
    ];

    protected function casts(): array
    {
        return ['mulai' => 'datetime', 'selesai' => 'datetime', 'harga_tiket' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('mulai', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('mulai', '<', now());
    }

    public function sisaKapasitas(): ?int
    {
        return $this->kapasitas === null ? null : max(0, $this->kapasitas - $this->registrations()->count());
    }
}
