<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    protected function tenantHasPremiumFeature(Request $request, string $feature): bool
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            return false;
        }
        $plan = $tenant->loadMissing('plan')->plan;
        if (!$plan) {
            return false;
        }
        return is_array($plan->features) && in_array($feature, $plan->features);
    }

    protected function tenantCanExport(Request $request): bool
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            return false;
        }
        $plan = $tenant->loadMissing('plan')->plan;
        if (!$plan) {
            return false;
        }
        return is_array($plan->features) && in_array('reports_pdf_csv', $plan->features);
    }

    public function index(Request $request): View
    {
        $bookings = Booking::with('room')->orderBy('check_in', 'desc')->get();
        $canExport = $this->tenantCanExport($request);
        $canUseAnalytics = $this->tenantHasPremiumFeature($request, 'revenue_analytics');
        $canUseActivityLog = $this->tenantHasPremiumFeature($request, 'activity_logs');

        $totalBookings = $bookings->count();
        $pending = $bookings->where('status', 'pending')->count();
        $confirmed = $bookings->where('status', 'confirmed')->count();
        $cancelled = $bookings->where('status', 'cancelled')->count();

        $revenue = $bookings
            ->where('status', 'confirmed')
            ->sum(function (Booking $b) {
                if (!$b->room) {
                    return 0;
                }
                $nights = $b->check_in->diffInDays($b->check_out);
                return $nights * (float) $b->room->price_per_night;
            });

        $confirmedBookings = $bookings->where('status', 'confirmed');
        $revenueByRoom = $confirmedBookings
            ->groupBy('room_id')
            ->map(function ($group) {
                $room = $group->first()->room;
                $total = $group->sum(function (Booking $b) {
                    if (!$b->room) {
                        return 0;
                    }
                    $nights = $b->check_in->diffInDays($b->check_out);
                    return $nights * (float) $b->room->price_per_night;
                });
                return [
                    'room' => $room,
                    'count' => $group->count(),
                    'revenue' => $total,
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        return view('Tenant.reports.index', [
            'bookings' => $bookings,
            'totalBookings' => $totalBookings,
            'pending' => $pending,
            'confirmed' => $confirmed,
            'cancelled' => $cancelled,
            'revenue' => $revenue,
            'revenueByRoom' => $revenueByRoom,
            'canExport' => $canExport,
            'canUseAnalytics' => $canUseAnalytics,
            'canUseActivityLog' => $canUseActivityLog,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse|Response|RedirectResponse
    {
        if (!$this->tenantCanExport($request)) {
            return redirect()
                ->route('tenant.reports.index')
                ->with('error', 'Export is available on Standard and Premium plans.');
        }

        $bookings = Booking::with('room')->orderBy('check_in', 'desc')->get();

        $filename = 'bookings-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($bookings) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Room', 'Guest Name', 'Guest Email', 'Guest Phone', 'Check-in', 'Check-out', 'Nights', 'Status', 'Amount (₱)',
            ]);
            foreach ($bookings as $b) {
                $nights = $b->check_in->diffInDays($b->check_out);
                $amount = $b->status === 'confirmed' && $b->room
                    ? $nights * (float) $b->room->price_per_night
                    : 0;
                fputcsv($out, [
                    $b->room?->name ?? '',
                    $b->guest_name ?? $b->user?->name ?? '',
                    $b->guest_email ?? $b->user?->email ?? '',
                    $b->guest_phone ?? '',
                    $b->check_in?->format('Y-m-d'),
                    $b->check_out?->format('Y-m-d'),
                    $nights,
                    $b->status,
                    number_format($amount, 2),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportPdf(Request $request): View|RedirectResponse
    {
        if (!$this->tenantCanExport($request)) {
            return redirect()
                ->route('tenant.reports.index')
                ->with('error', 'Export is available on Standard and Premium plans.');
        }

        $bookings = Booking::with('room')->orderBy('check_in', 'desc')->get();
        $totalBookings = $bookings->count();
        $pending = $bookings->where('status', 'pending')->count();
        $confirmed = $bookings->where('status', 'confirmed')->count();
        $cancelled = $bookings->where('status', 'cancelled')->count();
        $revenue = $bookings->where('status', 'confirmed')->sum(function (Booking $b) {
            if (!$b->room) {
                return 0;
            }
            return $b->check_in->diffInDays($b->check_out) * (float) $b->room->price_per_night;
        });
        $revenueByRoom = $bookings->where('status', 'confirmed')
            ->groupBy('room_id')
            ->map(function ($group) {
                $room = $group->first()->room;
                $total = $group->sum(fn (Booking $b) => $b->room ? $b->check_in->diffInDays($b->check_out) * (float) $b->room->price_per_night : 0);
                return ['room' => $room, 'count' => $group->count(), 'revenue' => $total];
            })
            ->sortByDesc('revenue')
            ->values();

        return view('Tenant.reports.print', [
            'bookings' => $bookings,
            'totalBookings' => $totalBookings,
            'pending' => $pending,
            'confirmed' => $confirmed,
            'cancelled' => $cancelled,
            'revenue' => $revenue,
            'revenueByRoom' => $revenueByRoom,
            'generatedAt' => now(),
        ]);
    }

    public function analytics(Request $request): View|RedirectResponse
    {
        if (!$this->tenantHasPremiumFeature($request, 'revenue_analytics')) {
            return redirect()
                ->route('tenant.reports.index')
                ->with('error', 'Revenue analytics is not enabled in your current subscription.');
        }

        $bookings = Booking::with('room')
            ->where('status', 'confirmed')
            ->orderBy('check_in')
            ->get();

        $revenueByDay = [];
        $revenueByMonth = [];
        foreach ($bookings as $b) {
            if (!$b->room) {
                continue;
            }
            $nights = $b->check_in->diffInDays($b->check_out);
            $amount = $nights * (float) $b->room->price_per_night;
            $date = $b->check_in->copy();
            for ($i = 0; $i < $nights; $i++) {
                $key = $date->format('Y-m-d');
                $revenueByDay[$key] = ($revenueByDay[$key] ?? 0) + (float) $b->room->price_per_night;
                $monthKey = $date->format('Y-m');
                $revenueByMonth[$monthKey] = ($revenueByMonth[$monthKey] ?? 0) + (float) $b->room->price_per_night;
                $date->addDay();
            }
        }
        ksort($revenueByDay);
        ksort($revenueByMonth);

        return view('Tenant.reports.analytics', [
            'revenueByDay' => $revenueByDay,
            'revenueByMonth' => $revenueByMonth,
        ]);
    }
}
