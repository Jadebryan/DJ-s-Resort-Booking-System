<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Support\ResortMailInitials;
use App\Models\TenantRegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantApplicationApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TenantRegistrationRequest $registrationRequest,
        public Tenant $tenant,
        public string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __(':app — Your resort is approved (sign in to your dashboard)', ['app' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.tenant-registration.approved',
            with: [
                'mailHeaderResortInitials' => ResortMailInitials::from($this->tenant->tenant_name),
                'mailHeaderResortName' => $this->tenant->tenant_name,
            ],
        );
    }
}
