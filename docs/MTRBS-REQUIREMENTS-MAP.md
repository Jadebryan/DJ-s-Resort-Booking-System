# MTRBS Requirements vs Implementation Map

Reference: **resort-wts.pdf** (root folder) — Multi-Tenant Resort Booking System (MTRBS) proposal.

---

## 1. System description (from PDF)

- **MTRBS**: SaaS platform for multiple resort businesses on one infrastructure.
- Tenants (resort owners) manage: **booking operations**, **branding**, **users**, **reports**; data isolated per tenant.
- Goals: streamline reservations, less manual booking, better customer experience.
- Architecture: shared infra, independent operation per tenant, secure data isolation, scalable, centralized maintenance.

**Current state:** Multi-tenant structure exists (tenant DBs, slug/domain routing, tenant vs tenant-user auth). Missing: full booking flow, tenant branding, plans/pricing, and role differentiation (owner vs staff).

---

## 2. Tenants (resort owners) — PDF requirements

Each tenant gets:

| Requirement | Implemented | Notes |
|-------------|-------------|--------|
| Dedicated admin dashboard | ✅ | `/{tenant_slug}/dashboard`; Rooms, Branding, Bookings |
| Customizable branding (logo, color scheme, homepage layout) | ✅ | Logo, primary/secondary colors; tenant branding UI; used on landing |
| Independent management of rooms, cottages, amenities | ✅ | Rooms + cottages (type); tenant dashboard CRUD |
| Booking and reservation tracking | ✅ | Bookings model; tenant dashboard list/confirm/cancel |
| Isolated database records | ✅ | Per-tenant DB + `SetTenantDatabase` middleware |
| Reports and analytics | ⚠️ | Reports page: totals + financial summary done; calendar/PDF/CSV pending |

---

## 3. Tenant user types (PDF)

### 4.1 Resort owner / tenant administrator

| Requirement | Implemented | Notes |
|-------------|-------------|--------|
| Full access to tenant dashboard | ✅ | Tenant auth; dashboard with Rooms, Branding, Bookings |
| Manage resort branding and configurations | ✅ | Branding UI; logo, colors; used on landing |
| Add, edit, or remove rooms and amenities | ✅ | Rooms CRUD (room/cottage type) |
| Manage user accounts and staff roles | ✅ | Staff CRUD under /staff; assign admin vs staff; admin-only nav/dashboard |
| View booking reports and analytics | ⚠️ | Bookings list; reports/analytics/calendar/PDF pending |
| Monitor financial summaries | ❌ | No finance module yet |

### 4.2 Resort staff

| Requirement | Implemented | Notes |
|-------------|-------------|--------|
| Manage and update bookings | ❌ | Owner can; staff role not yet used for permissions |
| Confirm or cancel reservations | ✅ | Tenant dashboard bookings (owner); staff access pending |
| Update room availability | ✅ | Room CRUD has is_available |
| Assist customers | ❌ | No support tooling |

**Current state:** Only “tenant” (owner) and “regular_user” (customer) are implemented. **Staff** role exists in DB (`tenant_users.role`) but not used in routes or features.

### 4.3 Customers

| Requirement | Implemented | Notes |
|-------------|-------------|--------|
| Browse resort information and accommodations | ✅ | Tenant landing + /book room list |
| Check availability and pricing | ✅ | Room list with price; overlap check on book |
| Make online reservations | ✅ | Public booking flow; pending then confirm/cancel |
| Receive booking confirmations | ✅ | Email sent via BookingStatusNotification when confirmed (and on received/cancelled) |
| View booking history | ✅ | My Bookings (tenant user) |

---

## 4. Pricing model (PDF)

### 5.1 Subscription plans

| Plan | Features (PDF) | In codebase |
|------|----------------|-------------|
| **Basic** | Limited rooms (e.g. 10), guest info, booking mgmt, simple dashboard, monthly | ✅ Plans table + tenant plan_id; max-rooms enforced in room CRUD |
| **Standard** | Unlimited rooms, booking calendar, PDF/CSV reports, room availability, monthly/yearly | ⚠️ Plan exists; calendar/PDF/CSV not yet |
| **Premium** | Standard + revenue analytics, advanced reports, booking archive, admin activity logs | ✅ Revenue analytics + activity log done; archive optional |

### 5.2 Optional add-ons

- SMS booking notifications — ✅ Optional; mail + SMS via BookingStatusNotification
- Online payment gateway — ✅ Stripe Checkout for subscription + booking payment
- Custom domain support — ✅ Admin + tenant UI to add/remove/set primary; middleware redirects custom domain to slug URL
- Advanced reporting modules — ❌

---

## 5. Implementation checklist (high level)

### Superadmin (platform) dashboard

- [ ] Tenant CRUD (create, edit, suspend/delete tenants).
- [ ] **Pricing/plans**: plans table (Basic/Standard/Premium), limits (e.g. max rooms), billing interval (monthly/yearly).
- [ ] Optional: assign plan/add-ons per tenant; usage/limits enforcement (e.g. room count for Basic).
- [ ] Wire admin dashboard cards to real pages (tenants, payments, settings, etc.).

### Tenant (resort owner) dashboard

- [ ] **Branding**: store logo, color scheme, optional homepage layout; use on tenant landing and emails.
- [ ] **Rooms, cottages, amenities**: models + migrations (tenant DB), CRUD in tenant dashboard.
- [ ] **Booking/reservation**: models (e.g. reservations, availability), status flow (pending/confirmed/cancelled).
- [ ] **Staff role**: use `tenant_users.role` (e.g. owner vs staff); restrict “branding / settings” to owner if desired.
- [ ] Reports/analytics and financial summary (can start simple: list reservations, totals).

### Tenant users — staff

- [ ] Routes/UI for staff: manage bookings, confirm/cancel, update room availability (and optionally assist customers).
- [ ] Permissions based on `tenant_users.role`.

### Tenant users — customers

- [ ] Public (or logged-in) browse: resort info, rooms/amenities, availability, pricing.
- [ ] Booking flow: choose room/dates → confirm → (optional) payment; store in tenant DB.
- [ ] Post-booking: confirmation (e.g. email/page); “My bookings” / booking history for logged-in customers.

### Optional (later)

- [ ] SMS notifications for bookings.
- [ ] Payment gateway integration.
- [ ] Custom domain UI (attach domain to tenant using `tenant_domains`).
- [ ] Advanced reporting (exports, analytics).

---

## 6. File reference (current)

- **Admin:** `routes/web.php` (admin prefix), `app/Http/Controllers/`, `resources/views/admin/`, `resources/views/tenant_seeders.blade.php`, `TenantController@index`.
- **Tenant:** `resources/views/Tenant/`, `resources/views/Tenant/TenantLandingPage.blade.php`, tenant auth in `auth/tenantAuth/`.
- **Tenant user:** `resources/views/TenantUser/`, `auth/tenantUserAuth/`; tenant DB: `tenant_users`, `regular_users` (migrations in `database/migrations/tenant/`).
- **Domain routing:** `routes/usingDomain.php`, `SetTenantDatabase` middleware, `Tenant` model + `tenant_domains`.

Use this document to prioritize and tick off items as they are implemented.

---

## 7. Core implementation order (focus first)

Work through these in order so each step has the right foundation:

| # | Task | Why first |
|---|------|------------|
| 1 | **Plans & pricing** — `plans` table, link to tenants | Defines what we sell; needed before tenant CRUD can assign a plan. |
| 2 | **Admin: Tenant CRUD** — list, create, edit, assign plan; wire dashboard | Superadmin can manage tenants and pricing. |
| 3 | **Rooms (tenant DB)** — migrations + model + tenant dashboard CRUD | Tenants must manage inventory before anyone can book. |
| 4 | **Tenant branding** — logo, colors in DB; tenant UI; use on landing | Each resort can customize its domain look. |
| 5 | **Bookings/Reservations (tenant DB)** — migration + model (room + customer) | Core data for reservations. |
| 6 | **Tenant dashboard: manage bookings** — list, confirm, cancel | Resort owners see and control reservations. |
| 7 | **Customer booking flow** — browse rooms, availability, create booking, My Bookings | Customers can complete a reservation and see history. |

*After core:* staff role permissions, reports/analytics, plan limits (e.g. max rooms for Basic), optional add-ons (SMS, payments, custom domain).

---

## 8. Full implementation TODO (post–core)

**Core (done):** Plans & pricing, Admin Tenant CRUD, Rooms, Branding, Bookings model, Tenant manage bookings, Customer booking flow + My Bookings.

| # | Task | Status |
|---|------|--------|
| 1 | **Plan limits** — Enforce max rooms per plan (e.g. Basic = 10) in tenant room CRUD | Pending |
| 2 | **Staff role** — Permissions + UI for staff to manage bookings (confirm/cancel); restrict branding/settings to owner | Pending |
| 3 | **Tenant reports** — Booking list + simple totals; financial summary view | Pending |
| 4 | **Booking calendar** — Calendar view for bookings (Standard plan) | ✅ Done |
| 5 | **Downloadable reports** — Export bookings as PDF/CSV (Standard plan) | ✅ Done |
| 6 | **Manage staff/owner** — Tenant dashboard: CRUD for tenant_users, assign owner vs staff roles | ✅ Done |
| 7 | **Admin dashboard** — Real pages for Payments, Maintenance, Notices, Reports, Settings or remove placeholders | ✅ Done |
| 8 | **Premium features** — Revenue analytics (daily/monthly), admin activity logs | ✅ Done |
| 9 | **Optional: SMS** — Booking notifications via SMS | ✅ Done |
| 10 | **Optional: Payment gateway** — Online payment for bookings/subscriptions | ✅ Done |
| 11 | **Optional: Custom domain UI** — Attach domain to tenant (tenant_domains) | Pending |
| 12 | **Optional: Email** — Booking confirmation email to customer | ✅ Done |
