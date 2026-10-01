<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pesan chat pribadi antar alumni. */
class Message extends Model
{
    protected $fillable = ['from_id', 'to_id', 'body', 'read_at'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function from(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    public function to(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_id');
    }

    /** Balasan cepat: teks + waktu singkat (cth. "2 mnt lalu"). */
    public function timeForHumans(): string
    {
        return $this->created_at?->diffForHumans(short: true) ?? '';
    }
}
