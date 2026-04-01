<?php

namespace App\Mail;

use App\Models\TenantRegistrationRequest;
use App\Support\ResortMailInitials;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewTenantRegistrationForAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TenantRegistrationRequest $registrationRequest
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('New resort signup pending review: :name', ['name' => $this->registrationRequest->tenant_name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.tenant-registration.admin-new',
            with: [
                'mailHeaderResortInitials' => ResortMailInitials::from($this->registrationRequest->tenant_name),
                'mailHeaderResortName' => $this->registrationRequest->tenant_name,
            ],
        );
    }
}
