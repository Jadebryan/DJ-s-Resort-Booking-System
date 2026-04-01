<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $connection = 'tenant';

    protected $table = 'rooms';

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'type',
        'capacity',
        'price_per_night',
        'is_available',
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }
}
