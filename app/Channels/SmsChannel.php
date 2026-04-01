<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (!config('services.sms.enabled', false)) {
            return;
        }

        $phone = $notifiable->routeNotificationFor('sms');
        if (empty($phone)) {
            return;
        }

        $message = $notification->toSms($notifiable);
        if (empty($message)) {
            return;
        }

        $driver = config('services.sms.driver', 'log');

        if ($driver === 'twilio') {
            $this->sendViaTwilio($phone, $message);
        } else {
            Log::channel('single')->info('SMS (log driver)', [
                'to' => $phone,
                'message' => $message,
            ]);
        }
    }

    protected function sendViaTwilio(string $phone, string $message): void
    {
        $sid = config('services.sms.twilio.sid');
        $token = config('services.sms.twilio.token');
        $from = config('services.sms.twilio.from');

        if (empty($sid) || empty($token) || empty($from)) {
            Log::channel('single')->warning('SMS Twilio: missing credentials, message not sent.', ['to' => $phone]);
            return;
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post(
                    'https://api.twilio.com/2010-04-01/Accounts/' . $sid . '/Messages.json',
                    [
                        'To' => $phone,
                        'From' => $from,
                        'Body' => $message,
                    ]
                );

            if (!$response->successful()) {
                Log::channel('single')->error('SMS Twilio failed', [
                    'to' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('single')->error('SMS Twilio exception: ' . $e->getMessage(), ['to' => $phone]);
        }
    }
}
