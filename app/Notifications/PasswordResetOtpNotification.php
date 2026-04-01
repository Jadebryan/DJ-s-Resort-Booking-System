<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
        public string $siteLabel,
        public int $expiresMinutes = 15,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Password reset code — :site', ['site' => $this->siteLabel]))
            ->line(__('Use this code to finish resetting your password:'))
            ->line($this->code)
            ->line(__('This code expires in :minutes minutes. If you did not request a reset, ignore this email.', [
                'minutes' => $this->expiresMinutes,
            ]));
    }
}
