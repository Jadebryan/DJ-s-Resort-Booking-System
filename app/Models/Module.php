<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'modules';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
        'sort_order',
        'metadata',
        'plan_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'json',
        'plan_id' => 'integer',
    ];

    /**
     * Get the plan associated with this module.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
