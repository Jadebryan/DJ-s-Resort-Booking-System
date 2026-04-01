<?php

namespace App\Http\Controllers\Auth;

use App\Models\Booking;
use App\Models\Plan;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantRegistrationRequest;
use Illuminate\View\View;

class DashboardController
{
    public function adminIndex(): View
    {
        $recentTenants = Tenant::with(['plan', 'domains'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'tenantCount' => Tenant::count(),
            'activeTenantCount' => Tenant::where('is_active', true)->count(),
            'planCount' => Plan::where('is_active', true)->count(),
            'domainCount' => TenantDomain::count(),
            'openSignupCount' => TenantRegistrationRequest::query()
                ->whereIn('status', [
                    TenantRegistrationRequest::STATUS_AWAITING_PAYMENT,
                    TenantRegistrationRequest::STATUS_PENDING_REVIEW,
                ])
                ->count(),
            'recentTenants' => $recentTenants,
        ]);
    }

    public function tenantIndex(): View
    {
        $roomsCount = Room::count();
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $confirmedBookings = Booking::where('status', 'confirmed')->count();
        $cancelledBookings = Booking::where('status', 'cancelled')->count();

        $totalRevenue = (float) Booking::where('status', 'confirmed')
            ->with('room')
            ->get()
            ->sum(function (Booking $b) {
                if (!$b->room || !$b->check_in || !$b->check_out) {
                    return 0;
                }
                $nights = max(1, $b->check_in->diffInDays($b->check_out));
                return $nights * (float) $b->room->price_per_night;
            });

        return view('Tenant.dashboard', [
            'roomsCount' => $roomsCount,
            'totalBookings' => $totalBookings,
            'pendingBookings' => $pendingBookings,
            'confirmedBookings' => $confirmedBookings,
            'cancelledBookings' => $cancelledBookings,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    public function userIndex(): View
    {
        $user = auth('regular_user')->user();
        $totalBookings = Booking::where('regular_user_id', $user->id)->count();
        $pendingBookings = Booking::where('regular_user_id', $user->id)->where('status', 'pending')->count();
        $confirmedBookings = Booking::where('regular_user_id', $user->id)->where('status', 'confirmed')->count();
        $cancelledBookings = Booking::where('regular_user_id', $user->id)->where('status', 'cancelled')->count();
        $rooms = Room::where('is_available', true)->orderBy('name')->get();
        return view('TenantUser.dashboard', [
            'totalBookings' => $totalBookings,
            'pendingBookings' => $pendingBookings,
            'confirmedBookings' => $confirmedBookings,
            'cancelledBookings' => $cancelledBookings,
            'availableRoomsCount' => $rooms->count(),
            'rooms' => $rooms,
        ]);
    }
}
