<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformSetting extends Model
{
    protected $table = 'platform_settings';

    protected $fillable = [
        'default_plan_id',
        'timezone',
        'send_system_emails',
        'send_sms_alerts',
        'feature_booking_calendar_beta',
        'feature_multi_currency',
    ];

    protected function casts(): array
    {
        return [
            'send_system_emails' => 'boolean',
            'send_sms_alerts' => 'boolean',
            'feature_booking_calendar_beta' => 'boolean',
            'feature_multi_currency' => 'boolean',
        ];
    }

    public function defaultPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'default_plan_id');
    }

    /** Single platform-wide settings row. */
    public static function instance(): self
    {
        return static::query()->first() ?? static::query()->create([
            'timezone' => config('app.timezone', 'Asia/Manila'),
            'send_system_emails' => true,
            'send_sms_alerts' => false,
            'feature_booking_calendar_beta' => false,
            'feature_multi_currency' => false,
        ]);
    }

    public static function featureEnabled(string $key): bool
    {
        $s = static::instance();

        return match ($key) {
            'booking_calendar_beta' => $s->feature_booking_calendar_beta,
            'multi_currency' => $s->feature_multi_currency,
            default => false,
        };
    }
}
