<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonationTransaction extends Model
{
    protected $fillable = [
        'donation_campaign_id', 'user_id', 'nama_donatur', 'amount', 'pesan', 'bukti_path', 'status',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DonationCampaign::class, 'donation_campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function labelDonatur(): string
    {
        return $this->nama_donatur ?: ($this->user?->name ?: 'Donatur');
    }
}
