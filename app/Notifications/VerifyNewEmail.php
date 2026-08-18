<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Dikirim ke alamat email BARU saat user meminta ganti email dari halaman
 * Profil — beda dari Illuminate\Auth\Notifications\VerifyEmail bawaan
 * (dipakai VerifyEmailController untuk verifikasi email yang SEDANG aktif).
 * Dikirim via Notification::route('mail', $emailBaru) karena tujuannya
 * BUKAN alamat email user saat ini di database.
 */
class VerifyNewEmail extends Notification
{
    use Queueable;

    public function __construct(public readonly string $verificationUrl)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifikasi Alamat Email Baru — ' . config('app.name'))
            ->line('Anda menerima email ini karena ada permintaan untuk menjadikan alamat email ini sebagai email akun ' . config('app.name') . '.')
            ->action('Verifikasi Email Baru', $this->verificationUrl)
            ->line('Link verifikasi ini akan kedaluwarsa dalam 60 menit.')
            ->line('Jika Anda tidak meminta perubahan ini, abaikan email ini — email akun Anda tidak akan berubah.');
    }
}
