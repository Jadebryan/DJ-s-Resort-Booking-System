<?php

return [

    /*
    | Comma-separated emails to notify on new resort signup requests.
    | Leave empty to notify every superadmin (admins table).
    */
    'notify_admin_emails' => env('TENANT_REGISTRATION_NOTIFY_EMAILS', ''),

];
