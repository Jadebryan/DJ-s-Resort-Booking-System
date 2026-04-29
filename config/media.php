<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Media storage driver
    |--------------------------------------------------------------------------
    |
    | Supported: "local", "cloudinary"
    |
    | - local: stores in Storage::disk('public') (storage/app/public)
    | - cloudinary: uploads to Cloudinary and stores the returned secure URL
    |
    */
    'driver' => env('MEDIA_DRIVER', 'local'),

    'cloudinary' => [
        // Preferred: set CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
        'url' => env('CLOUDINARY_URL'),

        // Optional explicit config (used if CLOUDINARY_URL is not set)
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
    ],
];

