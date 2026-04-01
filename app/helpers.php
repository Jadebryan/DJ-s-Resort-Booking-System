<?php

declare(strict_types=1);

if (! function_exists('tenant_primary_domain_storage')) {
    /**
     * Canonical value for tenant_domains.domain and registration primary_domain: strips URL junk and
     * removes a trailing ".{tenant_host_suffix}" so the DB holds jeddsresort, not jeddsresort.localhost.
     */
    function tenant_primary_domain_storage(string $raw): string
    {
        $domain = strtolower(trim($raw));
        $domain = (string) preg_replace('#^https?://#i', '', $domain);
        $domain = rtrim($domain, '/');
        $stripped = preg_replace('/:\d+$/', '', $domain);
        $domain = $stripped !== null && $stripped !== '' ? $stripped : $domain;

        $suffix = strtolower(trim((string) config('tenancy.tenant_host_suffix', 'localhost')));
        if ($suffix !== '' && str_ends_with($domain, '.'.$suffix)) {
            $domain = substr($domain, 0, -strlen($suffix) - 1);
        }

        return $domain;
    }
}

if (! function_exists('normalize_tenant_primary_domain')) {
    /** @deprecated Use tenant_primary_domain_storage() */
    function normalize_tenant_primary_domain(string $raw): string
    {
        return tenant_primary_domain_storage($raw);
    }
}

if (! function_exists('tenant_browser_hostname')) {
    /**
     * Hostname for URLs and the browser (append tenant_host_suffix when the stored value is a single label).
     */
    function tenant_browser_hostname(string $stored): string
    {
        $stored = strtolower(trim($stored));
        $suffix = strtolower(trim((string) config('tenancy.tenant_host_suffix', 'localhost')));
        if ($suffix !== '' && ! str_contains($stored, '.')) {
            return $stored.'.'.$suffix;
        }

        return $stored;
    }
}

if (! function_exists('tenant_domain_preferred_label')) {
    /**
     * Display / form value for a stored or legacy domain row (strips configured suffix if still present).
     */
    function tenant_domain_preferred_label(string $host): string
    {
        return tenant_primary_domain_storage($host);
    }
}

if (! function_exists('tenant_path_prefix')) {
    /**
     * Tenant URLs are always host-root paths (no /{slug} prefix).
     */
    function tenant_path_prefix(): string
    {
        return '';
    }
}

if (! function_exists('tenant_url')) {
    /**
     * Absolute URL on the current tenant host for a path like "bookings" or "/rooms/create".
     */
    function tenant_url(string $path = '/'): string
    {
        $path = trim($path, '/');
        if ($path === '') {
            return url('/');
        }

        return url('/'.$path);
    }
}

if (! function_exists('absolute_url_for_tenant_host')) {
    /**
     * Build an absolute URL to a tenant hostname using the same scheme and non-default port as APP_URL
     * (so http://localhost:8000 and redirects to http://tenant.localhost:8000 work with php artisan serve).
     */
    function absolute_url_for_tenant_host(string $tenantHost, string $path = '/'): string
    {
        $tenantHost = tenant_browser_hostname($tenantHost);

        $parsed = parse_url(config('app.url')) ?: [];
        $scheme = $parsed['scheme'] ?? 'https';
        $port = $parsed['port'] ?? null;

        if ($port === null && ! app()->runningInConsole()) {
            $req = request();
            $reqScheme = $req->getScheme();
            $reqPort = $req->getPort();
            $defaultForReq = $reqScheme === 'https' ? 443 : 80;
            if ((int) $reqPort !== $defaultForReq) {
                $port = $reqPort;
                $scheme = $reqScheme;
            }
        }

        $defaultPort = $scheme === 'https' ? 443 : 80;
        $portSuffix = ($port !== null && (int) $port !== $defaultPort) ? ':'.$port : '';
        $path = '/'.ltrim($path, '/');

        return $scheme.'://'.$tenantHost.$portSuffix.$path;
    }
}

if (! function_exists('central_route')) {
    /**
     * Absolute URL for a named route on the central app host (config('app.url')).
     * Use on tenant hostnames where route() would otherwise keep the current domain.
     */
    function central_route(string $name, mixed $parameters = []): string
    {
        $path = route($name, $parameters, false);

        return rtrim((string) config('app.url'), '/').(str_starts_with($path, '/') ? $path : '/'.$path);
    }
}

if (! function_exists('current_tenant')) {
    function current_tenant(): ?\App\Models\Tenant
    {
        $t = request()->attributes->get('tenant');
        if ($t instanceof \App\Models\Tenant) {
            return $t;
        }

        $host = request()->getHost();

        return \App\Models\TenantDomain::forRequestHost($host)?->tenant;
    }
}

if (! function_exists('tenant_staff_can')) {
    function tenant_staff_can(string $resource, string $action): bool
    {
        $user = auth('tenant')->user();
        if (! $user instanceof \App\Models\TenantModel\Tenant) {
            return false;
        }

        return app(\App\Services\TenantRbacService::class)->staffCan($user, $resource, $action);
    }
}

if (! function_exists('tenant_rbac_ready')) {
    function tenant_rbac_ready(): bool
    {
        try {
            return app(\App\Services\TenantRbacService::class)->rbacTablesReady();
        } catch (\Throwable) {
            return false;
        }
    }
}
