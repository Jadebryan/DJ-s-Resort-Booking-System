@props([
    'innerClass' => 'min-h-screen flex items-center justify-center px-3 min-w-0 sm:px-4 py-8 sm:py-10',
])
@php
    $tenantCtx = current_tenant();
    if (! $tenantCtx instanceof \App\Models\Tenant) {
        $tenantCtx = request()->attributes->get('tenant');
    }

    $primary = $tenantCtx instanceof \App\Models\Tenant ? (string) ($tenantCtx->primary_color ?? '') : '';
    $secondary = $tenantCtx instanceof \App\Models\Tenant ? (string) ($tenantCtx->secondary_color ?? '') : '';
    $landingBg = $tenantCtx instanceof \App\Models\Tenant ? (string) (($tenantCtx->metadata['landing_page_bg'] ?? '') ?: '') : '';

    $isHex = static fn (string $v): bool => (bool) preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $v);
    $expandHex = static function (string $v): string {
        if (strlen($v) !== 4) {
            return $v;
        }

        return '#' . $v[1] . $v[1] . $v[2] . $v[2] . $v[3] . $v[3];
    };
    $hexToRgba = static function (string $hex, float $alpha): string {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    };

    $primary = $isHex($primary) ? $expandHex($primary) : '#6366f1';
    $secondary = $isHex($secondary) ? $expandHex($secondary) : '#06b6d4';
    $bgBase = $isHex($landingBg) ? $expandHex($landingBg) : '#f8fafc';

    $backdropStyle = "background: radial-gradient(circle at 12% 18%, " . $hexToRgba($primary, 0.24) . " 0%, transparent 42%), radial-gradient(circle at 88% 82%, " . $hexToRgba($secondary, 0.20) . " 0%, transparent 44%), linear-gradient(135deg, {$bgBase} 0%, #ffffff 100%);";
    $blobPrimaryStyle = "background-color: " . $hexToRgba($primary, 0.30) . ';';
    $blobSecondaryStyle = "background-color: " . $hexToRgba($secondary, 0.28) . ';';
@endphp
<div {{ $attributes->merge(['class' => 'relative min-h-screen overflow-hidden']) }}>
    <div class="pointer-events-none absolute inset-0" style="{{ $backdropStyle }}" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -top-20 -left-16 h-64 w-64 rounded-full blur-3xl" style="{{ $blobPrimaryStyle }}" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-20 -right-16 h-64 w-64 rounded-full blur-3xl" style="{{ $blobSecondaryStyle }}" aria-hidden="true"></div>
    <div @class(['relative z-10 w-full', $innerClass])>
        {{ $slot }}
    </div>
</div>
