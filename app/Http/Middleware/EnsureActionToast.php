<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActionToast
{
    /**
     * Add a default success toast when a state-changing action redirects
     * without flashing its own message.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($request->expectsJson()) {
            return $response;
        }

        if (! $response instanceof RedirectResponse) {
            return $response;
        }

        if (! in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        $routeName = (string) optional($request->route())->getName();
        if ($routeName === '' || ! preg_match('/\.(store|update|destroy|approve|reject|activate|deactivate|primary|checkout-subscription|upgrade-request)$/', $routeName)) {
            return $response;
        }

        $session = $request->session();
        if ($session->has('success') || $session->has('error') || $session->has('info') || $session->has('status') || $session->has('errors')) {
            return $response;
        }

        return $response->with('success', __('Update saved successfully.'));
    }
}

