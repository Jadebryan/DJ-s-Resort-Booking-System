<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MaintenanceTicket;
use App\Models\Tenant;
use App\Models\TenantPlanUpgradeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    public function feed(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            abort(404);
        }

        $isAdminTenantUser = auth('tenant')->check() && auth('tenant')->user()->role === 'admin';
        $plan = $tenant->loadMissing('plan')->plan;
        $canOpenActivity = $plan && is_array($plan->features) && in_array('activity_logs', $plan->features, true);

        $activityItems = collect();
        if ($canOpenActivity && Schema::connection('tenant')->hasTable('activity_logs')) {
            try {
                $activityItems = ActivityLog::query()
                    ->latest('created_at')
                    ->limit(8)
                    ->get(['action', 'description', 'created_at'])
                    ->map(function ($row) use ($isAdminTenantUser) {
                        return [
                            'action' => (string) $row->action,
                            'description' => (string) ($row->description ?: 'Activity update'),
                            'created_at' => optional($row->created_at)?->toIso8601String(),
                            'time_human' => optional($row->created_at)?->diffForHumans(),
                            'url' => $this->routeForAction((string) $row->action, $isAdminTenantUser),
                        ];
                    });
            } catch (\Throwable) {
                $activityItems = collect();
            }
        }

        $upgradeItems = TenantPlanUpgradeRequest::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->latest('updated_at')
            ->limit(4)
            ->get()
            ->map(function (TenantPlanUpgradeRequest $row) {
                $when = $row->reviewed_at ?? $row->updated_at ?? $row->created_at;
                $status = (string) $row->status;
                $msg = match ($status) {
                    'pending' => 'Upgrade request is pending review.',
                    'approved' => 'Upgrade request was approved.',
                    'rejected' => 'Upgrade request was rejected.',
                    default => 'Upgrade request update.',
                };

                if ($status === 'rejected' && $row->review_notes) {
                    $msg .= ' Reason: ' . trim((string) $row->review_notes);
                }

                return [
                    'action' => 'billing.upgrade.' . $status,
                    'description' => $msg,
                    'created_at' => optional($when)?->toIso8601String(),
                    'time_human' => optional($when)?->diffForHumans(),
                    'url' => tenant_url('/payment'),
                ];
            });

        $supportPrefix = 'tenant#' . $tenant->id . ' ';
        $supportItems = MaintenanceTicket::query()
            ->where('status', MaintenanceTicket::STATUS_OPEN)
            ->where('related_tenant', 'like', $supportPrefix . '%')
            ->latest('updated_at')
            ->limit(6)
            ->get()
            ->map(function (MaintenanceTicket $row) {
                return [
                    'action' => 'support.ticket.open',
                    'description' => __('Support ticket submitted: :t', ['t' => $row->title]),
                    'created_at' => optional($row->updated_at)?->toIso8601String(),
                    'time_human' => optional($row->updated_at)?->diffForHumans(),
                    'url' => tenant_url('/support'),
                ];
            });

        $items = $supportItems
            ->concat($activityItems)
            ->concat($upgradeItems)
            ->sortByDesc(fn (array $item) => $item['created_at'] ?? '')
            ->take(8)
            ->values();

        return response()->json([
            'count' => $items->count(),
            'items' => $items,
            'can_open_activity' => $canOpenActivity,
        ]);
    }

    private function routeForAction(string $action, bool $isAdminTenantUser): string
    {
        return match (true) {
            str_starts_with($action, 'booking.') => tenant_url('/bookings'),
            str_starts_with($action, 'room.') => tenant_url('/rooms'),
            str_starts_with($action, 'staff.') => $isAdminTenantUser ? tenant_url('/staff') : tenant_url('/dashboard'),
            str_starts_with($action, 'domain.') => $isAdminTenantUser ? tenant_url('/domains') : tenant_url('/dashboard'),
            str_starts_with($action, 'branding.') => $isAdminTenantUser ? tenant_url('/branding') : tenant_url('/dashboard'),
            str_starts_with($action, 'billing.upgrade.') => tenant_url('/payment'),
            default => tenant_url('/activity'),
        };
    }
}

