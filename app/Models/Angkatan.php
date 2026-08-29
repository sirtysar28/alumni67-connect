<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Angkatan extends Model
{
    protected $table = 'angkatan';

    protected $fillable = ['tahun', 'nama', 'slug'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
