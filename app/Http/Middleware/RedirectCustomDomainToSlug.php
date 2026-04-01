<?php

namespace App\Http\Middleware;

use App\Models\TenantDomain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectCustomDomainToSlug
{
    public function handle(Request $request, Closure $next): Response
    {
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?: $request->getHost();
        $currentHost = $request->getHost();

        if (strtolower($currentHost) === strtolower($appHost)) {
            return $next($request);
        }

        $domain = TenantDomain::forRequestHost($currentHost);
        if (!$domain || !$domain->tenant) {
            return $next($request);
        }
        
        // Domain-first mode: keep tenant domain URL, no forced redirect to slug path.
        return $next($request);
    }
}
