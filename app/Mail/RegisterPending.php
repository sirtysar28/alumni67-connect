<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email konfirmasi setelah register: pendaftaran diterima,
 * menunggu persetujuan admin sebelum akun bisa login.
 */
class RegisterPending extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran diterima — menunggu persetujuan admin',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.approval.pending',
        );
    }
}
