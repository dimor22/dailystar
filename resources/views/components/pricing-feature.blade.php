@props(['accent' => false])

<li class="flex items-start gap-2.5">
    <span class="mt-0.5 flex-shrink-0 text-base leading-none {{ $accent ? 'text-amber-500' : 'text-sky-500' }}">
        ⭐
    </span>
    <span class="text-sm font-semibold text-slate-700">{{ $slot }}</span>
</li>
