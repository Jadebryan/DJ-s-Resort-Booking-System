@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-slate-900']) }}>
    {{ $value ?? $slot }}
</label>
