System Documentation

System Name: DJs Resort Booking System
Type: Multi-tenant SaaS web application (domain-based tenancy)
Repository Folder: `ResortBookingSystem/`

---

1. Introduction

The DJs Resort Booking System is a web-based, multi-tenant resort booking platform built to help multiple resorts operate independently within a single application. It digitizes the end-to-end booking flow—from room listing and availability awareness to booking requests, confirmation, receipts, and payment recording—while also providing subscription controls and administrative oversight through a central superadmin portal.

The platform separates “central” (platform-wide) operations such as tenant (resort) onboarding, subscription plan management, upgrade approvals, and webhooks from “tenant” (per-resort) operations such as room management, bookings, branding, staff/guest access, and reports.

---

2. Project Overview

The system is implemented using:

- Backend: Laravel (PHP 8.1+), multi-auth guards, notifications
- Multi-tenancy: `stancl/tenancy` with domain-aware tenant routing
- Frontend: Blade + Tailwind CSS + Alpine.js + Vite
- Database:
  - Central/Landlord DB: tenants, domains, admins, plans, subscriptions/upgrade requests, platform settings
  - Tenant DB (per resort): rooms, bookings, tenant staff users, regular users, activity logs, RBAC roles

Central host routes are registered in `routes/web.php`.
Tenant domain routes are registered in `routes/usingDomain.php` and are only served when the request host matches a mapped tenant domain and passes the tenant database resolver middleware.

---

3. Objectives

The primary objective of the DJs Resort Booking System is to improve resort booking efficiency and guest accessibility through digital transformation, while enabling multiple resorts to operate independently under a single platform.

- Provide a public, tenant-branded landing page for each resort with room listings
- Enable guests to create accounts and submit bookings under the correct resort (tenant)
- Give resort staff a dashboard and tools to manage rooms, bookings, guests, reports, and settings
- Support a central superadmin to manage tenants, plans, approvals, and platform operations
- Enforce data isolation between resorts and apply RBAC to reduce unauthorized access
- Support payment workflows including manual proof uploads

---

4. Scope and Limitations

Scope
The system covers essential functionalities related to resort discovery, booking requests, and booking management within a multi-tenant platform. It includes user authentication, role-based access, room and booking management, tenant branding, domain-based tenant routing, payment proof submission, notifications, and reporting features. The system also supports multiple tenants, allowing several resorts to operate independently within the same platform while keeping data isolated per resort.

Limitations
Despite its capabilities, the system has certain limitations:
● It requires a stable internet connection for operation
● Domain-based tenancy requires correct local/production host mapping (DNS / hosts file / reverse proxy)
● Some modules are subscription-feature-gated (e.g., booking calendar, advanced reports, PDF/CSV exports)
● Advanced security features such as multi-factor authentication are not implemented
● Screenshots and development evidence are not generated automatically; they must be captured during testing
● Initial setup may require additional configuration/training for non-technical resort staff (domains, plans, and workflows)

---

5. System Architecture

The system follows a standard web application architecture with central + tenant separation:

- Presentation layer: Blade templates + Tailwind + Alpine (Vite build)
- Application layer: Laravel controllers, middleware, services, notifications, RBAC checks
- Data layer:
  - Central database (platform data)
  - Tenant databases (per-resort data)

5.1 High-level request flow

1. Request arrives with a Host header.
2. Central routes are served on `APP_URL` host.
3. Tenant routes are served on mapped tenant domains using `routes/usingDomain.php`.
4. Middleware resolves the tenant and sets the active tenant database connection:
   - `App\Http\Middleware\SetTenantDatabase`

---

6. Multi-Tenancy Design

The platform uses domain-based tenancy:

- Each resort has one or more mapped domains in the central table `tenant_domains`.
- Tenant domain routing is defined in `routes/usingDomain.php` via `Route::domain('{tenant_domain}')`.
- The tenant context is applied by `SetTenantDatabase`, which:
  - resolves the tenant by hostname (mapped domain),
  - sets `database.connections.tenant.database` to the tenant database name,
  - stores tenant context in session and URL defaults.

6.1 Tenant suspension behavior

If a tenant is inactive (`tenants.is_active = false`), the middleware still allows limited access for renewal:

- Allowed paths include `/login`, password reset flows, and `/payment` so staff can submit renewals.

---

7. Database Design

The database design for this system is structured to support both scalability and efficient data management through the use of a Tenant Database and a Central Application Database. The Tenant Database is responsible for storing and isolating data specific to each individual resort (tenant), ensuring data privacy, security, and customization per resort. On the other hand, the Central Application Database serves as the core repository that manages shared platform data such as tenant accounts, mapped domains, subscription plans, registration requests, upgrade/renewal requests, and platform-wide settings. This dual-database architecture allows the DJs Resort Booking System to maintain good performance, improve data organization, and support multi-tenant functionality while ensuring consistency and control across the entire platform.

7.1 Central database (examples)

- `admins`: superadmin accounts (central)
- `tenants`: resorts (slug, database_name, metadata, etc.)
- `tenant_domains`: mapped hostnames per tenant
- `plans`: subscription plans (pricing + feature JSON)
- `tenant_registration_requests`: onboarding + payment submission + approval workflow
- `tenant_plan_upgrade_requests`: upgrades/renewals with proration fields
- `platform_settings`, `maintenance_tickets`, `tenant_update_logs`, etc.

7.2 Tenant database (examples)

- `rooms`: room/cottage inventory (price, capacity, availability)
- `room_images`: multiple images per room
- `bookings`: booking records and payment fields (proof, partial/full)
- `tenant_users`: tenant staff accounts (guard: `tenant`)
- `regular_users`: guest/customer accounts (guard: `regular_user`)
- `tenant_rbac_roles`: RBAC role definitions (staff + customer kinds)
- `activity_logs`: per-tenant audit log entries

---

8. System Features

8.1 Central (Superadmin) Features

- Tenant management: create, update, activate/deactivate, map domains
- Tenant registration review: approve/reject tenant signup requests
- Subscriptions:
  - create/update plans and feature flags
  - review upgrade/renewal requests with proration calculation
- Platform operations: maintenance tickets, settings, platform reports

8.2 Tenant (Resort Staff) Features

- Tenant landing page (public): room list and booking entry point
- Dashboard: booking counts and revenue summaries (feature-gated in some plans)
- Rooms: create/edit/delete, availability tracking (plan-dependent), multiple images
- Bookings:
  - view list and calendar (plan-dependent)
  - confirm/cancel/update bookings
  - generate receipts (including signed guest receipt links)
- Branding: tenant-resort branding and public landing page customization
- Domains: manage mapped custom domains and set primary domain
- Staff + RBAC:
  - manage staff accounts
  - initialize default RBAC roles and update permissions
- Guests: view guest accounts and update guest roles (customer RBAC roles)
- Reports: analytics, exports (CSV/PDF) depending on plan features
- Activity log: audit trail feed for key actions
- Support: create tickets
- Payment portal: view subscription status and submit upgrade/renewal requests with proof

8.3 Guest (Regular User) Features

- Account registration/login per tenant domain
- Booking flow: select room and submit booking request
- My Bookings: update booking details and upload payment proof

---

9. User Roles and Permissions (RBAC)

The system uses multi-auth plus tenant RBAC.

9.1 Central roles

- Superadmin (guard: `admin`)
  - full platform management: tenants, plans, approvals, payments, maintenance, settings, reports

9.2 Tenant roles

- Tenant staff user (guard: `tenant`)
  - access enforced by `tenant.staff.rbac` middleware
  - permissions map in `config/tenant_rbac.php` (`staff_route_permissions`)
  - staff roles can be initialized and edited via `TenantRbacController`

- Guest / customer (guard: `regular_user`)
  - access enforced by `tenant.customer.rbac` middleware
  - permissions map in `config/tenant_rbac.php` (`customer_route_permissions`)

9.3 Default RBAC role examples (created by `TenantRbacService`)

- Staff: Manager, Reception
- Customer: Standard guest, Limited guest

---

10. Pricing Model (Subscription)

The DJs Resort Booking System adopts a subscription-based pricing model designed to accommodate different levels of resort needs. This model allows flexibility depending on the resort size, number of rooms/cottages, booking volume, and required system features such as availability tracking, booking calendar views, and reporting/export tools.

Basic Plan
The Basic Plan is intended for small resorts with limited inventory and basic operational needs. This plan supports a restricted number of rooms (based on plan limits), manual booking management by resort staff (reviewing and confirming bookings), basic guest portal access (account registration, booking requests, and payment proof upload), and simple reporting. This plan focuses on essential booking functionality at a minimal cost.

Standard Plan
The Standard Plan is designed for medium-sized resorts with moderate booking activity and a larger inventory. It supports higher room limits, availability tracking for rooms/cottages, and improved booking management features such as calendar-based views (when enabled in the subscription features). It also includes enhanced reporting and export options (subject to plan features), helping resort staff monitor bookings and operational performance more effectively.

Premium Plan
The Premium Plan is the most advanced subscription tier, intended for large or highly active resorts. It supports unlimited or significantly higher room limits (depending on plan configuration), full booking management tools including booking calendar features, and more detailed analytics and reporting. It also includes data export functionality (CSV/PDF where enabled) and priority support/operations features, enabling faster issue resolution and better visibility into bookings and revenue trends.

Tenants can submit upgrade/renewal requests through the tenant payment portal, and superadmins approve or reject them (including proration support via stored proration fields).

---

11. Security Implementation

The DJs Resort Booking System implements multiple layers of security to protect user accounts, tenant data, and booking/payment information. Since the system handles sensitive details such as guest contact information, staff access to booking management, and uploaded payment proof images, security controls are applied across authentication, authorization, data isolation, and request validation.

Password Security
User passwords are protected using Laravel’s secure password hashing, ensuring that passwords are not stored in plain text.

Input Validation and Sanitization
All important forms (registration, login, booking requests, and payment proof uploads) are validated using Laravel’s request validation rules and custom rules. This reduces the risk of invalid data, prevents abuse of payment fields, and helps protect against common injection and form-based attacks.

Role-Based Access Control (RBAC)
The system enforces role-based access through multi-auth guards and tenant RBAC. Central (superadmin) pages are protected using the admin guard, while tenant staff and guest portal access are restricted based on authenticated guards and RBAC permission mappings. This ensures users can only access features relevant to their role and responsibilities.

Tenant Data Isolation
To maintain privacy between resorts, tenant data is isolated by using separate tenant databases. The active tenant database connection is resolved dynamically from the request hostname (mapped domains), and tenant models use the tenant connection to prevent cross-tenant data access.

Bot Protection (reCAPTCHA)
To reduce automated signups and brute-force attempts, the system supports reCAPTCHA verification on login and registration pages as well as booking submission forms when enabled.

Signed Links
Certain guest-facing links (such as receipts) can be protected using Laravel’s signed URL validation to prevent tampering and unauthorized access.

Least Privilege and Subscription Controls
The system follows the principle of least privilege by combining RBAC with subscription feature gating, ensuring that only authorized users and eligible subscription plans can access specific modules.

---

12. API / Integrations Documentation

This system integrates with third-party services to improve media handling and security. Integrations are designed to be optional and configurable via environment variables, so the platform can run in local development without requiring external services, and can be upgraded in production when needed.

12.1 Cloudinary (file/media storage)
Cloudinary can be used to store and optimize uploaded media assets such as room images and payment proof images. Instead of saving files only on the application server, the system uploads images to Cloudinary and stores the returned secure URL (or public ID) in the database. This reduces server storage usage and improves media delivery performance (compression, resizing, and format conversion).

In this system, Cloudinary is primarily used for two types of assets. First, room images uploaded by tenant staff can be stored in Cloudinary and then displayed on the tenant landing page and room pages using the saved image URL. Second, payment proof uploads submitted by guests during booking or through “My Bookings” can be stored in Cloudinary to avoid large file storage on the server while keeping access controlled through application logic.

Configuration is done through environment variables. You can use `CLOUDINARY_URL` or set `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, and `CLOUDINARY_API_SECRET`. The storage behavior can be switched using `MEDIA_DRIVER` (local or cloudinary).

12.2 reCAPTCHA / hCaptcha (bot protection)
Captcha services are used to reduce automated abuse such as spam account creation, brute-force login attempts, and automated booking submissions. When enabled, the system requires users to complete a captcha challenge on login and registration pages, as well as on booking submission forms. The captcha token is verified server-side before the request is accepted.

Configuration is done through environment variables. For reCAPTCHA, set `RECAPTCHA_ENABLED`, `RECAPTCHA_SITE_KEY`, and `RECAPTCHA_SECRET_KEY`. If hCaptcha is used in the future, equivalent keys can be configured (for example `HCAPTCHA_SITE_KEY` and `HCAPTCHA_SECRET_KEY`) and verified server-side using a similar validation workflow.

---

13. System Screenshots

The System Screenshots section presents the actual visual output of the developed application, showing how the system appears and functions in real use. It highlights the different user interfaces, including public pages, forms, dashboards, and management pages. These screenshots illustrate the layout and design of the system and serve as a reference for evaluating usability and completeness.

Add screenshots here as you capture them during testing (recommended folder: `docs/screenshots/`).

Suggested screenshot list (minimum):

- Central landing page
- Superadmin login
- Superadmin tenant list + tenant edit page (domains, activation)
- Superadmin subscription plans page
- Tenant public landing page (tenant domain)
- Tenant staff login + dashboard
- Rooms management pages (list/create/edit)
- Bookings list + booking confirmation/cancellation
- Guest registration/login
- Guest “My Bookings” page + payment proof upload
- Reports + export actions (CSV/PDF if enabled)

---

14. Development Documentation

The Development Documentation section provides a visual record of the system’s creation. These images serve as evidence of how the system was built and how its components were connected to achieve the final working application.

14.1 Code locations (high-signal)

- Central routes: `routes/web.php`
- Tenant domain routes: `routes/usingDomain.php`
- Tenant resolver middleware: `app/Http/Middleware/SetTenantDatabase.php`
- Tenancy config: `config/tenancy.php`
- RBAC config: `config/tenant_rbac.php`
- Booking model (tenant DB): `app/Models/Booking.php`
- Build tooling: `package.json` (Vite), Tailwind/Alpine in resources

14.2 Local setup (summary)

From `ResortBookingSystem/`:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
php artisan serve
```

> Note: Tenancy uses a central DB + tenant DBs. Your environment may require additional tenant provisioning/migration commands depending on how you create tenants in your workflow.

