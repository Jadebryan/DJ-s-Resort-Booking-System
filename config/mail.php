<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email header logo (Markdown / HTML mails)
    |--------------------------------------------------------------------------
    |
    | MAIL_LOGO_URL: full absolute URL (best for CDNs or external assets).
    | MAIL_LOGO_PATH: path under /public, e.g. images/mail-logo.png — combined
    | with asset() so APP_URL must be correct for recipients to load the image.
    | If both are empty, the header shows APP_NAME as text (no Laravel logo).
    |
    */

    'logo_url' => env('MAIL_LOGO_URL'),

    'logo_path' => env('MAIL_LOGO_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Markdown mail
    |--------------------------------------------------------------------------
    |
    | Theme is the file name under html/themes/ (e.g. "default"). If
    | MAIL_MARKDOWN_THEME is wrongly set to "themes.default", it is normalized.
    |
    | Laravel 11+ no longer ships html/themes/default inside the framework; run
    | `php artisan vendor:publish --tag=laravel-mail` and keep paths below so
    | Markdown can load resources/views/vendor/mail/html/themes/default.blade.php.
    |
    */

    'markdown' => [
        'theme' => (static function (): string {
            $t = trim((string) env('MAIL_MARKDOWN_THEME', 'default'));
            $t = $t !== '' ? $t : 'default';
            if (str_starts_with($t, 'themes.')) {
                $t = substr($t, strlen('themes.'));
            }

            return $t !== '' ? $t : 'default';
        })(),
        'paths' => array_values(array_filter([
            is_dir(resource_path('views/vendor/mail')) ? resource_path('views/vendor/mail') : null,
        ])),
    ],

];
