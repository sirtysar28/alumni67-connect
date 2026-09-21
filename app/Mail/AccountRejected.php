<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email notifikasi: pendaftaran DITOLAK admin (dengan alasan).
 */
class AccountRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $alasan)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hasil peninjauan pendaftaran akun Alumni67 Connect',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.approval.rejected',
        );
    }
}
