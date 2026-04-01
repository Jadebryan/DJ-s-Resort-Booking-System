@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
    'class' => 'border-slate-300 bg-white text-slate-900 focus:border-sky-500 focus:ring-sky-500 rounded-md shadow-sm'
]) }}>
