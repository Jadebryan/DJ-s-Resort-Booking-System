<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\TenantModel\Tenant as TenantStaffUser;
use App\Services\TenantRbacService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantStaffRbac
{
    public function __construct(
        private readonly TenantRbacService $rbac
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var TenantStaffUser|null $user */
        $user = $request->user('tenant');
        if (! $user) {
            return $next($request);
        }

        $route = $request->route();
        $name = $route?->getName();
        if (! is_string($name) || $name === '') {
            return $next($request);
        }

        $public = [
            'tenant.profile.edit',
            'tenant.profile.update',
            'tenant.profile.destroy',
            'tenant.verification.send',
            'tenant.password.update',
            'tenant.logout',
        ];
        if (in_array($name, $public, true)) {
            return $next($request);
        }

        if (! $this->rbac->rbacTablesReady()) {
            return $next($request);
        }

        $map = config('tenant_rbac.staff_route_permissions', []);
        if (! isset($map[$name])) {
            return $next($request);
        }

        [$resource, $action] = $map[$name];
        if ($this->rbac->staffCan($user, $resource, $action)) {
            return $next($request);
        }

        return redirect()
            ->route('tenant.dashboard')
            ->with('error', __('You do not have permission to access that page.'));
    }
}
