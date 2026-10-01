<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notifikasi database generik untuk seluruh fitur Alumni67 Connect
 * (tampil di ikon lonceng 🔔 navbar — berlaku untuk semua user login).
 *
 * Pakai: $user->notify(new AlumniNotification('Judul', 'Pesan', '/url-tujuan', 'icon'));
 */
class AlumniNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $message,
        public string $url = '/dashboard',
        public string $icon = '🔔',
    ) {}

    /** Kirim ke channel database (tabel notifications). */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** Payload yang disimpan di kolom data. */
    public function toArray(object $notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'url'     => $this->url,
            'icon'    => $this->icon,
        ];
    }
}
