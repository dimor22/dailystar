<div
    x-data="{ visible: @entangle('visible') }"
    x-show="visible"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
>
    <div class="mb-4 flex items-start justify-between gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 text-xl">⭐</span>
            <div>
                <p class="font-extrabold text-amber-900">Free plan limit reached</p>
                <p class="mt-0.5 text-sm font-semibold text-amber-800">{{ $message }}</p>
                <a href="{{ route('parent.billing') }}"
                   class="mt-2 inline-block text-sm font-extrabold text-sky-700 underline decoration-sky-300 underline-offset-4 hover:text-sky-900">
                    Upgrade to Pro →
                </a>
            </div>
        </div>
        <button
            type="button"
            wire:click="dismiss"
            class="flex-shrink-0 rounded-lg p-1 text-amber-700 hover:bg-amber-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
            aria-label="Dismiss">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
