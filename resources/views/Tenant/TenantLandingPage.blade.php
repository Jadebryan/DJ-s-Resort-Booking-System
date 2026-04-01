@php
    $siteName = $tenant instanceof \App\Models\Tenant ? $tenant->appDisplayName() : 'Resort';
    $primary = $tenant?->primary_color ?? '#0ea5e9';
    $secondary = $tenant?->secondary_color ?? '#0369a1';
    $meta = $tenant?->metadata ?? [];
    $heroTitle = $meta['hero_title'] ?? 'Book your perfect stay';
    $heroSubtitle = $meta['hero_subtitle'] ?? 'Explore our rooms and cottages, check availability, and reserve your stay in just a few clicks.';
    $heroBadge = $meta['hero_badge'] ?? 'Rooms & cottages';
    $heroNote = $meta['hero_note'] ?? 'No account needed to browse · Create one to manage your bookings';
    $ctaPrimaryText = $meta['cta_primary_text'] ?? 'View rooms';
    $ctaSecondaryText = $meta['cta_secondary_text'] ?? 'Create account';
    $ctaPrimaryRaw = trim((string) ($meta['cta_primary_url'] ?? ''));
    $ctaSecondaryRaw = trim((string) ($meta['cta_secondary_url'] ?? ''));
    $logoUrl = ($tenant && $tenant->logo_path) ? asset('storage/' . $tenant->logo_path) : null;
    $heroMediaPath = $meta['hero_media_path'] ?? null;
    $heroMediaType = $meta['hero_media_type'] ?? null;
    $heroMediaUrl = $heroMediaPath ? asset('storage/' . $heroMediaPath) : null;
    $heroOverlayOpacity = (int) ($meta['hero_overlay_opacity'] ?? 55);
    if ($heroOverlayOpacity < 0 || $heroOverlayOpacity > 90) {
        $heroOverlayOpacity = 55;
    }
    $heroOverlayAlpha = number_format($heroOverlayOpacity / 100, 2, '.', '');
    $sectionVisibility = $meta['section_visibility'] ?? ['hero' => true, 'rooms' => true, 'cta' => true];
    $showHero = (bool) ($sectionVisibility['hero'] ?? true);
    $showRooms = (bool) ($sectionVisibility['rooms'] ?? true);
    $showCta = (bool) ($sectionVisibility['cta'] ?? true);
    $sectionOrder = $meta['section_order'] ?? ['hero', 'rooms', 'cta'];
    if (!is_array($sectionOrder) || count($sectionOrder) !== 3) {
        $sectionOrder = ['hero', 'rooms', 'cta'];
    }
    $safeSectionOrder = array_values(array_unique(array_filter($sectionOrder, fn ($s) => in_array($s, ['hero', 'rooms', 'cta'], true))));
    foreach (['hero', 'rooms', 'cta'] as $requiredSection) {
        if (!in_array($requiredSection, $safeSectionOrder, true)) {
            $safeSectionOrder[] = $requiredSection;
        }
    }
    $sectionOrderMap = array_flip($safeSectionOrder);
    $resolveCtaUrl = function (string $value, string $fallback): string {
        if ($value === '') {
            return $fallback;
        }
        if (str_starts_with($value, '#') || str_starts_with($value, '/')) {
            return $value;
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return filter_var($value, FILTER_VALIDATE_URL) ? $value : $fallback;
        }
        if (str_starts_with($value, 'mailto:')) {
            $email = substr($value, 7);
            return ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) ? $value : $fallback;
        }
        if (str_starts_with($value, 'tel:')) {
            $phone = substr($value, 4);
            return ($phone !== '' && preg_match('/^[0-9+\-\s()]+$/', $phone)) ? $value : $fallback;
        }
        return $fallback;
    };
    $ctaPrimaryUrl = $resolveCtaUrl($ctaPrimaryRaw, '#rooms');
    $ctaSecondaryUrl = $resolveCtaUrl($ctaSecondaryRaw, tenant_url('user/register'));
    $contactPhone = trim((string) ($meta['contact_phone'] ?? ''));
    $contactEmail = trim((string) ($meta['contact_email'] ?? ''));
    $contactAddress = trim((string) ($meta['contact_address'] ?? ''));
    $socialFacebook = trim((string) ($meta['social_facebook'] ?? ''));
    $socialInstagram = trim((string) ($meta['social_instagram'] ?? ''));
    $socialTikTok = trim((string) ($meta['social_tiktok'] ?? ''));
    $socialLinks = array_filter([
        'Facebook' => $socialFacebook,
        'Instagram' => $socialInstagram,
        'TikTok' => $socialTikTok,
    ]);
    $hasContactInfo = $contactPhone !== '' || $contactEmail !== '' || $contactAddress !== '' || !empty($socialLinks);
    $rooms = $rooms ?? collect();
    $seoMetaTitle = trim((string) ($meta['seo_meta_title'] ?? ''));
    $seoMetaDescription = trim((string) ($meta['seo_meta_description'] ?? ''));
    $seoOgImagePath = $meta['seo_og_image_path'] ?? null;
    $seoFaviconPath = $meta['seo_favicon_path'] ?? null;
    $seoOgImageUrl = $seoOgImagePath ? asset('storage/' . $seoOgImagePath) : ($heroMediaType === 'image' && $heroMediaUrl ? $heroMediaUrl : asset('images/background.jpg'));
    $seoFaviconUrl = $seoFaviconPath ? asset('storage/' . $seoFaviconPath) : ($logoUrl ?: asset('favicon.ico'));
    $seoTitle = $seoMetaTitle !== '' ? $seoMetaTitle : ($siteName . ' · Book Your Stay');
    $seoDescription = $seoMetaDescription !== '' ? $seoMetaDescription : 'Explore our rooms and cottages, check availability, and reserve your stay in just a few clicks.';
    $promoEnabled = (bool) ($meta['promo_enabled'] ?? false);
    $promoText = trim((string) ($meta['promo_text'] ?? ''));
    $promoStartDate = trim((string) ($meta['promo_start_date'] ?? ''));
    $promoEndDate = trim((string) ($meta['promo_end_date'] ?? ''));
    $promoDismissible = (bool) ($meta['promo_dismissible'] ?? true);
    $today = now()->toDateString();
    $promoWithinStart = $promoStartDate === '' || $today >= $promoStartDate;
    $promoWithinEnd = $promoEndDate === '' || $today <= $promoEndDate;
    $showPromo = $promoEnabled && $promoText !== '' && $promoWithinStart && $promoWithinEnd;
    $pageBg = trim((string) ($meta['landing_page_bg'] ?? ''));
    if ($pageBg === '' || ! preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $pageBg)) {
        $pageBg = '#f4f2ee';
    }
    $roomsList = $rooms->take(6);
    $roomsForBooking = $rooms->map(function ($r) {
        $coverPath = $r->image_path ?: $r->images->first()?->image_path;
        $imageUrl = $coverPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($coverPath) : '';

        return [
            'id' => $r->id,
            'name' => $r->name,
            'type' => $r->type,
            'capacity' => $r->capacity,
            'price_per_night' => (float) $r->price_per_night,
            'description' => $r->description,
            'image_url' => $imageUrl,
            'hue' => abs(crc32((string) $r->id.$r->name)) % 360,
        ];
    })->values()->all();
    $storeUrl = tenant_url('book');
@endphp
@extends('layouts.public')

@section('title', $seoTitle)
@section('meta')
    <meta name="description" content="{{ $seoDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:image" content="{{ $seoOgImageUrl }}">
    <meta property="og:url" content="{{ request()->fullUrl() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoOgImageUrl }}">
    <link rel="icon" href="{{ $seoFaviconUrl }}">
@endsection

@push('styles')
<style>
    /* Match html/body to tenant page background (layout default is cream). */
    body { background-color: {{ $pageBg }} !important; }
    .tenant-landing-surface {
        background-color: {{ $pageBg }};
        background-image:
            radial-gradient(ellipse 120% 80% at 100% -20%, color-mix(in srgb, {{ $primary }} 12%, transparent), transparent 55%),
            radial-gradient(ellipse 90% 70% at -10% 60%, color-mix(in srgb, {{ $secondary }} 10%, transparent), transparent 50%);
    }
    @supports not (background: color-mix(in srgb, red, blue)) {
        .tenant-landing-surface { background-color: {{ $pageBg }}; background-image: none; }
    }
</style>
@endpush

@section('body')
<div class="tenant-landing-surface min-h-screen min-w-0" x-data="{
    browseModalOpen: false,
    bookModalOpen: false,
    selectedRoom: null,
    promoVisible: {{ $showPromo ? 'true' : 'false' }},
    promoDismissible: {{ $promoDismissible ? 'true' : 'false' }},
    roomsForBooking: @js($roomsForBooking),
    openBookModal(roomId) {
        this.selectedRoom = this.roomsForBooking.find(r => r.id == roomId) || null;
        this.bookModalOpen = !!this.selectedRoom;
        this.browseModalOpen = false;
    },
    closeBookModal() {
        this.bookModalOpen = false;
        this.selectedRoom = null;
    }
}" @keydown.escape.window="bookModalOpen = false; browseModalOpen = false; selectedRoom = null">
@if($showPromo)
<div x-show="promoVisible" x-cloak class="relative z-50 border-b border-teal-200 bg-teal-50">
    <div class="max-w-7xl mx-auto min-w-0 px-4 sm:px-6 lg:px-8 py-2.5 flex items-center gap-3">
        <p class="min-w-0 flex-1 break-words text-xs sm:text-sm text-teal-900 font-medium">{{ $promoText }}</p>
        @if($promoDismissible)
            <button type="button" @click="promoVisible = false"
                    class="ml-auto inline-flex h-7 w-7 items-center justify-center rounded-md text-teal-700 hover:bg-teal-100"
                    aria-label="Dismiss announcement">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        @endif
    </div>
</div>
@endif
<!-- Tenant nav -->
<header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/85 backdrop-blur-md">
    <div class="mx-auto max-w-7xl min-w-0 px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 min-w-0 items-center justify-between gap-3">
            <a href="{{ tenant_url('/') }}" class="flex min-w-0 max-w-[65%] items-center gap-2 sm:max-w-none">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="h-9 w-auto object-contain">
                @else
                    <div class="flex h-9 w-9 items-center justify-center rounded-2xl text-lg font-bold text-slate-950 shadow-md" style="background: linear-gradient(135deg, {{ $primary }}45, {{ $secondary }}55);">
                        {{ strtoupper(substr($siteName, 0, 2)) }}
                    </div>
                @endif
                <div class="flex min-w-0 flex-col leading-tight">
                    <span class="font-display truncate text-sm font-semibold tracking-tight text-slate-900">{{ $siteName }}</span>
                    <span class="truncate text-[11px] tracking-tight text-slate-500">{{ __('Book your stay') }}</span>
                </div>
            </a>

            <div class="hidden shrink-0 items-center gap-8 text-sm font-medium md:flex">
                <a href="{{ tenant_url('/') }}" class="text-slate-500 transition hover:text-slate-900">{{ __('Home') }}</a>
                <a href="#rooms" class="text-slate-500 transition hover:text-slate-900">{{ __('Rooms & cottages') }}</a>
            </div>

            <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                <a href="{{ tenant_url('user/login') }}"
                   class="hidden items-center justify-center rounded-full border border-slate-300 px-4 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 sm:inline-flex">
                    {{ __('Login') }}
                </a>
                <button type="button" @click="browseModalOpen = true"
                   class="inline-flex items-center justify-center rounded-full px-4 py-1.5 text-xs font-semibold text-white shadow-md transition hover:opacity-95"
                   style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
                    {{ __('Browse & book') }}
                </button>
            </div>
        </div>
    </div>
</header>

<div class="flex flex-col">
@if($showHero)
<section class="relative overflow-hidden" style="order: {{ (int) ($sectionOrderMap['hero'] ?? 0) }};">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20 lg:pb-24 relative">
        <div class="grid lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] gap-10 lg:gap-14 items-start">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-slate-200 text-[11px] text-slate-500 mb-5 shadow-sm">
                    <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $primary }};"></span>
                    {{ $heroBadge }}
                    <span class="text-slate-400">·</span>
                    <span class="text-slate-600">{{ $siteName }}</span>
                </div>

                <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl xl:text-[3.25rem] xl:leading-[1.08]">
                    {{ $heroTitle }}
                    <span class="mt-1 block font-medium" style="color: {{ $primary }};">{{ __('at') }} {{ $siteName }}.</span>
                </h1>

                <p class="mt-5 text-sm sm:text-base text-slate-600 max-w-xl">
                    {{ $heroSubtitle }}
                </p>

                <div class="mt-6 flex flex-wrap gap-5 text-xs text-slate-500">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-slate-900">{{ $rooms->count() }} available</span>
                        <span class="text-slate-500">Rooms & cottages</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-slate-900">Online booking</span>
                        <span class="text-slate-500">Instant confirmation</span>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $ctaPrimaryUrl }}" class="inline-flex items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold text-white shadow-xl hover:opacity-95 transition scroll-smooth"
                       style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
                        {{ $ctaPrimaryText }}
                    </a>
                    <a href="{{ $ctaSecondaryUrl }}"
                       class="inline-flex items-center justify-center rounded-full border border-slate-300 px-4 py-2 text-xs font-medium text-slate-700 hover:border-slate-400 hover:bg-slate-50 transition">
                        {{ $ctaSecondaryText }}
                    </a>
                    <a href="{{ tenant_url('user/login') }}"
                       class="inline-flex items-center justify-center rounded-full border border-slate-200 px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 transition">
                        Guest login
                    </a>
                </div>

                <p class="mt-3 text-[11px] text-slate-500">
                    {{ $heroNote }}
                </p>
            </div>

            <div class="relative">
                <div class="relative rounded-3xl overflow-hidden shadow-2xl bg-slate-900">
                    @if($heroMediaUrl && $heroMediaType === 'video')
                        <video autoplay muted loop playsinline class="h-72 w-full object-cover">
                            <source src="{{ $heroMediaUrl }}">
                        </video>
                    @elseif($heroMediaUrl)
                        <img src="{{ $heroMediaUrl }}"
                             alt="{{ $siteName }}"
                             class="h-72 w-full object-cover"
                             onerror="this.onerror=null; this.src='{{ asset('images/background.jpg') }}';">
                    @else
                        <img src="{{ asset('images/background.jpg') }}"
                             alt="{{ $siteName }}"
                             class="h-72 w-full object-cover">
                    @endif
                    <div class="absolute inset-x-0 bottom-0 px-6 pb-5 pt-16 flex flex-col justify-end"
                         style="background: linear-gradient(to top, rgba(2, 6, 23, {{ $heroOverlayAlpha }}), rgba(2, 6, 23, {{ min(0.95, (float)$heroOverlayAlpha + 0.15) }}), rgba(2, 6, 23, 0));">
                        <div class="text-xs text-slate-200 flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 border text-white/90 border-white/30" style="background-color: {{ $primary }}40;">
                                {{ $siteName }}
                            </span>
                            <span class="text-slate-300">Rooms & cottages</span>
                        </div>
                        <p class="text-sm text-slate-100">
                            Check availability and reserve your stay online.
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <a href="#rooms" class="rounded-2xl border border-slate-200 bg-white p-3 text-xs text-slate-600 shadow-sm hover:border-slate-300 transition text-left block">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold text-slate-900">Dates</span>
                            <span class="text-[10px]" style="color: {{ $primary }};">Pick on booking</span>
                        </div>
                        <p>Choose check‑in & check‑out when you book a room.</p>
                    </a>
                    <a href="#rooms" class="rounded-2xl border border-slate-200 bg-white p-3 text-xs text-slate-600 shadow-sm hover:border-slate-300 transition text-left block">
                        <span class="font-semibold text-slate-900 block mb-1">Rooms</span>
                        <p>See rooms and cottages with transparent pricing below.</p>
                    </a>
                    <a href="{{ tenant_url('user/register') }}" class="rounded-2xl border border-slate-200 bg-white p-3 text-xs text-slate-600 shadow-sm hover:border-slate-300 transition block">
                        <span class="font-semibold text-slate-900 block mb-1">Account</span>
                        <p>Register to view and manage your bookings.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@if($showRooms)
<section id="rooms" class="border-t border-slate-200/60 bg-white/70 py-16 backdrop-blur-sm sm:py-20" style="order: {{ (int) ($sectionOrderMap['rooms'] ?? 1) }};">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ __('Stays') }}</p>
            <h2 class="font-display mt-2 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                {{ __('Rooms & cottages') }}
            </h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-600 sm:text-base">
                {{ __('Choose your space. Rates are per night—book in a few steps with instant confirmation.') }}
            </p>
        </div>

        @if($roomsList->isEmpty())
            <div class="mt-10 rounded-3xl border border-dashed border-slate-300/80 bg-slate-50/80 p-10 text-center text-sm text-slate-500">
                {{ __('No rooms available right now. Check back soon.') }}
            </div>
        @else
            <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3 lg:gap-10">
                @foreach($roomsList as $room)
                    @php
                        $coverPath = $room->image_path ?: $room->images->first()?->image_path;
                        $coverUrl = $coverPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($coverPath) : null;
                        $hue = abs(crc32((string) $room->id.$room->name)) % 360;
                        $isCottage = $room->type === 'cottage';
                    @endphp
                    <button type="button" @click="openBookModal({{ $room->id }})"
                       class="group w-full overflow-hidden rounded-3xl bg-white text-left shadow-sm ring-1 ring-slate-200/60 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:ring-slate-300/80">
                        <div class="relative aspect-[5/4] overflow-hidden bg-slate-200">
                            @if($coverUrl)
                                <img src="{{ $coverUrl }}" alt="{{ $room->name }}" class="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-[1.04]">
                            @else
                                <div class="absolute inset-0 flex flex-col items-center justify-center"
                                     style="background: linear-gradient(145deg, hsl({{ $hue }}, 28%, 92%) 0%, hsl({{ ($hue + 48) % 360 }}, 32%, 88%) 100%);">
                                    <span class="text-5xl opacity-40" aria-hidden="true">{{ $isCottage ? '🏠' : '🛏️' }}</span>
                                </div>
                            @endif
                            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-900/50 via-transparent to-transparent opacity-90"></div>
                            <span class="absolute left-4 top-4 inline-flex rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold tabular-nums text-slate-900 shadow-sm ring-1 ring-white/30 backdrop-blur-sm">
                                ₱{{ number_format($room->price_per_night, 0) }}<span class="font-semibold text-slate-500">/{{ __('night') }}</span>
                            </span>
                            <span class="absolute bottom-4 left-4 rounded-full bg-white/20 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-white backdrop-blur-md">
                                {{ $isCottage ? __('Cottage') : __('Room') }}
                            </span>
                        </div>
                        <div class="flex flex-col p-5 sm:p-6">
                            <h3 class="font-display text-lg font-semibold tracking-tight text-slate-900 sm:text-xl">{{ $room->name }}</h3>
                            <p class="mt-1.5 text-sm text-slate-500">
                                @if($room->capacity)
                                    {{ __('Up to :n guests', ['n' => $room->capacity]) }}
                                @endif
                                @if($room->description)
                                    <span class="mt-1 block line-clamp-2 text-slate-600">{{ Str::limit($room->description, 90) }}</span>
                                @endif
                            </p>
                            <span class="mt-5 inline-flex items-center gap-2 text-sm font-semibold transition group-hover:gap-2.5" style="color: {{ $primary }};">
                                {{ __('Book this stay') }}
                                <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </span>
                        </div>
                    </button>
                @endforeach
            </div>
            @if($rooms->count() > 6)
            <div class="mt-8 text-center">
                <button type="button" @click="browseModalOpen = true"
                   class="inline-flex items-center justify-center rounded-full border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:border-slate-400 hover:bg-slate-50 transition">
                    View all {{ $rooms->count() }} rooms
                </button>
            </div>
            @endif
        @endif
    </div>
</section>
@endif

@if($showCta)
<section class="border-t border-slate-200/80 bg-slate-100/80 py-16 sm:py-20" style="order: {{ (int) ($sectionOrderMap['cta'] ?? 2) }};">
    <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="font-display text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
            {{ __('Ready to book?') }}
        </h2>
        <p class="mt-3 text-sm leading-relaxed text-slate-600 sm:text-base">
            {{ __('Browse availability and reserve your stay at :name in a few clicks.', ['name' => $siteName]) }}
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="#rooms" class="inline-flex items-center justify-center rounded-full px-6 py-2.5 text-sm font-semibold text-white shadow-xl hover:opacity-95 transition scroll-smooth"
               style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
                View rooms
            </a>
            <a href="{{ tenant_url('user/register') }}"
               class="inline-flex items-center justify-center rounded-full border border-slate-300 px-5 py-2 text-xs font-medium text-slate-700 hover:border-slate-400 hover:bg-slate-50 transition">
                Create account
            </a>
            <a href="{{ tenant_url('user/login') }}"
               class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 transition">
                Guest login
            </a>
        </div>
    </div>
</section>
@endif
</div>

<!-- Footer -->
<footer class="border-t border-slate-200 bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
        <div class="space-y-1">
            <p>© {{ now()->year }} {{ $siteName }}. All rights reserved.</p>
            @if($hasContactInfo)
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-600">
                    @if($contactPhone !== '')
                        <span>Phone: {{ $contactPhone }}</span>
                    @endif
                    @if($contactEmail !== '')
                        <a href="mailto:{{ $contactEmail }}" class="hover:text-slate-800">Email: {{ $contactEmail }}</a>
                    @endif
                    @if($contactAddress !== '')
                        <span>Address: {{ $contactAddress }}</span>
                    @endif
                </div>
                @if(!empty($socialLinks))
                    <div class="flex flex-wrap items-center gap-3 text-[11px]">
                        @foreach($socialLinks as $platform => $link)
                            <a href="{{ $link }}" target="_blank" rel="noopener noreferrer" class="hover:text-slate-700">{{ $platform }}</a>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
        <div class="flex gap-4">
            <a href="#rooms" class="hover:text-slate-700 scroll-smooth">Rooms</a>
            <a href="{{ tenant_url('user/login') }}" class="hover:text-slate-700">Login</a>
            <a href="{{ tenant_url('user/register') }}" class="hover:text-slate-700">Register</a>
        </div>
    </div>
</footer>

<!-- Browse & Book modal -->
<div x-show="browseModalOpen"
     x-cloak
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="browse-modal-title">
    <div x-show="browseModalOpen"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.self="browseModalOpen = false"
         class="fixed inset-0 bg-black/50"></div>
    <div class="relative w-full max-w-4xl max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-6 py-4">
            <h2 id="browse-modal-title" class="font-display text-xl font-semibold text-slate-900">{{ __('Rooms & cottages') }}</h2>
            <button type="button" @click="browseModalOpen = false" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            @if($rooms->isEmpty())
                <p class="text-center text-slate-500 py-8">No rooms or cottages available at the moment. Check back soon.</p>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($rooms as $room)
                        @php
                            $mCover = $room->image_path ?: $room->images->first()?->image_path;
                            $mUrl = $mCover ? \Illuminate\Support\Facades\Storage::disk('public')->url($mCover) : null;
                            $mHue = abs(crc32((string) $room->id.$room->name)) % 360;
                            $mCottage = $room->type === 'cottage';
                        @endphp
                        <button type="button" @click="openBookModal({{ $room->id }})"
                           class="group w-full overflow-hidden rounded-2xl border border-slate-200/80 bg-white text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                            <div class="relative aspect-[5/4] overflow-hidden bg-slate-200">
                                @if($mUrl)
                                    <img src="{{ $mUrl }}" alt="{{ $room->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center" style="background: linear-gradient(145deg, hsl({{ $mHue }}, 28%, 92%) 0%, hsl({{ ($mHue + 48) % 360 }}, 32%, 88%) 100%);">
                                        <span class="text-4xl opacity-40">{{ $mCottage ? '🏠' : '🛏️' }}</span>
                                    </div>
                                @endif
                                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-900/45 to-transparent"></div>
                                <span class="absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-bold text-slate-900 shadow-sm">₱{{ number_format($room->price_per_night, 0) }}/{{ __('night') }}</span>
                            </div>
                            <div class="p-4">
                                <h3 class="font-display text-base font-semibold text-slate-900">{{ $room->name }}</h3>
                                <p class="mt-1 text-xs text-slate-500">
                                    @if($room->capacity){{ __('Up to :n guests', ['n' => $room->capacity]) }}@endif
                                </p>
                                <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold" style="color: {{ $primary }};">{{ __('Book') }} →</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Book this room modal (room details + booking form) -->
<div x-show="bookModalOpen && selectedRoom"
     x-cloak
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[60] flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="book-modal-title">
    <div @click="closeBookModal()"
         class="fixed inset-0 bg-black/50"></div>
    <div x-show="bookModalOpen && selectedRoom"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-150"
         @click.stop
         class="relative w-full max-w-lg max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
        <div class="shrink-0 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <h2 id="book-modal-title" class="font-display text-xl font-semibold text-slate-900" x-text="selectedRoom ? selectedRoom.name : ''"></h2>
            <button type="button" @click="closeBookModal()" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-5">
            <!-- Room summary with image -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                <div class="relative aspect-video bg-slate-200" x-show="selectedRoom">
                    <img x-show="selectedRoom && selectedRoom.image_url"
                         :src="selectedRoom && selectedRoom.image_url ? selectedRoom.image_url : ''"
                         :alt="selectedRoom ? selectedRoom.name : ''"
                         class="h-full w-full object-cover">
                    <div x-show="selectedRoom && !selectedRoom.image_url"
                         class="absolute inset-0 flex items-center justify-center text-5xl opacity-40"
                         :style="selectedRoom ? 'background: linear-gradient(145deg, hsl(' + selectedRoom.hue + ', 28%, 88%) 0%, hsl(' + ((selectedRoom.hue + 48) % 360) + ', 32%, 82%) 100%)' : ''">
                        <span x-text="selectedRoom && selectedRoom.type === 'cottage' ? '🏠' : '🛏️'"></span>
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                    <span class="inline-flex items-center rounded-full text-[10px] px-2 py-0.5 font-medium border capitalize"
                          :style="'background-color: {{ $primary }}20; color: {{ $primary }}; border-color: {{ $primary }}40;'"
                          x-text="selectedRoom ? selectedRoom.type : ''"></span>
                    <span class="text-sm font-semibold text-slate-900" x-show="selectedRoom">
                        ₱<span x-text="selectedRoom ? selectedRoom.price_per_night.toLocaleString('en-PH', {maximumFractionDigits:0}) : ''"></span><span class="text-slate-500 font-normal">/night</span>
                    </span>
                </div>
                    <p class="mt-2 text-xs text-slate-500" x-show="selectedRoom && selectedRoom.capacity" x-text="selectedRoom && selectedRoom.capacity ? 'Up to ' + selectedRoom.capacity + ' guests' : ''"></p>
                    <p class="mt-2 text-sm text-slate-600" x-show="selectedRoom && selectedRoom.description" x-text="selectedRoom ? selectedRoom.description : ''"></p>
                </div>
            </div>

            <!-- Booking form -->
            <x-form-with-busy method="POST" action="{{ $storeUrl }}" class="space-y-4" :overlay="false" busy-message="{{ __('Sending your booking…') }}">
                @csrf
                <input type="hidden" name="room_id" :value="selectedRoom ? selectedRoom.id : ''">

                <div class="grid grid-cols-2 gap-4" x-data="{}">
                    <div>
                        <label for="book_check_in" class="block text-sm font-medium text-slate-900 mb-1">Check-in</label>
                        <input type="date" id="book_check_in" name="check_in" required min="{{ date('Y-m-d') }}"
                               x-ref="bookCheckIn"
                               @change="let d = $event.target.value; if (d && $refs.bookCheckOut) { let next = new Date(d); next.setDate(next.getDate() + 1); $refs.bookCheckOut.min = next.toISOString().slice(0,10); if ($refs.bookCheckOut.value && $refs.bookCheckOut.value < $refs.bookCheckOut.min) $refs.bookCheckOut.value = '' }"
                               class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                    </div>
                    <div>
                        <label for="book_check_out" class="block text-sm font-medium text-slate-900 mb-1">Check-out</label>
                        <input type="date" id="book_check_out" name="check_out" required
                               x-ref="bookCheckOut"
                               class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                    </div>
                </div>

                @auth('regular_user')
                    <p class="text-sm text-slate-600">Booking as <strong>{{ auth('regular_user')->user()->name }}</strong> ({{ auth('regular_user')->user()->email }})</p>
                @else
                    <div class="space-y-4">
                        <div>
                            <label for="book_guest_name" class="block text-sm font-medium text-slate-900 mb-1">Your name</label>
                            <input type="text" id="book_guest_name" name="guest_name" required
                                   class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                        </div>
                        <div>
                            <label for="book_guest_email" class="block text-sm font-medium text-slate-900 mb-1">Email</label>
                            <input type="email" id="book_guest_email" name="guest_email" required
                                   class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                        </div>
                        <div>
                            <label for="book_guest_phone" class="block text-sm font-medium text-slate-900 mb-1">Phone (optional)</label>
                            <input type="text" id="book_guest_phone" name="guest_phone"
                                   class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                        </div>
                    </div>
                @endauth

                <div>
                    <label for="book_notes" class="block text-sm font-medium text-slate-900 mb-1">Notes (optional)</label>
                    <textarea id="book_notes" name="notes" rows="2" placeholder="Special requests..."
                              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900"></textarea>
                </div>

                <x-busy-submit class="w-full rounded-xl py-3 text-sm font-semibold text-white shadow-lg transition hover:opacity-95" style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});" busy-text="{{ __('Submitting…') }}">
                    {{ __('Submit booking request') }}
                </x-busy-submit>
            </x-form-with-busy>
        </div>
    </div>
</div>
</div>
@endsection
