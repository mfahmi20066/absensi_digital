<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KodeOtpVerifikasi extends Notification
{
    use Queueable;

    public function __construct(public readonly string $kode)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode OTP Verifikasi Email - '.config('app.name'))
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kode OTP Anda untuk memverifikasi email adalah:')
            ->line('**'.$this->kode.'**')
            ->line('Kode ini berlaku selama 10 menit. Jangan bagikan kode ini kepada siapa pun.')
            ->line('Jika Anda tidak melakukan pendaftaran, abaikan email ini.');
    }
}
