<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Pemetaan key setting di database → konfigurasi runtime Laravel.
     * Bila Super Admin mengisi SMTP lewat panel admin, nilai di sini
     * menimpa konfigurasi default dari .env — tanpa perlu restart.
     */
    private const MAIL_MAP = [
        'mail_mailer'       => 'mail.default',
        'mail_host'         => 'mail.mailers.smtp.host',
        'mail_port'         => 'mail.mailers.smtp.port',
        'mail_username'     => 'mail.mailers.smtp.username',
        'mail_encryption'   => 'mail.mailers.smtp.encryption',
        'mail_from_address' => 'mail.from.address',
        'mail_from_name'    => 'mail.from.name',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->overrideMailConfig();
        $this->brandAuthEmails();
    }

    /**
     * Notifikasi auth (reset password & verifikasi email) memakai template HTML
     * brand — header logo Alumni 67 + footer komunitas, bahasa Indonesia.
     */
    private function brandAuthEmails(): void
    {
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->view('emails.auth.reset-password', ['user' => $notifiable, 'url' => $url])
                ->subject('Reset Password — Alumni67 Connect');
        });

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->view('emails.auth.verify-email', ['user' => $notifiable, 'url' => $url])
                ->subject('Verifikasi Email — Alumni67 Connect');
        });
    }

    /**
     * Terapkan pengaturan SMTP dari database ke config runtime.
     * Aman dijalankan saat migrasi awal (tabel belum ada → lewati).
     */
    private function overrideMailConfig(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }

            foreach (self::MAIL_MAP as $key => $configKey) {
                $value = Setting::get($key);
                if ($value === null || $value === '') {
                    continue;
                }

                // From name boleh dikutip dari .env — bersihkan
                if ($key === 'mail_from_name') {
                    $value = trim(str_replace('"', '', $value));
                }

                config([$configKey => $value]);
            }

            // Password SMTP disimpan terenkripsi di database
            $password = Setting::getEncrypted('mail_password');
            if ($password !== null) {
                config(['mail.mailers.smtp.password' => $password]);
            }
        } catch (\Throwable) {
            // Abaikan saat migrasi awal / console belum siap.
        }
    }
}
