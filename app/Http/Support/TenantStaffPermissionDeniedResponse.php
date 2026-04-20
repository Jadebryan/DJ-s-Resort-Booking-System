<?php

declare(strict_types=1);

namespace App\Http\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TenantStaffPermissionDeniedResponse
{
    public static function make(Request $request, string $title, string $message, int $status = Response::HTTP_FORBIDDEN): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'title' => $title,
                'code' => 'staff_permission_denied',
            ], $status);
        }

        return response()->view('Tenant.errors.permission-denied', [
            'title' => $title,
            'message' => $message,
        ], $status);
    }
}
