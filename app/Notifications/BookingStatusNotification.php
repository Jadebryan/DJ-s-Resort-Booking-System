<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $status
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['mail'];
        if (config('services.sms.enabled', false) && $notifiable->routeNotificationFor('sms')) {
            $channels[] = \App\Channels\SmsChannel::class;
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $room = $this->booking->room?->name ?? 'Room';
        $checkIn = $this->booking->check_in?->format('M j, Y');
        $checkOut = $this->booking->check_out?->format('M j, Y');

        if ($this->status === 'confirmed') {
            return (new MailMessage)
                ->subject('Booking confirmed – ' . $room)
                ->line('Your reservation has been confirmed.')
                ->line('Room: ' . $room)
                ->line('Check-in: ' . $checkIn)
                ->line('Check-out: ' . $checkOut)
                ->line('Thank you for your booking.');
        }

        if ($this->status === 'cancelled') {
            return (new MailMessage)
                ->subject('Booking cancelled – ' . $room)
                ->line('Your reservation has been cancelled.')
                ->line('Room: ' . $room)
                ->line('Dates: ' . $checkIn . ' – ' . $checkOut);
        }

        // pending / received
        return (new MailMessage)
            ->subject('Booking received – ' . $room)
            ->line('We have received your booking request.')
            ->line('Room: ' . $room)
            ->line('Check-in: ' . $checkIn)
            ->line('Check-out: ' . $checkOut)
            ->line('You will be notified once it is confirmed.');
    }

    public function toSms(object $notifiable): string
    {
        $room = $this->booking->room?->name ?? 'Room';
        $checkIn = $this->booking->check_in?->format('M j');
        $checkOut = $this->booking->check_out?->format('M j');

        if ($this->status === 'confirmed') {
            return "Booking confirmed. {$room}, {$checkIn}–{$checkOut}. Thank you.";
        }
        if ($this->status === 'cancelled') {
            return "Your booking for {$room} ({$checkIn}–{$checkOut}) has been cancelled.";
        }
        return "Booking received for {$room}, {$checkIn}–{$checkOut}. We'll confirm shortly.";
    }
}
