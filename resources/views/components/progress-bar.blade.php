@props([
    'current' => 0,
    'total' => 0,
])

@php
    $currentValue = (int) $current;
    $totalValue = (int) $total;
    $percent = $totalValue > 0 ? (int) round(($currentValue / $totalValue) * 100) : 0;
@endphp

<div>
    <div class="h-5 w-full rounded-full bg-slate-200">
        <div
            class="h-5 rounded-full bg-green-500 transition-all duration-500"
            style="width: {{ $percent }}%"
        ></div>
    </div>
    <p class="mt-1 text-sm font-semibold text-slate-600">{{ $currentValue }} / {{ $totalValue }} complete ({{ $percent }}%)</p>
</div>
