<?php

namespace App\Services;

use App\Mail\NewTenantRegistrationForAdmin;
use App\Mail\TenantApplicationSubmitted;
use App\Models\AdminModel\Admin;
use App\Models\TenantRegistrationRequest;
use Illuminate\Support\Facades\Mail;

class TenantRegistrationNotifier
{
    public function notifySubmittedForReview(TenantRegistrationRequest $request): void
    {
        $request->loadMissing('plan');

        Mail::to($request->admin_email)->send(new TenantApplicationSubmitted($request));

        $emails = config('registration.notify_admin_emails');
        if (is_string($emails) && $emails !== '') {
            foreach (array_map('trim', explode(',', $emails)) as $email) {
                if ($email !== '') {
                    Mail::to($email)->send(new NewTenantRegistrationForAdmin($request));
                }
            }

            return;
        }

        foreach (Admin::query()->get(['email']) as $admin) {
            Mail::to($admin->email)->send(new NewTenantRegistrationForAdmin($request));
        }
    }
}
