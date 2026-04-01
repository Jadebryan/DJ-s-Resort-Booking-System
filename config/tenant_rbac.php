<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Staff resources and valid actions (dashboard UI + permission checks)
    |--------------------------------------------------------------------------
    */
    'staff_resources' => [
        'dashboard' => ['read'],
        'rooms' => ['create', 'read', 'update', 'delete'],
        'bookings' => ['read', 'update', 'confirm', 'cancel'],
        'reports' => ['read', 'export'],
        'branding' => ['read', 'update'],
        'staff' => ['create', 'read', 'update', 'delete'],
        'domains' => ['create', 'read', 'update', 'delete'],
        'settings' => ['read', 'update'],
        'activity' => ['read'],
        'payment' => ['read', 'update'],
        'rbac' => ['read', 'update'],
        'guests' => ['read', 'update'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Customer (portal) resources — regular_user guard
    |--------------------------------------------------------------------------
    */
    'customer_resources' => [
        'portal' => ['read', 'update', 'upload_proof'],
        'portal_profile' => ['read', 'update'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route name => [resource, action] for auth:tenant
    |--------------------------------------------------------------------------
    */
    'staff_route_permissions' => [
        'tenant.api.check-update' => ['settings', 'read'],
        'tenant.api.apply-update' => ['settings', 'update'],
        'tenant.dashboard' => ['dashboard', 'read'],
        'tenant.rooms.index' => ['rooms', 'read'],
        'tenant.rooms.create' => ['rooms', 'create'],
        'tenant.rooms.store' => ['rooms', 'create'],
        'tenant.rooms.edit' => ['rooms', 'read'],
        'tenant.rooms.update' => ['rooms', 'update'],
        'tenant.rooms.destroy' => ['rooms', 'delete'],
        'tenant.branding.edit' => ['branding', 'read'],
        'tenant.branding.update' => ['branding', 'update'],
        'tenant.bookings.index' => ['bookings', 'read'],
        'tenant.bookings.calendar' => ['bookings', 'read'],
        'tenant.bookings.confirm' => ['bookings', 'confirm'],
        'tenant.bookings.cancel' => ['bookings', 'cancel'],
        'tenant.reports.index' => ['reports', 'read'],
        'tenant.reports.analytics' => ['reports', 'read'],
        'tenant.reports.export.csv' => ['reports', 'export'],
        'tenant.reports.export.pdf' => ['reports', 'export'],
        'tenant.activity.index' => ['activity', 'read'],
        'tenant.notifications.feed' => ['dashboard', 'read'],
        'tenant.payment.portal' => ['payment', 'read'],
        'tenant.payment.upgrade-quote' => ['payment', 'read'],
        'tenant.payment.upgrade-request' => ['payment', 'update'],
        'tenant.domains.index' => ['domains', 'read'],
        'tenant.domains.store' => ['domains', 'create'],
        'tenant.domains.destroy' => ['domains', 'delete'],
        'tenant.domains.primary' => ['domains', 'update'],
        'tenant.staff.index' => ['staff', 'read'],
        'tenant.staff.create' => ['staff', 'create'],
        'tenant.staff.store' => ['staff', 'create'],
        'tenant.staff.edit' => ['staff', 'read'],
        'tenant.staff.update' => ['staff', 'update'],
        'tenant.staff.destroy' => ['staff', 'delete'],
        'tenant.settings.index' => ['settings', 'read'],
        'tenant.settings.update' => ['settings', 'update'],
        'tenant.rbac.index' => ['rbac', 'read'],
        'tenant.rbac.initialize' => ['rbac', 'update'],
        'tenant.rbac.update' => ['rbac', 'update'],
        'tenant.users.index' => ['guests', 'read'],
        'tenant.users.update-role' => ['guests', 'update'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route name => [resource, action] for auth:regular_user
    |--------------------------------------------------------------------------
    */
    'customer_route_permissions' => [
        'tenant.user.dashboard' => ['portal', 'read'],
        'tenant.user.notifications.feed' => ['portal', 'read'],
        'tenant.user.bookings.index' => ['portal', 'read'],
        'tenant.user.bookings.update' => ['portal', 'update'],
        'tenant.user.bookings.upload-proof' => ['portal', 'upload_proof'],
        'tenant.user.profile.edit' => ['portal_profile', 'read'],
        'tenant.user.profile.update' => ['portal_profile', 'update'],
        'tenant.user.profile.destroy' => ['portal_profile', 'update'],
        'tenant.user.verification.send' => ['portal_profile', 'update'],
        'tenant.user.password.update' => ['portal_profile', 'update'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Staff with role=staff and no tenant_rbac_role_id (legacy)
    |--------------------------------------------------------------------------
    */
    'legacy_staff_permissions' => [
        'dashboard' => ['read'],
        'rooms' => ['create', 'read', 'update', 'delete'],
        'bookings' => ['read', 'update', 'confirm', 'cancel'],
        'reports' => ['read', 'export'],
        'branding' => [],
        'staff' => [],
        'domains' => [],
        'settings' => ['read', 'update'],
        'activity' => ['read'],
        'payment' => ['read', 'update'],
        'rbac' => [],
        'guests' => ['read'],
    ],
];
