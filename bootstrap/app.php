<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            \App\Http\Middleware\SetTenantDatabase::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\DisconnectTenantConnection::class,
            \App\Http\Middleware\EnsureActionToast::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/paymongo',
        ]);
        
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'tenant.staff.rbac' => \App\Http\Middleware\EnsureTenantStaffRbac::class,
            'tenant.customer.rbac' => \App\Http\Middleware\EnsureTenantCustomerRbac::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $redirect419 = static function (Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please refresh and try again.'], 419);
            }
            $path = $request->getPathInfo();
            $appHost = strtolower((string) (parse_url(config('app.url'), PHP_URL_HOST) ?: $request->getHost()));
            $onTenantHost = strtolower($request->getHost()) !== $appHost;

            if (str_starts_with($path, '/admin')) {
                return redirect()->route('admin.login')->with('error', 'Session expired. Please sign in again.');
            }
            if ($onTenantHost && str_contains($path, '/user/')) {
                return redirect('/user/login')->with('error', 'Session expired. Please sign in again.');
            }
            if ($onTenantHost) {
                return redirect('/login')->with('error', 'Session expired. Please sign in again.');
            }

            return redirect()->route('landing')->with('error', 'Session expired. Please try again.');
        };

        $exceptions->render(function (TokenMismatchException $e, Request $request) use ($redirect419) {
            return $redirect419($request);
        });

        $exceptions->render(function (HttpException $e, Request $request) use ($redirect419) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            return $redirect419($request);
        });
    })->create();
