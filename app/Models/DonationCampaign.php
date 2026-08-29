<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonationCampaign extends Model
{
    protected $fillable = [
        'user_id', 'judul', 'slug', 'deskripsi', 'target', 'poster', 'deadline', 'is_active',
    ];

    protected function casts(): array
    {
        return ['target' => 'decimal:2', 'deadline' => 'date', 'is_active' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(DonationTransaction::class);
    }

    /** Total donasi yang sudah diverifikasi (laporan transparan). */
    public function terkumpul(): float
    {
        return (float) $this->transactions()->where('status', 'verified')->sum('amount');
    }

    public function progressPercent(): int
    {
        if ($this->target <= 0) {
            return 0;
        }

        return (int) min(100, round($this->terkumpul() / $this->target * 100));
    }
}
