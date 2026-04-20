<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Support\TenantStaffPermissionDeniedResponse;
use App\Models\TenantModel\Tenant as TenantStaffUser;
use App\Services\TenantRbacService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        [$title, $message] = $this->denialCopy($resource, $action);

        return TenantStaffPermissionDeniedResponse::make($request, $title, $message);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function denialCopy(string $resource, string $action): array
    {
        $area = match ($resource) {
            'dashboard' => __('the dashboard'),
            'rooms' => __('Rooms'),
            'bookings' => __('Bookings'),
            'reports' => __('Reports'),
            'branding' => __('Branding'),
            'staff' => __('Staff accounts'),
            'domains' => __('Domains'),
            'settings' => __('Settings'),
            'activity' => __('Activity log'),
            'payment' => __('Billing & payment'),
            'rbac' => __('Access control'),
            'guests' => __('Guest / portal users'),
            default => Str::headline(str_replace('_', ' ', $resource)),
        };

        $verb = match ($action) {
            'read' => __('view'),
            'create' => __('create'),
            'update' => __('change'),
            'delete' => __('remove'),
            'export' => __('export'),
            'confirm' => __('confirm'),
            'cancel' => __('cancel'),
            default => Str::headline(str_replace('_', ' ', $action)),
        };

        $title = __('You don’t have access');
        $message = __('Your staff role does not include permission to :verb :area. If you need this, ask the resort owner to update your permission set (Staff → permission set, or Access control).', [
            'verb' => $verb,
            'area' => $area,
        ]);

        return [$title, $message];
    }
}
