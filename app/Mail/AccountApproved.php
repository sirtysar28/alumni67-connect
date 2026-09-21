<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email notifikasi: akun telah DISETUJUI admin, silakan login.
 */
class AccountApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akun disetujui — selamat bergabung di Alumni67 Connect! 🎉',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.approval.approved',
        );
    }
}
