<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\TenantRegisterController;
use App\Http\Controllers\Auth\TenantLoginController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\DashboardController;
use App\Http\Controllers\TenantRegistrationPaymentController;
use App\Http\Controllers\Admin\TenantRegistrationController;

// routes/web.php
use App\Http\Controllers\TenantController;
use App\Http\Controllers\DebugController;

/*
 * Tenant host routes must register before any host-agnostic routes: Laravel matches
 * the first compatible route, and routes without Route::domain() accept every Host.
 */
require __DIR__.'/usingDomain.php';

// Public: browse resorts (tenants) for landing page "Browse demo tenants"
Route::get('/resorts', [TenantController::class, 'publicIndex'])->name('tenants.index');

// Debug routes
Route::get('/debug/tenants', [DebugController::class, 'checkTenants']);
Route::get('/debug/tenant/{slug}', [DebugController::class, 'checkTenant']);
Route::get('/debug/all-tenants-users', [DebugController::class, 'checkAllTenantUsers']);


// Routes for testing
Route::get('/debug/tenant-users/{slug}', function ($slug) {
    $tenant = \App\Models\Tenant::where('slug', $slug)->first();
    if (!$tenant) {
        return response()->json(['error' => 'Tenant not found'], 404);
    }
    
    config(['database.connections.tenant.database' => $tenant->database_name]);
    \Illuminate\Support\Facades\DB::purge('tenant');
    
    $users = \Illuminate\Support\Facades\DB::connection('tenant')->table('tenant_users')->get();
    return response()->json([
        'tenant' => $tenant,
        'database' => $tenant->database_name,
        'user_count' => count($users),
        'users' => $users
    ]);
});

Route::get('/debug/test-login/{slug}/{email}/{password}', function ($slug, $email, $password) {
    $tenant = \App\Models\Tenant::where('slug', $slug)->first();
    if (!$tenant) {
        return response()->json(['error' => 'Tenant not found'], 404);
    }
    
    config(['database.connections.tenant.database' => $tenant->database_name]);
    \Illuminate\Support\Facades\DB::purge('tenant');
    
    $user = \Illuminate\Support\Facades\DB::connection('tenant')->table('tenant_users')->where('email', $email)->first();
    
    if (!$user) {
        return response()->json([
            'status' => 'failed',
            'reason' => 'User not found',
            'email_searched' => $email
        ]);
    }
    
    $passwordMatch = \Illuminate\Support\Facades\Hash::check($password, $user->password);
    
    return response()->json([
        'status' => $passwordMatch ? 'success' : 'failed',
        'reason' => $passwordMatch ? 'Password matches!' : 'Password does not match',
        'user' => $user,
        'password_hash' => $user->password
    ]);
});


Route::get('/', function () {
    return view('landing');
})->name('landing');


// =======================
// ADMIN ROUTES (TOP)
// =======================
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest admin routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store']);
    });

    // Authenticated admin routes
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Auth\LogoutController::class, 'admin'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'adminIndex'])->name('dashboard');

        // Tenant management (Superadmin)
        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}', [TenantController::class, 'edit'])->name('tenants.edit');
        Route::patch('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
        Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
        Route::post('/tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
        Route::post('/tenants/{tenant}/deactivate', [TenantController::class, 'deactivate'])->name('tenants.deactivate');
        Route::post('/tenants/{tenant}/domains', [TenantController::class, 'storeDomain'])->name('tenants.domains.store');
        Route::delete('/tenants/{tenant}/domains/{domain}', [TenantController::class, 'destroyDomain'])->name('tenants.domains.destroy');
        Route::post('/tenants/{tenant}/domains/{domain}/primary', [TenantController::class, 'setPrimaryDomain'])->name('tenants.domains.primary');

        Route::get('/tenant-registrations', [TenantRegistrationController::class, 'index'])->name('tenant-registrations.index');
        Route::post('/tenant-registrations/{registration}/approve', [TenantRegistrationController::class, 'approve'])->name('tenant-registrations.approve');
        Route::post('/tenant-registrations/{registration}/reject', [TenantRegistrationController::class, 'reject'])->name('tenant-registrations.reject');

        // Admin pages (Payments, Maintenance, Reports, Settings)
        Route::get('/notifications/feed', [\App\Http\Controllers\Admin\NotificationController::class, 'feed'])->name('notifications.feed');
        Route::get('/payments', [\App\Http\Controllers\Admin\PageController::class, 'payments'])->name('payments');
        Route::post('/payments/upgrade-requests/{upgradeRequest}/approve', [\App\Http\Controllers\Admin\PageController::class, 'approveUpgradeRequest'])->name('payments.upgrade-requests.approve');
        Route::post('/payments/upgrade-requests/{upgradeRequest}/reject', [\App\Http\Controllers\Admin\PageController::class, 'rejectUpgradeRequest'])->name('payments.upgrade-requests.reject');
        Route::get('/maintenance', [\App\Http\Controllers\Admin\PageController::class, 'maintenance'])->name('maintenance');
        Route::post('/maintenance/tickets', [\App\Http\Controllers\Admin\PageController::class, 'storeMaintenanceTicket'])->name('maintenance.tickets.store');
        Route::patch('/maintenance/tickets/{ticket}', [\App\Http\Controllers\Admin\PageController::class, 'updateMaintenanceTicket'])->name('maintenance.tickets.update');
        Route::get('/reports', [\App\Http\Controllers\Admin\PageController::class, 'reports'])->name('reports');
        Route::get('/subscriptions', [\App\Http\Controllers\Admin\PageController::class, 'subscriptions'])->name('subscriptions.index');
        Route::post('/subscriptions', [\App\Http\Controllers\Admin\PageController::class, 'updateSubscriptions'])->name('subscriptions.update');
        Route::get('/settings', [\App\Http\Controllers\Admin\PageController::class, 'settings'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\PageController::class, 'updateSettings'])->name('settings.update');

        // Admin profile routes
        Route::get('/profile', [\App\Http\Controllers\adminControlller\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [\App\Http\Controllers\adminControlller\ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [\App\Http\Controllers\adminControlller\ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::post('/profile/send-verification', [\App\Http\Controllers\adminControlller\ProfileController::class, 'sendVerification'])->name('verification.send');
        
        // Admin password update
        Route::put('/password', [\App\Http\Controllers\adminControlller\PasswordController::class, 'update'])->name('password.update');
    });
});


// Generic tenant selector routes (show full login/register forms)
Route::get('/tenant/login', function () {
    return view('auth.tenantAuth.login');
})->name('tenant.select.login');

Route::post('/tenant/login', [TenantLoginController::class, 'store']);

Route::get('/tenant/register', [TenantRegisterController::class, 'create'])->name('tenant.select.register');
Route::post('/tenant/register', [TenantRegisterController::class, 'store']);

Route::prefix('tenant/register')->name('tenant.register.')->group(function () {
    Route::get('/payment/{registration:token}', [TenantRegistrationPaymentController::class, 'show'])->name('payment');
    // Avoid 404/blank page if someone opens the POST URL in a new tab or refreshes after submit.
    Route::get('/payment/{registration:token}/manual', function (\App\Models\TenantRegistrationRequest $registration) {
        return redirect()->route('tenant.register.payment', ['registration' => $registration->token]);
    });
    Route::post('/payment/{registration:token}/manual', [TenantRegistrationPaymentController::class, 'submitManual'])->name('payment.manual');
    Route::get('/submitted/{registration:token}', [TenantRegistrationPaymentController::class, 'submitted'])->name('submitted');
    Route::get('/submitted/{registration:token}/status', [TenantRegistrationPaymentController::class, 'submittedStatus'])->name('submitted.status');
});

Route::get('/check-tenants', function () {
    $tenants = \Illuminate\Support\Facades\DB::table('tenants')->select('slug', 'database_name')->get();
    return response()->json($tenants);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Tenant-facing routes (login, book, dashboard, etc.) are registered only on custom
// domains — see routes/usingDomain.php.


require __DIR__.'/auth.php';
