<?php

namespace App\Http\Controllers\Auth;

use App\Models\Booking;
use App\Models\Plan;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantRegistrationRequest;
use App\Support\BookingCalendarGrid;
use App\Support\GuestBookingCalendar;
use App\Support\TenantPlanFeatures;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function tenantIndex(Request $request): View|RedirectResponse
    {
        if (! TenantPlanFeatures::hasRequestFeature($request, 'simple_dashboard')) {
            return redirect()
                ->route('tenant.payment.portal')
                ->with('error', 'Dashboard widgets are not available on your current subscription.');
        }

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

        $dashboardCalendarMonth = Carbon::now()->startOfMonth();
        $startOfMonth = $dashboardCalendarMonth->copy();
        $endOfMonth = $dashboardCalendarMonth->copy()->endOfMonth();
        $monthBookings = Booking::with('room')
            ->where('check_in', '<=', $endOfMonth)
            ->where('check_out', '>=', $startOfMonth)
            ->orderBy('check_in')
            ->get();
        $dashboardCalendarWeeks = BookingCalendarGrid::buildWeeks($startOfMonth, $endOfMonth, $monthBookings);

        return view('Tenant.dashboard', [
            'roomsCount' => $roomsCount,
            'totalBookings' => $totalBookings,
            'pendingBookings' => $pendingBookings,
            'confirmedBookings' => $confirmedBookings,
            'cancelledBookings' => $cancelledBookings,
            'totalRevenue' => $totalRevenue,
            'dashboardCalendarWeeks' => $dashboardCalendarWeeks,
            'dashboardCalendarMonth' => $dashboardCalendarMonth,
            'dashboardMonthBookings' => $monthBookings,
            'tenantHasBookingCalendar' => TenantPlanFeatures::hasRequestFeature($request, 'booking_calendar'),
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
        $gcal = GuestBookingCalendar::currentMonthForUser((int) $user->id);

        return view('TenantUser.dashboard', [
            'totalBookings' => $totalBookings,
            'pendingBookings' => $pendingBookings,
            'confirmedBookings' => $confirmedBookings,
            'cancelledBookings' => $cancelledBookings,
            'availableRoomsCount' => $rooms->count(),
            'rooms' => $rooms,
            'guestDashboardCalendarWeeks' => $gcal['calendarWeeks'],
            'guestDashboardMonthBookings' => $gcal['monthBookings'],
            'guestDashboardCalendarMonth' => $gcal['date'],
        ]);
    }
}
