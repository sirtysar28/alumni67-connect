<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumThread extends Model
{
    protected $fillable = [
        'user_id', 'angkatan_id', 'judul', 'isi', 'kategori', 'is_anonymous', 'is_pinned',
    ];

    protected function casts(): array
    {
        return ['is_anonymous' => 'boolean', 'is_pinned' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function angkatan(): BelongsTo
    {
        return $this->belongsTo(Angkatan::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ForumReply::class)->oldest();
    }

    /** Nama tampilan: anonim bila thread anonim. */
    public function displayName(): string
    {
        if ($this->is_anonymous) {
            return 'Anonim';
        }
        $u = $this->user;

        return $u ? $u->name : 'Alumni';
    }
}
