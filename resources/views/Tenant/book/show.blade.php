@php
    $tenant = current_tenant();
    $siteName = $tenant instanceof \App\Models\Tenant ? $tenant->appDisplayName() : 'Resort';
    $primary = $tenant?->primary_color ?? '#0d9488';
    $user = auth('regular_user')->user();
    $coverPath = $room->image_path ?: $room->images->first()?->image_path;
    $coverUrl = $coverPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($coverPath) : null;
    $hue = abs(crc32((string) $room->id . $room->name)) % 360;
@endphp
@extends('layouts.public')

@section('title', $room->name . ' · ' . __('Book') . ' · ' . $siteName)

@push('styles')
<style>
    .booking-form-fields input:focus-visible,
    .booking-form-fields textarea:focus-visible {
        outline: none;
        box-shadow: 0 0 0 2px {{ $primary }};
        border-color: #e2e8f0;
        background-color: #fff;
    }
</style>
@endpush

@section('body')
<div class="min-h-screen" style="background-color:#f4f2ee;background-image:radial-gradient(ellipse 120% 80% at 100% -20%, color-mix(in srgb, {{ $primary }} 12%, transparent), transparent 55%),radial-gradient(ellipse 90% 70% at -10% 60%, color-mix(in srgb, {{ $primary }} 8%, transparent), transparent 50%);">
    <header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/80 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl min-w-0 items-center justify-between gap-4 px-4 py-3.5 sm:px-6 lg:px-8">
            <a href="{{ tenant_url('/') }}" class="font-display min-w-0 truncate text-lg font-semibold tracking-tight text-slate-900 transition hover:opacity-75 sm:text-xl">{{ $siteName }}</a>
            <nav class="flex shrink-0 items-center gap-2 sm:gap-3">
                <a href="{{ tenant_url('user/login') }}" class="rounded-full px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">{{ __('Login') }}</a>
                <a href="{{ tenant_url('user/register') }}" class="rounded-full px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-110" style="background-color: {{ $primary }};">{{ __('Get started') }}</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto min-w-0 max-w-3xl px-4 pb-20 pt-8 sm:px-6 sm:pt-12 lg:px-8">
        <a href="{{ tenant_url('/') }}#rooms" class="group mb-8 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-slate-800">
            <span class="transition group-hover:-translate-x-0.5" aria-hidden="true">←</span>
            {{ __('All stays') }}
        </a>

        <div class="mb-10 overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200/60">
            <div class="relative aspect-[21/9] min-h-[200px] bg-slate-200 sm:aspect-video">
                @if($coverUrl)
                    <img src="{{ $coverUrl }}" alt="{{ $room->name }}" class="h-full w-full object-cover">
                @else
                    <div class="absolute inset-0 flex items-center justify-center"
                         style="background: linear-gradient(145deg, hsl({{ $hue }}, 28%, 88%) 0%, hsl({{ ($hue + 48) % 360 }}, 32%, 82%) 100%);">
                        <span class="text-6xl opacity-35" aria-hidden="true">{{ $room->type === 'cottage' ? '🏠' : '🛏️' }}</span>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-slate-900/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-8">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/80">{{ $room->type === 'cottage' ? __('Cottage') : __('Room') }}</p>
                    <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-white sm:text-3xl">{{ $room->name }}</h1>
                    <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
                        <p class="text-sm text-white/90">
                            @if($room->capacity)
                                {{ __('Up to :n guests', ['n' => $room->capacity]) }}
                            @endif
                        </p>
                        <p class="font-display text-2xl font-semibold tabular-nums text-white">
                            ₱{{ number_format($room->price_per_night, 0) }}<span class="text-sm font-normal text-white/80">/{{ __('night') }}</span>
                        </p>
                    </div>
                </div>
            </div>
            @if($room->description)
                <div class="border-t border-slate-100 px-6 py-5 sm:px-8 sm:py-6">
                    <p class="text-sm leading-relaxed text-slate-600 sm:text-base">{{ $room->description }}</p>
                </div>
            @endif
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60 sm:p-8">
            <h2 class="font-display text-xl font-semibold text-slate-900">{{ __('Reserve your stay') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Choose dates and send a request—the resort will confirm availability.') }}</p>

            @if ($errors->any())
                <ul class="mb-6 mt-6 list-inside list-disc rounded-2xl border border-red-200/80 bg-red-50/90 p-4 text-sm text-red-800">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            @endif

            <x-form-with-busy method="POST" action="{{ tenant_url('book') }}" class="booking-form-fields mt-8 space-y-6" :overlay="true" busy-message="{{ __('Sending your booking…') }}">
                @csrf
                <input type="hidden" name="room_id" value="{{ $room->id }}">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="check_in" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Check-in') }}</label>
                        <input type="date" id="check_in" name="check_in" value="{{ old('check_in', request('check_in')) }}" min="{{ date('Y-m-d') }}" required
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-900 transition">
                    </div>
                    <div>
                        <label for="check_out" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Check-out') }}</label>
                        <input type="date" id="check_out" name="check_out" value="{{ old('check_out', request('check_out')) }}" required
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-900 transition">
                    </div>
                </div>

                @if($user)
                    <p class="rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3 text-sm text-slate-600">
                        {{ __('Booking as') }} <strong>{{ $user->name }}</strong> ({{ $user->email }})
                    </p>
                @else
                    <div class="space-y-4">
                        <div>
                            <label for="guest_name" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Your name') }}</label>
                            <input type="text" id="guest_name" name="guest_name" value="{{ old('guest_name') }}" required
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-900 transition">
                        </div>
                        <div>
                            <label for="guest_email" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Email') }}</label>
                            <input type="email" id="guest_email" name="guest_email" value="{{ old('guest_email') }}" required
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-900 transition">
                        </div>
                        <div>
                            <label for="guest_phone" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Phone') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                            <input type="text" id="guest_phone" name="guest_phone" value="{{ old('guest_phone') }}"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-900 transition">
                        </div>
                    </div>
                @endif

                <div>
                    <label for="notes" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Notes') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                    <textarea id="notes" name="notes" rows="3" class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-slate-900 transition" placeholder="{{ __('Special requests…') }}">{{ old('notes') }}</textarea>
                </div>

                <x-busy-submit class="w-full rounded-2xl py-4 text-base font-semibold text-white shadow-md transition hover:brightness-105 hover:shadow-lg" style="background-color: {{ $primary }};" busy-text="{{ __('Submitting…') }}">
                    {{ __('Submit booking request') }}
                </x-busy-submit>
            </x-form-with-busy>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var checkIn = document.getElementById('check_in');
        var checkOut = document.getElementById('check_out');
        if (checkIn && checkOut) {
            checkIn.addEventListener('change', function() {
                var minOut = this.value || '';
                if (minOut) {
                    var d = new Date(minOut);
                    d.setDate(d.getDate() + 1);
                    checkOut.min = d.toISOString().slice(0, 10);
                }
            });
        }
    });
</script>
@endsection
