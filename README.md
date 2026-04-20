# DJs Resort Booking System

Multi-tenant resort booking platform built with Laravel, Tailwind CSS, Alpine.js, and MySQL.

## Overview

This system supports three major roles:

- **Superadmin**: manages tenants, subscriptions, approvals, and platform-wide operations.
- **Tenant (resort owner/staff)**: manages rooms, branding, bookings, users/roles, and payment workflow.
- **Regular user / guest**: browses resort landing pages and submits bookings.

The app uses domain-aware tenant routing and tenant-specific data isolation.

## Key Features

- Domain-based tenant routing (`routes/usingDomain.php`)
- Multi-auth flows (admin, tenant staff, tenant regular user)
- Tenant branding and customizable public landing page
- Public booking flow with room listings and booking modal/form
- Tenant registration with payment submission and approval process
- Contextual action feedback (button spinner, localized form overlays, success/error toasts)

## UX Loading Feedback (Updated)

Global top progress feedback was replaced with localized, action-specific feedback.

- Reusable components:
  - `resources/views/components/form-with-busy.blade.php`
  - `resources/views/components/busy-submit.blade.php`
  - `resources/views/components/spinner.blade.php`
- Alpine state helper:
  - `resources/js/app.js` (`formWithBusy`)
- Livewire top navigate progress bar disabled:
  - `config/livewire.php` -> `navigate.show_progress_bar = false`

This ensures users see exactly what is processing (e.g., *Signing in...*, *Submitting...*, *Saving...*) near the action they triggered.

## Tech Stack

- Laravel
- Blade templates
- Alpine.js
- Tailwind CSS
- MySQL
- Livewire (available in project)

## Local Setup

### 1) Clone and install

```bash
composer install
npm install
```

### 2) Environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` database/mail/app values as needed.

### 3) Database

```bash
php artisan migrate
```

If your flow requires tenant-side schema setup, run the tenant migration command(s) used in your environment.

### 4) Build assets

```bash
npm run dev
```

### 5) Run app

```bash
php artisan serve
```

## Routing Notes

- Central/public routes: `routes/web.php`
- Tenant domain routes: `routes/usingDomain.php`
- Auth route files: `routes/auth.php`, `routes/tenant.php`

## Repository

GitHub: https://github.com/Jadebryan/DJ-s-Resort-Booking-System

## License

This project is open-sourced under the MIT license.
