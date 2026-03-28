@props([
    'compact' => false,
])

<div class="relative z-30 border-b border-amber-300/80 bg-amber-100/90 text-amber-900 backdrop-blur">
    <div class="mx-auto w-full px-4 {{ $compact ? 'max-w-6xl py-2' : 'max-w-6xl py-3' }} sm:px-6 lg:px-8">
        <p class="{{ $compact ? 'text-xs' : 'text-sm' }} font-bold leading-relaxed">
            Beta Notice: DailyStars is currently in beta testing. Features, UI/UX, and workflows may change without prior notice. Temporary interruptions or unexpected behavior may occur while improvements are being deployed.
        </p>
    </div>
</div>
