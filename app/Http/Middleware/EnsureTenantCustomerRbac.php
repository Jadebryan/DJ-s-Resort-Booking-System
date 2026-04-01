<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\TenantUserModel\RegularUser;
use App\Services\TenantRbacService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantCustomerRbac
{
    public function __construct(
        private readonly TenantRbacService $rbac
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var RegularUser|null $user */
        $user = $request->user('regular_user');
        if (! $user) {
            return $next($request);
        }

        $route = $request->route();
        $name = $route?->getName();
        if (! is_string($name) || $name === '') {
            return $next($request);
        }

        $public = [
            'tenant.user.logout',
        ];
        if (in_array($name, $public, true)) {
            return $next($request);
        }

        if (! $this->rbac->rbacTablesReady()) {
            return $next($request);
        }

        $map = config('tenant_rbac.customer_route_permissions', []);
        if (! isset($map[$name])) {
            return $next($request);
        }

        [$resource, $action] = $map[$name];
        if ($this->rbac->customerCan($user, $resource, $action)) {
            return $next($request);
        }

        return redirect()
            ->route('tenant.user.dashboard')
            ->with('error', __('You do not have permission to access that page.'));
    }
}
