<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            $path = $request->getPathInfo();
            $appHost = strtolower((string) (parse_url(config('app.url'), PHP_URL_HOST) ?: $request->getHost()));
            $onTenantHost = strtolower($request->getHost()) !== $appHost;

            if (str_starts_with($path, '/admin')) {
                return route('admin.login');
            }

            if ($onTenantHost && str_contains($path, '/user/')) {
                return url('/user/login');
            }

            if ($onTenantHost) {
                return url('/login');
            }

            return route('landing');
        }

        return null;
    }
}
