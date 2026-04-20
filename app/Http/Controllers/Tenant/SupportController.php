<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceTicket;
use App\Models\Tenant;
use App\Support\InputRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->attributes->get('tenant');
        $tenantId = $tenant instanceof Tenant ? (int) $tenant->id : 0;

        $prefix = $tenantId > 0 ? ('tenant#' . $tenantId . ' ') : '';
        $tickets = $prefix !== ''
            ? MaintenanceTicket::query()
                ->where('related_tenant', 'like', $prefix . '%')
                ->orderByDesc('updated_at')
                ->get()
            : collect();

        $ticketCounts = [
            MaintenanceTicket::STATUS_OPEN => $tickets->where('status', MaintenanceTicket::STATUS_OPEN)->count(),
            MaintenanceTicket::STATUS_IN_PROGRESS => $tickets->where('status', MaintenanceTicket::STATUS_IN_PROGRESS)->count(),
            MaintenanceTicket::STATUS_RESOLVED => $tickets->where('status', MaintenanceTicket::STATUS_RESOLVED)->count(),
        ];

        return view('Tenant.support.index', compact('tickets', 'ticketCounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', __('Could not resolve resort context.'));
        }

        $validated = $request->validate([
            '_from' => ['nullable', 'string'],
            'title' => InputRules::title(255, true),
            'priority' => ['required', Rule::in(array_keys(MaintenanceTicket::priorityLabels()))],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        MaintenanceTicket::query()->create([
            'title' => $validated['title'],
            'priority' => $validated['priority'],
            'status' => MaintenanceTicket::STATUS_OPEN,
            'related_tenant' => 'tenant#' . $tenant->id . ' ' . $tenant->appDisplayName(),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('tenant.support.index')
            ->with('success', __('Support ticket submitted.'))
            ->with('openSupportTicketModal', false);
    }
}

