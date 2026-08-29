<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Berita extends Model
{
    protected $fillable = [
        'user_id', 'judul', 'slug', 'ringkasan', 'isi', 'gambar', 'is_pinned', 'published_at',
    ];

    protected function casts(): array
    {
        return ['is_pinned' => 'boolean', 'published_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
