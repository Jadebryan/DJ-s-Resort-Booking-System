@props([
    'innerClass' => 'min-h-screen flex items-center justify-center px-3 min-w-0 sm:px-4 py-8 sm:py-10',
])
<div {{ $attributes->merge(['class' => 'relative min-h-screen overflow-hidden']) }}>
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-indigo-100 via-sky-100 to-emerald-100" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -top-20 -left-16 h-64 w-64 rounded-full bg-indigo-300/35 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-20 -right-16 h-64 w-64 rounded-full bg-cyan-300/35 blur-3xl" aria-hidden="true"></div>
    <div @class(['relative z-10 w-full', $innerClass])>
        {{ $slot }}
    </div>
</div>
