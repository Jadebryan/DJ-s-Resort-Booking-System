<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\Tenant;
use App\Support\SignedBookingReceiptUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $status,
        public ?Tenant $tenant = null,
        public ?string $tenantPortalBaseUrl = null,
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
        $this->booking->loadMissing('room', 'user');

        $room = $this->booking->room?->name ?? __('Room');
        $guestReceiptUrl = null;
        if ($this->tenant && $this->tenantPortalBaseUrl) {
            $guestReceiptUrl = SignedBookingReceiptUrl::make($this->booking, $this->tenantPortalBaseUrl);
        }

        $printedAt = now()->timezone(config('app.timezone'));

        $subject = match ($this->status) {
            'confirmed' => __('Booking confirmed – :room', ['room' => $room]),
            'cancelled' => __('Booking cancelled – :room', ['room' => $room]),
            default => __('Booking received – :room', ['room' => $room]),
        };

        return (new MailMessage)
            ->subject($subject)
            ->view('mail.booking-status', [
                'booking' => $this->booking,
                'status' => $this->status,
                'tenant' => $this->tenant,
                'guestReceiptUrl' => $guestReceiptUrl,
                'printedAt' => $printedAt,
            ]);
    }

    public function toSms(object $notifiable): string
    {
        $this->booking->loadMissing('room');
        $room = $this->booking->room?->name ?? 'Room';
        $checkIn = $this->booking->check_in?->format('M j');
        $checkOut = $this->booking->check_out?->format('M j');

        if ($this->status === 'confirmed') {
            $msg = "Booking confirmed. {$room}, {$checkIn}–{$checkOut}. Thank you.";
            if ($this->booking->is_fully_paid) {
                $msg .= ' '.(__('Present your receipt at check-in.'));
            }

            return $msg;
        }
        if ($this->status === 'cancelled') {
            return "Your booking for {$room} ({$checkIn}–{$checkOut}) has been cancelled.";
        }

        return "Booking received for {$room}, {$checkIn}–{$checkOut}. We'll confirm shortly.";
    }
}
