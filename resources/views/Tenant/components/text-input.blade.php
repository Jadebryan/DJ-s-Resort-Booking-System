@props(['disabled' => false, 'constraint' => null, 'constraintMax' => null])

@php
    $constraintBag = $constraint === null
        ? new \Illuminate\View\ComponentAttributeBag([])
        : match ($constraint) {
            'personName' => \App\Support\InputHtmlAttributes::personName($constraintMax ?? 255),
            'title' => \App\Support\InputHtmlAttributes::title($constraintMax ?? 255),
            'email' => \App\Support\InputHtmlAttributes::email(),
            'phone' => \App\Support\InputHtmlAttributes::phone($constraintMax ?? 25),
            'primaryDomain' => \App\Support\InputHtmlAttributes::primaryDomain(),
            'reference' => \App\Support\InputHtmlAttributes::reference($constraintMax ?? 80),
            'paymentReference' => \App\Support\InputHtmlAttributes::paymentReference($constraintMax ?? 255),
            'paymentReferenceShort' => \App\Support\InputHtmlAttributes::paymentReference(120),
            'paymentMethod' => \App\Support\InputHtmlAttributes::paymentMethod($constraintMax ?? 80),
            'otp' => \App\Support\InputHtmlAttributes::digitsOtp($constraintMax ?? 6),
            default => new \Illuminate\View\ComponentAttributeBag([]),
        };
@endphp

<input @disabled($disabled) {{ $attributes->merge($constraintBag->all())->merge([
    'class' => 'border-slate-300 bg-white text-slate-900 focus:border-sky-500 focus:ring-sky-500 rounded-md shadow-sm'
]) }}>
