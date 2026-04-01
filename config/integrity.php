<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant audit log HMAC key
    |--------------------------------------------------------------------------
    |
    | Optional dedicated secret for tamper-evident activity log chaining.
    | If empty, the application key is used (still secure if APP_KEY is private).
    |
    */

    'audit_hmac_key' => env('AUDIT_HMAC_KEY', ''),

];
