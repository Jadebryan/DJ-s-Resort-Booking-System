<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\UpdateTenantJob;
use App\Models\Tenant;
use App\Services\PlatformReleaseVersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantSystemUpdateController extends Controller
{
    public function checkUpdate(Request $request, PlatformReleaseVersionService $releases): JsonResponse
    {
        $landlord = current_tenant();
        if (! $landlord instanceof Tenant) {
            return response()->json(['message' => 'Tenant not resolved.'], 404);
        }

        $landlord = Tenant::query()->whereKey($landlord->id)->firstOrFail();
        $latest = $releases->latestSchemaVersion();
        $current = $landlord->version ?? '1.0.0';

        return response()->json([
            'current_version' => $current,
            'latest_version' => $latest,
            'update_available' => version_compare($current, $latest, '<'),
        ]);
    }

    public function applyUpdate(Request $request, PlatformReleaseVersionService $releases): JsonResponse
    {
        $staff = $request->user('tenant');
        if (! $staff || $staff->role !== 'admin') {
            return response()->json(['message' => 'Only tenant administrators can apply updates.'], 403);
        }

        $landlord = current_tenant();
        if (! $landlord instanceof Tenant) {
            return response()->json(['message' => 'Tenant not resolved.'], 404);
        }

        $latest = $releases->latestSchemaVersion();

        if (config('queue.default') === 'sync') {
            UpdateTenantJob::dispatchSync($landlord->id);
            $landlord->refresh();

            return response()->json([
                'queued' => false,
                'current_version' => $landlord->version ?? '1.0.0',
                'latest_version' => $latest,
                'update_available' => version_compare($landlord->version ?? '1.0.0', $latest, '<'),
            ]);
        }

        UpdateTenantJob::dispatch($landlord->id);

        return response()->json([
            'queued' => true,
            'message' => 'Update job has been queued. Check back shortly.',
        ], 202);
    }
}
