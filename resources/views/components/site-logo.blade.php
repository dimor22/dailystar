@props([
    'src' => asset('images/dailystars-logo-2.png'),
    'alt' => 'DailyStars',
])

<img
    src="{{ $src }}"
    alt="{{ $alt }}"
    {{ $attributes->merge(['class' => 'object-contain']) }}
/>
