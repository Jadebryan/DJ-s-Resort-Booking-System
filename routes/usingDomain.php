<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\DashboardController;
use App\Http\Middleware\SetTenantDatabase;
use App\Models\TenantDomain;
use App\Http\Controllers\tenantControlllers\ProfileController;
use App\Http\Controllers\Tenant\TenantSystemUpdateController;

/**
 * Tenant routes: served only on mapped custom domains (not the central APP_URL host).
 */
$centralHost = strtolower((string) (parse_url(config('app.url'), PHP_URL_HOST) ?: ''));
$domainPattern = $centralHost !== ''
    ? '^(?!'.preg_quote($centralHost, '/').')([a-zA-Z0-9\-.]+)$'
    : '.+';

Route::domain('{tenant_domain}')
    ->where(['tenant_domain' => $domainPattern])
    ->middleware([SetTenantDatabase::class])
    ->group(function () {
        
        Route::get('/', function ($tenant_domain) {
            $domain = TenantDomain::forRequestHost($tenant_domain);
            if (! $domain || ! $domain->tenant) {
                abort(404, 'Tenant not found');
            }
            $rooms = \App\Models\Room::where('is_available', true)->with('images')->orderBy('name')->get();
            $bookedRoomIds = \App\Models\Booking::query()
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('check_out', '>=', now()->toDateString())
                ->pluck('room_id')
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values()
                ->all();

            return view('Tenant.TenantLandingPage', [
                'tenant' => $domain->tenant,
                'rooms' => $rooms,
                'bookedRoomIds' => $bookedRoomIds,
            ]);
        })->name('tenant.landing');

        Route::middleware('guest')->group(function () {
            Route::get('/forgot-password', [\App\Http\Controllers\Auth\tenantAuthController\PasswordResetLinkController::class, 'create'])
                ->name('tenant.password.request');
            Route::post('/forgot-password', [\App\Http\Controllers\Auth\tenantAuthController\PasswordResetLinkController::class, 'store'])
                ->name('tenant.password.email');
            Route::get('/reset-password/verify', [\App\Http\Controllers\Auth\tenantAuthController\PasswordOtpController::class, 'create'])
                ->name('tenant.password.otp');
            Route::post('/reset-password/verify', [\App\Http\Controllers\Auth\tenantAuthController\PasswordOtpController::class, 'store'])
                ->name('tenant.password.otp.verify');
            Route::post('/reset-password/resend-otp', [\App\Http\Controllers\Auth\tenantAuthController\PasswordOtpController::class, 'resend'])
                ->name('tenant.password.otp.resend');
            Route::get('/reset-password', [\App\Http\Controllers\Auth\tenantAuthController\NewPasswordController::class, 'create'])
                ->name('tenant.password.reset');
            Route::post('/reset-password', [\App\Http\Controllers\Auth\tenantAuthController\NewPasswordController::class, 'store'])
                ->name('tenant.password.store');

            Route::get('/login', [\App\Http\Controllers\Auth\TenantLoginController::class, 'create'])->name('tenant.login');
            Route::post('/login', [\App\Http\Controllers\Auth\TenantLoginController::class, 'store']);
            Route::get('/user/login', [\App\Http\Controllers\Auth\TenantUserLoginController::class, 'create'])->name('tenant.user.login');
            Route::post('/user/login', [\App\Http\Controllers\Auth\TenantUserLoginController::class, 'store']);
            Route::get('/user/register', [\App\Http\Controllers\Auth\tenantUserAuthController\RegisteredUserController::class, 'create'])->name('tenant.user.register');
            Route::post('/user/register', [\App\Http\Controllers\Auth\tenantUserAuthController\RegisteredUserController::class, 'store']);
            Route::get('/user/forgot-password', [\App\Http\Controllers\Auth\tenantUserAuthController\PasswordResetLinkController::class, 'create'])->name('tenant.user.password.request');
            Route::post('/user/forgot-password', [\App\Http\Controllers\Auth\tenantUserAuthController\PasswordResetLinkController::class, 'store'])->name('user.password.email');
            Route::get('/user/reset-password/verify', [\App\Http\Controllers\Auth\tenantUserAuthController\PasswordOtpController::class, 'create'])->name('tenant.user.password.otp');
            Route::post('/user/reset-password/verify', [\App\Http\Controllers\Auth\tenantUserAuthController\PasswordOtpController::class, 'store'])->name('tenant.user.password.otp.verify');
            Route::post('/user/reset-password/resend-otp', [\App\Http\Controllers\Auth\tenantUserAuthController\PasswordOtpController::class, 'resend'])->name('tenant.user.password.otp.resend');
            Route::get('/user/reset-password', [\App\Http\Controllers\Auth\tenantUserAuthController\NewPasswordController::class, 'create'])->name('tenant.user.password.reset');
            Route::post('/user/reset-password', [\App\Http\Controllers\Auth\tenantUserAuthController\NewPasswordController::class, 'store'])->name('user.password.store');
        });

        Route::get('/book', [\App\Http\Controllers\Tenant\PublicBookingController::class, 'index'])->name('tenant.book.index');
        Route::get('/book/{room}', [\App\Http\Controllers\Tenant\PublicBookingController::class, 'show'])->name('tenant.book.show')->where('room', '[0-9]+');
        Route::post('/book', [\App\Http\Controllers\Tenant\PublicBookingController::class, 'store'])->name('tenant.book.store');

        Route::get('/bookings/{booking}/receipt/guest', [\App\Http\Controllers\Tenant\BookingController::class, 'guestReceipt'])
            ->middleware('signed')
            ->name('tenant.booking.receipt.guest')
            ->where('booking', '[0-9]+');

        Route::middleware(['auth:tenant', 'tenant.staff.rbac'])->group(function () {
            Route::get('/api/tenant/check-update', [TenantSystemUpdateController::class, 'checkUpdate'])->name('tenant.api.check-update');
            Route::post('/api/tenant/apply-update', [TenantSystemUpdateController::class, 'applyUpdate'])->name('tenant.api.apply-update');

            Route::get('/dashboard', [DashboardController::class, 'tenantIndex'])->name('tenant.dashboard');
            Route::get('/rooms', [\App\Http\Controllers\Tenant\RoomController::class, 'index'])->name('tenant.rooms.index');
            Route::get('/rooms/create', [\App\Http\Controllers\Tenant\RoomController::class, 'create'])->name('tenant.rooms.create');
            Route::post('/rooms', [\App\Http\Controllers\Tenant\RoomController::class, 'store'])->name('tenant.rooms.store');
            Route::get('/rooms/{room}', [\App\Http\Controllers\Tenant\RoomController::class, 'edit'])->name('tenant.rooms.edit')->where('room', '[0-9]+');
            Route::patch('/rooms/{room}', [\App\Http\Controllers\Tenant\RoomController::class, 'update'])->name('tenant.rooms.update')->where('room', '[0-9]+');
            Route::delete('/rooms/{room}', [\App\Http\Controllers\Tenant\RoomController::class, 'destroy'])->name('tenant.rooms.destroy')->where('room', '[0-9]+');
            Route::get('/branding', [\App\Http\Controllers\Tenant\BrandingController::class, 'edit'])->name('tenant.branding.edit');
            Route::patch('/branding', [\App\Http\Controllers\Tenant\BrandingController::class, 'update'])->name('tenant.branding.update');
            Route::get('/bookings', [\App\Http\Controllers\Tenant\BookingController::class, 'index'])->name('tenant.bookings.index');
            Route::get('/bookings/{booking}/receipt', [\App\Http\Controllers\Tenant\BookingController::class, 'receipt'])->name('tenant.bookings.receipt')->where('booking', '[0-9]+');
            Route::get('/bookings/calendar', [\App\Http\Controllers\Tenant\BookingController::class, 'calendar'])->name('tenant.bookings.calendar');
            Route::post('/bookings/{booking}/confirm', [\App\Http\Controllers\Tenant\BookingController::class, 'confirm'])->name('tenant.bookings.confirm');
            Route::post('/bookings/{booking}/cancel', [\App\Http\Controllers\Tenant\BookingController::class, 'cancel'])->name('tenant.bookings.cancel');
            Route::put('/bookings/{booking}', [\App\Http\Controllers\Tenant\BookingController::class, 'update'])->name('tenant.bookings.update')->where('booking', '[0-9]+');
            Route::get('/reports', [\App\Http\Controllers\Tenant\ReportController::class, 'index'])->name('tenant.reports.index');
            Route::get('/reports/advanced', [\App\Http\Controllers\Tenant\ReportController::class, 'advanced'])->name('tenant.reports.advanced');
            Route::get('/reports/analytics', [\App\Http\Controllers\Tenant\ReportController::class, 'analytics'])->name('tenant.reports.analytics');
            Route::get('/reports/export/csv', [\App\Http\Controllers\Tenant\ReportController::class, 'exportCsv'])->name('tenant.reports.export.csv');
            Route::get('/reports/export/pdf', [\App\Http\Controllers\Tenant\ReportController::class, 'exportPdf'])->name('tenant.reports.export.pdf');
            Route::get('/activity', [\App\Http\Controllers\Tenant\ActivityLogController::class, 'index'])->name('tenant.activity.index');
            Route::get('/notifications/feed', [\App\Http\Controllers\Tenant\NotificationController::class, 'feed'])->name('tenant.notifications.feed');
            Route::get('/payment', [\App\Http\Controllers\Tenant\PaymentController::class, 'portal'])->name('tenant.payment.portal');
            Route::get('/payment/upgrade-quote', [\App\Http\Controllers\Tenant\PaymentController::class, 'upgradeQuote'])->name('tenant.payment.upgrade-quote');
            Route::post('/payment/upgrade-request', [\App\Http\Controllers\Tenant\PaymentController::class, 'submitUpgradeRequest'])->name('tenant.payment.upgrade-request');
            Route::get('/support', [\App\Http\Controllers\Tenant\SupportController::class, 'index'])->name('tenant.support.index');
            Route::post('/support/tickets', [\App\Http\Controllers\Tenant\SupportController::class, 'store'])->name('tenant.support.tickets.store');
            Route::get('/domains', [\App\Http\Controllers\Tenant\DomainController::class, 'index'])->name('tenant.domains.index');
            Route::post('/domains', [\App\Http\Controllers\Tenant\DomainController::class, 'store'])->name('tenant.domains.store');
            Route::delete('/domains/{domain}', [\App\Http\Controllers\Tenant\DomainController::class, 'destroy'])->name('tenant.domains.destroy');
            Route::post('/domains/{domain}/primary', [\App\Http\Controllers\Tenant\DomainController::class, 'setPrimary'])->name('tenant.domains.primary');
            Route::get('/staff', [\App\Http\Controllers\Tenant\TenantUserController::class, 'index'])->name('tenant.staff.index');
            Route::get('/staff/create', [\App\Http\Controllers\Tenant\TenantUserController::class, 'create'])->name('tenant.staff.create');
            Route::post('/staff', [\App\Http\Controllers\Tenant\TenantUserController::class, 'store'])->name('tenant.staff.store');
            Route::get('/staff/{member}', [\App\Http\Controllers\Tenant\TenantUserController::class, 'edit'])->name('tenant.staff.edit')->where('member', '[0-9]+');
            Route::patch('/staff/{member}', [\App\Http\Controllers\Tenant\TenantUserController::class, 'update'])->name('tenant.staff.update')->where('member', '[0-9]+');
            Route::delete('/staff/{member}', [\App\Http\Controllers\Tenant\TenantUserController::class, 'destroy'])->name('tenant.staff.destroy')->where('member', '[0-9]+');
            Route::get('/rbac', [\App\Http\Controllers\Tenant\TenantRbacController::class, 'index'])->name('tenant.rbac.index');
            Route::post('/rbac/initialize', [\App\Http\Controllers\Tenant\TenantRbacController::class, 'initialize'])->name('tenant.rbac.initialize');
            Route::patch('/rbac/roles/{rbacRole}', [\App\Http\Controllers\Tenant\TenantRbacController::class, 'update'])->name('tenant.rbac.update')->where('rbacRole', '[0-9]+');
            Route::redirect('/guests', '/users', 301);
            Route::get('/users', [\App\Http\Controllers\Tenant\GuestUserController::class, 'index'])->name('tenant.users.index');
            Route::patch('/users/{guest}/role', [\App\Http\Controllers\Tenant\GuestUserController::class, 'updateRole'])->name('tenant.users.update-role')->where('guest', '[0-9]+');
            Route::get('/settings', [\App\Http\Controllers\Tenant\TenantSettingsController::class, 'index'])->name('tenant.settings.index');
            Route::patch('/settings', [\App\Http\Controllers\Tenant\TenantSettingsController::class, 'update'])->name('tenant.settings.update');
            Route::get('/profile', [ProfileController::class, 'edit'])->name('tenant.profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('tenant.profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('tenant.profile.destroy');
            Route::post('/profile/send-verification', [ProfileController::class, 'sendVerification'])->name('tenant.verification.send');
            Route::put('/password', [\App\Http\Controllers\Auth\tenantAuthController\PasswordController::class, 'update'])->name('tenant.password.update');
            Route::post('/logout', [\App\Http\Controllers\Auth\LogoutController::class, 'tenant'])->name('tenant.logout');
        });

        Route::middleware(['auth:regular_user', 'tenant.customer.rbac'])->group(function () {
            Route::get('/user/dashboard', [DashboardController::class, 'userIndex'])->name('tenant.user.dashboard');
            Route::get('/user/notifications/feed', [\App\Http\Controllers\TenantUser\NotificationController::class, 'feed'])->name('tenant.user.notifications.feed');
            Route::get('/user/bookings', [\App\Http\Controllers\TenantUser\BookingController::class, 'index'])->name('tenant.user.bookings.index');
            Route::put('/user/bookings/{booking}', [\App\Http\Controllers\TenantUser\BookingController::class, 'update'])->name('tenant.user.bookings.update');
            Route::post('/user/bookings/{booking}/pay-gcash', [\App\Http\Controllers\TenantUser\PayMongoBookingPaymentController::class, 'start'])->name('tenant.user.bookings.pay-gcash')->where('booking', '[0-9]+');
            Route::get('/user/bookings/gcash-return', [\App\Http\Controllers\TenantUser\PayMongoBookingPaymentController::class, 'returnPage'])->name('tenant.user.bookings.gcash-return');
            Route::post('/user/bookings/{booking}/upload-proof', [\App\Http\Controllers\TenantUser\BookingController::class, 'uploadProof'])->name('tenant.user.bookings.upload-proof');
            Route::get('/user/profile', [\App\Http\Controllers\tenantUserControlllers\ProfileController::class, 'edit'])->name('tenant.user.profile.edit');
            Route::patch('/user/profile', [\App\Http\Controllers\tenantUserControlllers\ProfileController::class, 'update'])->name('tenant.user.profile.update');
            Route::delete('/user/profile', [\App\Http\Controllers\tenantUserControlllers\ProfileController::class, 'destroy'])->name('tenant.user.profile.destroy');
            Route::post('/user/profile/send-verification', [\App\Http\Controllers\tenantUserControlllers\ProfileController::class, 'sendVerification'])->name('tenant.user.verification.send');
            Route::put('/user/password', [\App\Http\Controllers\Auth\tenantUserAuthController\PasswordController::class, 'update'])->name('tenant.user.password.update');
            Route::post('/user/logout', [\App\Http\Controllers\Auth\LogoutController::class, 'user'])->name('tenant.user.logout');
        });
    });
