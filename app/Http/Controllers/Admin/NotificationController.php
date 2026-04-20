<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceTicket;
use App\Models\TenantPlanUpgradeRequest;
use App\Models\TenantRegistrationRequest;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function feed(): JsonResponse
    {
        $maintenanceItems = MaintenanceTicket::query()
            ->where('status', MaintenanceTicket::STATUS_OPEN)
            ->latest('updated_at')
            ->limit(8)
            ->get()
            ->map(function (MaintenanceTicket $row) {
                $tenant = $row->related_tenant ? (' · ' . $row->related_tenant) : '';

                return [
                    'kind' => 'maintenance',
                    'description' => __('Open ticket: :t', ['t' => $row->title]) . $tenant,
                    'time_human' => optional($row->updated_at)->diffForHumans(),
                    'created_at' => optional($row->updated_at)?->toIso8601String(),
                    'url' => route('admin.maintenance'),
                ];
            });

        $upgradeItems = TenantPlanUpgradeRequest::query()
            ->with(['tenant', 'requestedPlan'])
            ->latest('updated_at')
            ->limit(8)
            ->get()
            ->map(function (TenantPlanUpgradeRequest $row) {
                $tenantName = $row->tenant?->tenant_name ?? 'Tenant';
                $planName = $row->requestedPlan?->name ?? 'plan';
                $status = (string) $row->status;

                $description = match ($status) {
                    'pending' => $tenantName . ' requested upgrade to ' . $planName . '.',
                    'approved' => 'Upgrade approved for ' . $tenantName . ' (' . $planName . ').',
                    'rejected' => 'Upgrade rejected for ' . $tenantName . ' (' . $planName . ').',
                    default => 'Upgrade request updated for ' . $tenantName . '.',
                };

                return [
                    'kind' => 'upgrade',
                    'description' => $description,
                    'time_human' => optional($row->updated_at)->diffForHumans(),
                    'created_at' => optional($row->updated_at)?->toIso8601String(),
                    'url' => route('admin.payments'),
                ];
            });

        $registrationItems = TenantRegistrationRequest::query()
            ->latest('updated_at')
            ->limit(8)
            ->get()
            ->map(function (TenantRegistrationRequest $row) {
                $tenantName = $row->tenant_name ?: 'Tenant signup';
                $status = (string) $row->status;

                $description = match ($status) {
                    TenantRegistrationRequest::STATUS_PENDING_REVIEW => $tenantName . ' is awaiting signup review.',
                    TenantRegistrationRequest::STATUS_AWAITING_PAYMENT => $tenantName . ' is awaiting payment.',
                    TenantRegistrationRequest::STATUS_REJECTED => $tenantName . ' signup was rejected.',
                    TenantRegistrationRequest::STATUS_APPROVED => $tenantName . ' signup was approved.',
                    default => $tenantName . ' signup status updated.',
                };

                return [
                    'kind' => 'registration',
                    'description' => $description,
                    'time_human' => optional($row->updated_at)->diffForHumans(),
                    'created_at' => optional($row->updated_at)?->toIso8601String(),
                    'url' => route('admin.tenant-registrations.index'),
                ];
            });

        $items = $maintenanceItems
            ->concat($upgradeItems)
            ->concat($registrationItems)
            ->sortByDesc(fn (array $item) => $item['created_at'] ?? '')
            ->take(10)
            ->values();

        return response()->json([
            'count' => $items->count(),
            'items' => $items,
        ]);
    }
}

