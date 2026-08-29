<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumReply extends Model
{
    protected $fillable = ['forum_thread_id', 'user_id', 'isi', 'is_anonymous'];

    protected function casts(): array
    {
        return ['is_anonymous' => 'boolean'];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(ForumThread::class, 'forum_thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function displayName(): string
    {
        if ($this->is_anonymous) {
            return 'Anonim';
        }
        $u = $this->user;

        return $u ? $u->name : 'Alumni';
    }
}
