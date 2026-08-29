<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Pengaturan aplikasi (key-value) — dipakai antara lain untuk
 * konfigurasi SMTP email yang bisa diubah dari panel admin.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** PK berupa string "key" (bukan id auto-increment). */
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    /** Ambil nilai setting (null bila belum ada). */
    public static function get(string $key, ?string $default = null): ?string
    {
        $row = static::query()->where('key', $key)->value('value');

        return $row !== null ? $row : $default;
    }

    /** Simpan setting (upsert). */
    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }

    /** Ambil nilai terenkripsi (untuk password SMTP). */
    public static function getEncrypted(string $key): ?string
    {
        $raw = static::get($key);
        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            return Crypt::decryptString($raw);
        } catch (\Throwable) {
            return null;
        }
    }

    /** Simpan nilai terenkripsi. */
    public static function setEncrypted(string $key, ?string $value): void
    {
        static::set($key, $value ? Crypt::encryptString($value) : null);
    }
}
