<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceTicket extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'title',
        'priority',
        'status',
        'related_tenant',
        'description',
    ];

    public static function statusLabels(): array
    {
        return [
            self::STATUS_OPEN => __('Open'),
            self::STATUS_IN_PROGRESS => __('In progress'),
            self::STATUS_RESOLVED => __('Resolved'),
        ];
    }

    public static function priorityLabels(): array
    {
        return [
            'low' => __('Low'),
            'medium' => __('Medium'),
            'high' => __('High'),
            'critical' => __('Critical'),
        ];
    }
}
