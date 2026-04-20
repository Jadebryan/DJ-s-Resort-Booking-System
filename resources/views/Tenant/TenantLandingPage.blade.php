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
    $promoEnabled = (bool) ($meta['promo_enabled'] ?? false);
    $promoText = trim((string) ($meta['promo_text'] ?? ''));
    $promoImagePath = $meta['promo_image_path'] ?? null;
    $promoImageUrl = $promoImagePath ? asset('storage/' . $promoImagePath) : null;
    $promoStartDate = trim((string) ($meta['promo_start_date'] ?? ''));
    $promoEndDate = trim((string) ($meta['promo_end_date'] ?? ''));
    $promoDismissible = (bool) ($meta['promo_dismissible'] ?? true);
    $promoCtaText = trim((string) ($meta['promo_cta_text'] ?? ''));
    $promoCtaRaw = trim((string) ($meta['promo_cta_url'] ?? ''));
    $promoCtaUrl = $resolveCtaUrl($promoCtaRaw, '#rooms');
    $promoFrequencyDays = (int) ($meta['promo_frequency_days'] ?? 7);
    if ($promoFrequencyDays < 0 || $promoFrequencyDays > 365) {
        $promoFrequencyDays = 7;
    }
    $today = now()->toDateString();
    $promoWithinStart = $promoStartDate === '' || $today >= $promoStartDate;
    $promoWithinEnd = $promoEndDate === '' || $today <= $promoEndDate;
    $showPromo = $promoEnabled && $promoText !== '' && $promoWithinStart && $promoWithinEnd;
    $promoVersionKey = md5(implode('|', [
        (string) ($tenant?->id ?? 'tenant'),
        $promoText,
        (string) ($promoImagePath ?? ''),
        $promoStartDate,
        $promoEndDate,
        $promoCtaText,
        $promoCtaUrl,
    ]));
    $pageBg = trim((string) ($meta['landing_page_bg'] ?? ''));
    if ($pageBg === '' || ! preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $pageBg)) {
        $pageBg = '#f4f2ee';
    }
    $roomsList = $rooms->take(6);
    $bookedRoomIds = $bookedRoomIds ?? [];
    $availableRoomsCount = $rooms->filter(fn ($r) => ! in_array((int) $r->id, $bookedRoomIds, true))->count();
    $roomsForBooking = $rooms->map(function ($r) use ($bookedRoomIds) {
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
            'is_booked' => in_array((int) $r->id, $bookedRoomIds, true),
        ];
    })->values()->all();
    $heroRoomSlides = $rooms
        ->filter(fn ($r) => ! in_array((int) $r->id, $bookedRoomIds, true))
        ->map(function ($r) {
            $coverPath = $r->image_path ?: $r->images->first()?->image_path;
            $imageUrl = $coverPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($coverPath) : '';
            if ($imageUrl === '') {
                return null;
            }
            $typeLabel = $r->type ? ucfirst((string) $r->type) : __('Room');
            $desc = trim(strip_tags((string) ($r->description ?? '')));
            $tagline = $desc !== ''
                ? \Illuminate\Support\Str::limit($desc, 120)
                : __('From :price / night', ['price' => number_format((float) $r->price_per_night, 0)]);

            return [
                'id' => (int) $r->id,
                'name' => $r->name,
                'type' => $typeLabel,
                'tagline' => $tagline,
                'image_url' => $imageUrl,
            ];
        })
        ->filter()
        ->values()
        ->take(12)
        ->all();
    $useHeroRoomSlideshow = count($heroRoomSlides) > 0;
    $seoOgImageUrl = $seoOgImagePath
        ? asset('storage/' . $seoOgImagePath)
        : ($useHeroRoomSlideshow
            ? $heroRoomSlides[0]['image_url']
            : ($heroMediaType === 'image' && $heroMediaUrl ? $heroMediaUrl : asset('images/background.jpg')));
    $seoFaviconUrl = $seoFaviconPath ? asset('storage/' . $seoFaviconPath) : ($logoUrl ?: asset('favicon.ico'));
    $seoTitle = $seoMetaTitle !== '' ? $seoMetaTitle : ($siteName . ' · Book Your Stay');
    $seoDescription = $seoMetaDescription !== '' ? $seoMetaDescription : 'Explore our rooms and cottages, check availability, and reserve your stay in just a few clicks.';
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
    @keyframes landingFadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes landingSoftPulse {
        0%, 100% { transform: scale(1); opacity: .22; }
        50% { transform: scale(1.06); opacity: .32; }
    }
    .landing-reveal {
        opacity: 0;
        animation: landingFadeUp .65s cubic-bezier(.22,1,.36,1) forwards;
    }
    .landing-glow {
        animation: landingSoftPulse 7s ease-in-out infinite;
    }
</style>
@endpush

@section('body')
<div class="tenant-landing-surface min-h-screen min-w-0" x-data="{
    browseModalOpen: false,
    bookModalOpen: false,
    selectedRoom: null,
    bookCheckIn: '',
    bookCheckOut: '',
    promoVisible: {{ $showPromo ? 'true' : 'false' }},
    promoDismissible: {{ $promoDismissible ? 'true' : 'false' }},
    promoFrequencyDays: {{ $promoFrequencyDays }},
    promoStorageKey: 'tenant-promo-dismissed-{{ $promoVersionKey }}',
    heroSlides: @js($heroRoomSlides),
    heroSlideIndex: 0,
    heroSlideTimer: null,
    roomsForBooking: @js($roomsForBooking),
    init() {
        if (this.promoVisible && this.promoDismissible) {
            try {
                const raw = localStorage.getItem(this.promoStorageKey);
                if (raw) {
                    const dismissedAt = Number(raw);
                    if (!Number.isNaN(dismissedAt)) {
                        const capMs = Math.max(0, this.promoFrequencyDays) * 24 * 60 * 60 * 1000;
                        if (capMs === 0) {
                            this.promoVisible = false;
                        } elseif ((Date.now() - dismissedAt) < capMs) {
                            this.promoVisible = false;
                        }
                    }
                }
            } catch (_) {}
        }
        if (Array.isArray(this.heroSlides) && this.heroSlides.length > 1) {
            this.heroSlideTimer = setInterval(() => {
                this.heroSlideIndex = (this.heroSlideIndex + 1) % this.heroSlides.length;
            }, 4200);
        }

        const reopenRoomId = @js(session('openBookModalRoomId'));
        if (reopenRoomId) {
            this.openBookModal(reopenRoomId);
        }
    },
    dismissPromo() {
        this.promoVisible = false;
        if (!this.promoDismissible) return;
        try {
            localStorage.setItem(this.promoStorageKey, String(Date.now()));
        } catch (_) {}
    },
    openBookModal(roomId) {
        const room = this.roomsForBooking.find(r => r.id == roomId) || null;
        if (room && room.is_booked) return;
        this.selectedRoom = room;
        this.bookModalOpen = !!room;
        this.browseModalOpen = false;
        this.bookCheckIn = '';
        this.bookCheckOut = '';
    },
    closeBookModal() {
        this.bookModalOpen = false;
        this.selectedRoom = null;
        this.bookCheckIn = '';
        this.bookCheckOut = '';
    },
    nights() {
        if (!this.bookCheckIn || !this.bookCheckOut) return 0;
        const a = new Date(this.bookCheckIn);
        const b = new Date(this.bookCheckOut);
        if (Number.isNaN(a.getTime()) || Number.isNaN(b.getTime())) return 0;
        const diff = Math.ceil((b.getTime() - a.getTime()) / 86400000);
        return Math.max(0, diff);
    },
    payable() {
        const n = this.nights();
        const rate = Number(this.selectedRoom?.price_per_night || 0);
        if (!rate || n <= 0) return 0;
        return rate * n;
    },
    money(v) {
        try {
            return Number(v || 0).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        } catch (e) {
            return String(v || 0);
        }
    }
}" @keydown.escape.window="bookModalOpen = false; browseModalOpen = false; selectedRoom = null">
@if (session('success') || session('error'))
    <div class="fixed inset-x-0 top-4 z-[80] flex justify-center px-4">
        <div class="w-full max-w-2xl rounded-2xl border px-4 py-3 text-sm shadow-lg backdrop-blur-md"
             style="background: rgba(255,255,255,.92); border-color: {{ $primary }}33;">
            @if(session('success'))
                <p class="font-semibold text-emerald-700">{{ session('success') }}</p>
            @endif
            @if(session('error'))
                <p class="font-semibold text-rose-700">{{ session('error') }}</p>
            @endif
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="fixed inset-x-0 top-4 z-[80] flex justify-center px-4">
        <div class="w-full max-w-2xl rounded-2xl border border-rose-200 bg-rose-50/95 px-4 py-3 text-sm shadow-lg backdrop-blur-md">
            <p class="font-semibold text-rose-800">{{ __('Please fix the errors and try again.') }}</p>
            <ul class="mt-1 list-disc pl-5 text-rose-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if($showPromo)
<div x-show="promoVisible" x-cloak class="pointer-events-none fixed bottom-4 right-4 z-[70] sm:bottom-6 sm:right-6">
    <div class="pointer-events-auto relative w-[19rem] max-w-[calc(100vw-2rem)] overflow-hidden rounded-3xl border shadow-2xl ring-1 ring-white/35 backdrop-blur-md"
         style="border-color: {{ $primary }}55; background: linear-gradient(150deg, color-mix(in srgb, {{ $primary }} 76%, #ffffff 24%), color-mix(in srgb, {{ $secondary }} 82%, #ffffff 18%));">
        <div class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-white/20 blur-2xl"></div>
        <div class="pointer-events-none absolute -left-10 bottom-0 h-24 w-24 rounded-full bg-black/10 blur-2xl"></div>

        <div class="flex items-center justify-between px-4 pt-3">
            <span class="inline-flex items-center rounded-full border border-white/50 bg-white/25 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-white">
                {{ __('Promo') }}
            </span>
            @if($promoDismissible)
                <button type="button" @click="dismissPromo()"
                    class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-white/85 transition hover:bg-white/20 hover:text-white"
                    aria-label="Dismiss announcement">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            @endif
        </div>

        @if($promoImageUrl)
            <div class="px-4 pt-2">
                <img src="{{ $promoImageUrl }}" alt="Promo image" class="h-24 w-full rounded-2xl object-cover ring-1 ring-white/45 shadow-sm">
            </div>
        @endif

        <div class="px-4 pb-4 pt-3">
            <p class="min-w-0 break-words text-[13px] font-semibold leading-relaxed text-white sm:text-sm" style="text-shadow: 0 1px 1px rgba(0,0,0,.16);">{{ $promoText }}</p>
            @if($promoCtaText !== '')
                <a href="{{ $promoCtaUrl }}"
                   class="mt-3 inline-flex items-center rounded-full border border-white/45 bg-white/20 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-white/30">
                    {{ $promoCtaText }}
                </a>
            @endif
        </div>
    </div>
</div>
@endif
<!-- Tenant nav — floating translucent bar -->
<div class="sticky top-0 z-40 pt-3 sm:pt-4 px-4 sm:px-6 lg:px-8 pointer-events-none">
    <header class="pointer-events-auto mx-auto max-w-7xl min-w-0 rounded-2xl border border-white/50 bg-white/40 backdrop-blur-xl shadow-lg shadow-slate-900/[0.07] ring-1 ring-slate-900/[0.04]">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex h-14 min-w-0 items-center justify-between gap-3 sm:h-16">
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
                @auth('regular_user')
                    <a href="{{ tenant_url('user/dashboard') }}"
                       class="hidden items-center justify-center rounded-full border border-slate-300 px-4 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 sm:inline-flex">
                        {{ __('My dashboard') }}
                    </a>
                    <form method="POST" action="{{ tenant_url('user/logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-full border border-rose-200 bg-rose-50 px-4 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                            {{ __('Log out') }}
                        </button>
                    </form>
                @else
                    <a href="{{ tenant_url('user/login') }}"
                       class="hidden items-center justify-center rounded-full border border-slate-300 px-4 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 sm:inline-flex">
                        {{ __('Login') }}
                    </a>
                @endauth

                <button type="button" @click="browseModalOpen = true"
                   class="inline-flex items-center justify-center rounded-full px-4 py-1.5 text-xs font-semibold text-white shadow-md transition hover:opacity-95"
                   style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
                    {{ __('Browse & book') }}
                </button>
            </div>
            </div>
        </div>
    </header>
</div>

<div class="flex flex-col">
@if($showHero)
<section class="relative overflow-hidden" style="order: {{ (int) ($sectionOrderMap['hero'] ?? 0) }};">
    <div class="landing-glow pointer-events-none absolute -left-24 top-16 h-64 w-64 rounded-full blur-3xl" style="background: {{ $primary }}33;"></div>
    <div class="landing-glow pointer-events-none absolute -right-20 bottom-10 h-56 w-56 rounded-full blur-3xl" style="background: {{ $secondary }}2f; animation-delay: 1.2s;"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20 lg:pb-24 relative">
        <div class="grid lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] gap-10 lg:gap-14 items-start">
            <div class="landing-reveal" style="animation-delay: .05s;">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-slate-200 text-[11px] text-slate-500 mb-5 shadow-sm landing-reveal" style="animation-delay: .12s;">
                    <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $primary }};"></span>
                    {{ $heroBadge }}
                    <span class="text-slate-400">·</span>
                    <span class="text-slate-600">{{ $siteName }}</span>
                </div>

                <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl xl:text-[3.25rem] xl:leading-[1.08] landing-reveal" style="animation-delay: .2s;">
                    {{ $heroTitle }}
                    <span class="mt-1 block font-medium" style="color: {{ $primary }};">{{ __('at') }} {{ $siteName }}.</span>
                </h1>

                <p class="mt-5 text-sm sm:text-base text-slate-600 max-w-xl landing-reveal" style="animation-delay: .32s;">
                    {{ $heroSubtitle }}
                </p>

                <div class="mt-6 flex flex-wrap gap-5 text-xs text-slate-500 landing-reveal" style="animation-delay: .44s;">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-slate-900">{{ $availableRoomsCount }} available</span>
                        <span class="text-slate-500">Rooms & cottages</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-slate-900">Online booking</span>
                        <span class="text-slate-500">Instant confirmation</span>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3 landing-reveal" style="animation-delay: .54s;">
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

            <div class="relative landing-reveal" style="animation-delay: .18s;">
                <div class="relative rounded-3xl overflow-hidden shadow-2xl bg-slate-900 transition duration-500 hover:-translate-y-0.5">
                    @if($useHeroRoomSlideshow)
                        <template x-for="(slide, i) in heroSlides" :key="'hero-slide-' + slide.id">
                            <img x-show="heroSlideIndex === i"
                                 x-transition:enter="transition-opacity duration-500"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 :src="slide.image_url"
                                 :alt="slide.name"
                                 class="absolute inset-0 h-72 w-full object-cover">
                        </template>
                        <div class="h-72 w-full"></div>
                        <div class="absolute bottom-3 right-3 flex items-center gap-1.5 rounded-full bg-black/35 px-2 py-1 backdrop-blur-sm">
                            <template x-for="(slide, i) in heroSlides" :key="'hero-dot-' + slide.id">
                                <button type="button"
                                        @click="heroSlideIndex = i"
                                        :class="heroSlideIndex === i ? 'bg-white' : 'bg-white/45'"
                                        class="h-1.5 w-1.5 rounded-full transition"
                                        :aria-label="'Go to slide ' + (i + 1)"></button>
                            </template>
                        </div>
                    @elseif($heroMediaUrl && $heroMediaType === 'video')
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
                    <div class="absolute inset-x-0 bottom-0 px-6 pb-5 pt-16 flex flex-col justify-end pointer-events-none"
                         style="background: linear-gradient(to top, rgba(2, 6, 23, {{ $heroOverlayAlpha }}), rgba(2, 6, 23, {{ min(0.95, (float)$heroOverlayAlpha + 0.15) }}), rgba(2, 6, 23, 0));">
                        @if($useHeroRoomSlideshow)
                            <template x-for="(slide, i) in heroSlides" :key="'hero-caption-' + slide.id">
                                <div x-show="heroSlideIndex === i"
                                     x-transition:enter="transition-opacity duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     class="text-left">
                                    <div class="text-xs text-slate-200 flex items-center gap-2 mb-1 flex-wrap">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 border text-white/90 border-white/30" style="background-color: {{ $primary }}cc;">
                                            {{ $siteName }}
                                        </span>
                                        <span class="text-slate-200/95" x-text="slide.type"></span>
                                    </div>
                                    <p class="text-base font-semibold text-white tracking-tight" x-text="slide.name"></p>
                                    <p class="text-sm text-slate-100/90 mt-1 line-clamp-2" x-text="slide.tagline"></p>
                                </div>
                            </template>
                        @else
                            <div class="text-xs text-slate-200 flex items-center gap-2 mb-1">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 border text-white/90 border-white/30" style="background-color: {{ $primary }}40;">
                                    {{ $siteName }}
                                </span>
                                <span class="text-slate-300">{{ $heroBadge }}</span>
                            </div>
                            <p class="text-sm text-slate-100">
                                {{ $heroSubtitle }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <a href="#rooms" class="landing-reveal rounded-2xl border border-slate-200 bg-white p-3 text-xs text-slate-600 shadow-sm hover:-translate-y-0.5 hover:border-slate-300 transition text-left block" style="animation-delay: .62s;">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold text-slate-900">Dates</span>
                            <span class="text-[10px]" style="color: {{ $primary }};">Pick on booking</span>
                        </div>
                        <p>Choose check‑in & check‑out when you book a room.</p>
                    </a>
                    <a href="#rooms" class="landing-reveal rounded-2xl border border-slate-200 bg-white p-3 text-xs text-slate-600 shadow-sm hover:-translate-y-0.5 hover:border-slate-300 transition text-left block" style="animation-delay: .7s;">
                        <span class="font-semibold text-slate-900 block mb-1">Rooms</span>
                        <p>See rooms and cottages with transparent pricing below.</p>
                    </a>
                    <a href="{{ tenant_url('user/register') }}" class="landing-reveal rounded-2xl border border-slate-200 bg-white p-3 text-xs text-slate-600 shadow-sm hover:-translate-y-0.5 hover:border-slate-300 transition block" style="animation-delay: .78s;">
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
                        $isBooked = in_array((int) $room->id, $bookedRoomIds ?? [], true);
                    @endphp
                    <button type="button" @click="openBookModal({{ $room->id }})" @if($isBooked) disabled @endif
                       class="landing-reveal group w-full overflow-hidden rounded-3xl bg-white text-left shadow-sm ring-1 ring-slate-200/60 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:ring-slate-300/80 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:translate-y-0 disabled:hover:shadow-sm"
                       style="animation-delay: {{ number_format(0.08 * (($loop->index % 6) + 1), 2) }}s;">
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
                            @if($isBooked)
                                <span class="absolute right-4 top-4 inline-flex rounded-full px-3 py-1.5 text-xs font-bold text-white shadow-sm ring-1 ring-white/30"
                                      style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
                                    {{ __('Booked') }}
                                </span>
                            @endif
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
                                {{ $isBooked ? __('Unavailable') : __('Book this stay') }}
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
         class="fixed inset-0 bg-slate-900/55 backdrop-blur-sm"></div>
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
         class="fixed inset-0 bg-slate-900/55 backdrop-blur-sm"></div>
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
            <x-form-with-busy method="POST" action="{{ $storeUrl }}" enctype="multipart/form-data" class="space-y-4" :overlay="false" busy-message="{{ __('Sending your booking…') }}">
                @csrf
                <input type="hidden" name="room_id" :value="selectedRoom ? selectedRoom.id : ''">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="book_check_in" class="block text-sm font-medium text-slate-900 mb-1">Check-in</label>
                        <input type="date" id="book_check_in" name="check_in" required min="{{ date('Y-m-d') }}"
                               x-ref="bookCheckIn"
                               x-model="bookCheckIn"
                               @change="let d = $event.target.value; bookCheckIn = d; if (d && $refs.bookCheckOut) { let next = new Date(d); next.setDate(next.getDate() + 1); $refs.bookCheckOut.min = next.toISOString().slice(0,10); if ($refs.bookCheckOut.value && $refs.bookCheckOut.value < $refs.bookCheckOut.min) { $refs.bookCheckOut.value = ''; bookCheckOut = ''; } }"
                               class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                    </div>
                    <div>
                        <label for="book_check_out" class="block text-sm font-medium text-slate-900 mb-1">Check-out</label>
                        <input type="date" id="book_check_out" name="check_out" required
                               x-ref="bookCheckOut"
                               x-model="bookCheckOut"
                               @input="bookCheckOut = $event.target.value"
                               class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Amount payable') }}</p>
                        <p class="text-sm font-bold text-teal-700 tabular-nums">
                            ₱<span x-text="money(payable())"></span>
                        </p>
                    </div>
                    <p class="mt-1 text-xs text-slate-600">
                        <span x-text=\"nights() ? (nights() + ' night(s) × ₱' + money(selectedRoom ? selectedRoom.price_per_night : 0) + '/night') : @js(__('Select your dates to calculate the total.'))\"></span>
                    </p>
                </div>

                @auth('regular_user')
                    <p class="text-sm text-slate-600">Booking as <strong>{{ auth('regular_user')->user()->name }}</strong> ({{ auth('regular_user')->user()->email }})</p>
                @else
                    <div class="space-y-4">
                        <div>
                            <label for="book_guest_name" class="block text-sm font-medium text-slate-900 mb-1">Your name</label>
                            <input type="text" id="book_guest_name" name="guest_name" required
                                   value="{{ old('guest_name') }}"
                                   {{ \App\Support\InputHtmlAttributes::personName() }}
                                   class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                            @error('guest_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="book_guest_email" class="block text-sm font-medium text-slate-900 mb-1">Email</label>
                            <input type="email" id="book_guest_email" name="guest_email" required
                                   value="{{ old('guest_email') }}"
                                   {{ \App\Support\InputHtmlAttributes::email() }}
                                   class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                            @error('guest_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="book_guest_phone" class="block text-sm font-medium text-slate-900 mb-1">Phone (optional)</label>
                            <input type="text" id="book_guest_phone" name="guest_phone"
                                   value="{{ old('guest_phone') }}"
                                   {{ \App\Support\InputHtmlAttributes::phone() }}
                                   class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                            @error('guest_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                @endauth

                <div>
                    <label for="book_notes" class="block text-sm font-medium text-slate-900 mb-1">Notes (optional)</label>
                    <textarea id="book_notes" name="notes" rows="2" placeholder="Special requests..."
                              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900"></textarea>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ __('Payment details (required)') }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ __('Upload proof now so your request includes payment right away.') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 mb-1">{{ __('Payment type') }}</label>
                        <select name="payment_type" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                            <option value="">{{ __('Select') }}</option>
                            <option value="full" @selected(old('payment_type') === 'full')>{{ __('Full payment') }}</option>
                            <option value="partial" @selected(old('payment_type') === 'partial')>{{ __('Partial payment') }}</option>
                        </select>
                        @error('payment_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 mb-1">{{ __('Full Name') }}</label>
                        <input name="payer_full_name" type="text" value="{{ old('payer_full_name') }}" required
                               {{ \App\Support\InputHtmlAttributes::personName() }}
                               class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                        @error('payer_full_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-1">{{ __('Payment method') }}</label>
                            <select name="payer_gcash_no" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                                <option value="">{{ __('Select method') }}</option>
                                @foreach (['GCash', 'Maya', 'GrabPay', 'ShopeePay', 'Coins.ph', 'BPI Online', 'BDO Online', 'UnionBank Online', 'PNB Digital', 'Other wallet / bank'] as $method)
                                    <option value="{{ $method }}" @selected(old('payer_gcash_no') === $method)>{{ $method }}</option>
                                @endforeach
                            </select>
                            @error('payer_gcash_no')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-1">{{ __('Ref. No.') }}</label>
                            <input name="payer_ref_no" type="text" value="{{ old('payer_ref_no') }}" required
                                   {{ \App\Support\InputHtmlAttributes::reference() }}
                                   class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                            @error('payer_ref_no')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-1">{{ __('Amount paid (PHP)') }}</label>
                            <input name="amount_paid" type="number" step="0.01" min="0" value="{{ old('amount_paid') }}" required
                                   {{ \App\Support\InputHtmlAttributes::money() }}
                                   class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900">
                            @error('amount_paid')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-1">{{ __('Payment proof') }}</label>
                            <input name="payment_proof" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required
                                   class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 file:mr-2 file:rounded-lg file:border-0 file:bg-slate-100 file:px-2 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200"/>
                            @error('payment_proof')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
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
