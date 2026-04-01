@props([
    'url',
    'initials' => null,
    'subtitle' => null,
])

@php
    $brandName = trim((string) $slot) !== '' ? trim((string) $slot) : (string) config('app.name');
    $initialsStr = is_string($initials) ? trim($initials) : '';
    $showInitials = $initialsStr !== '';
    $subtitleStr = is_string($subtitle) ? trim($subtitle) : '';
    $logoUrl = config('mail.logo_url');
    if (! filled($logoUrl)) {
        $path = trim((string) config('mail.logo_path', ''));
        if ($path !== '') {
            $logoUrl = asset(ltrim($path, '/'));
        }
    }
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;" @if($subtitleStr !== '') title="{{ e($subtitleStr) }}" @endif>
@if ($showInitials)
<table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 auto;">
<tr>
<td align="center" style="padding:0;">
<table cellpadding="0" cellspacing="0" role="presentation" style="margin:0 auto;">
<tr>
<td align="center" valign="middle" height="56" width="56" style="width:56px;height:56px;background-color:#0d9488;border-radius:14px;mso-line-height-rule:exactly;">
<span style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:20px;font-weight:700;line-height:56px;color:#ffffff;letter-spacing:0.04em;">{{ $initialsStr }}</span>
</td>
</tr>
@if ($subtitleStr !== '')
<tr>
<td align="center" style="padding-top:10px;">
<span style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;color:#3d4852;">{{ $subtitleStr }}</span>
</td>
</tr>
@endif
</table>
</td>
</tr>
</table>
@elseif (filled($logoUrl))
<img src="{{ $logoUrl }}" class="logo" alt="{{ $brandName }}">
@else
{{ $brandName }}
@endif
</a>
</td>
</tr>
