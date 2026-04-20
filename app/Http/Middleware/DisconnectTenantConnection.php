<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class DisconnectTenantConnection
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Ensure no tenant DB connection persists across requests in long-running PHP processes.
        try {
            DB::disconnect('tenant');
        } catch (\Throwable) {
        }

        return $response;
    }
}

