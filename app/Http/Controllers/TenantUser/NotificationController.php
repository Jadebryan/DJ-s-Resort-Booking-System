<?php

namespace App\Http\Controllers\TenantUser;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function feed(Request $request): JsonResponse
    {
        $userId = auth('regular_user')->id();
        if (! $userId) {
            abort(401);
        }

        $items = Booking::query()
            ->with('room')
            ->where('regular_user_id', $userId)
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(function (Booking $booking) {
                $status = (string) $booking->status;
                $room = $booking->room?->name ?: 'your booking';

                $description = match ($status) {
                    'confirmed' => 'Booking confirmed for ' . $room . '.',
                    'cancelled' => 'Booking cancelled for ' . $room . '.',
                    'pending' => 'Booking request is pending for ' . $room . '.',
                    default => 'Booking updated for ' . $room . '.',
                };

                return [
                    'kind' => 'booking',
                    'description' => $description,
                    'created_at' => optional($booking->updated_at)?->toIso8601String(),
                    'time_human' => optional($booking->updated_at)?->diffForHumans(),
                    'url' => tenant_url('/user/bookings'),
                ];
            })
            ->values();

        return response()->json([
            'count' => $items->count(),
            'items' => $items,
        ]);
    }
}

