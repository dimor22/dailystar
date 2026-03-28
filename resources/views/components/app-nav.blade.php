@props([])

<div x-data="{ open: false }" x-on:keydown.escape.window="open = false">

    {{-- Top bar: always visible on every screen size --}}
    <div class="flex items-center justify-between gap-3 mb-10">

        {{-- Logo: compact on mobile, full-size on desktop --}}
        <x-site-logo class="w-28 sm:w-44 lg:w-64" />

        {{-- Desktop nav buttons (hidden on mobile) --}}
        <div class="hidden lg:flex flex-wrap items-center justify-end gap-3">
            {{ $slot }}
        </div>

        {{-- Mobile "Menu" button (hidden on desktop) --}}
        <button
            type="button"
            class="lg:hidden kid-btn kid-btn-primary flex items-center gap-2"
            @click="open = true"
            :aria-expanded="open.toString()"
            aria-label="Open navigation menu"
        >
            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    {{-- Full-screen sliding mobile menu overlay --}}
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 left-0 sm:left-auto sm:w-1/2 z-50 flex flex-col overflow-y-auto bg-sky-200 shadow-2xl p-8 lg:hidden"
        role="dialog"
        aria-modal="true"
        aria-label="Navigation menu"
    >
        {{-- Overlay header: logo + close button --}}
        <div class="mb-12 flex items-center justify-between">
            <x-site-logo class="w-44" />
            <button
                type="button"
                class="kid-btn kid-btn-warn flex items-center gap-2"
                @click="open = false"
                aria-label="Close navigation menu"
            >
                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Nav links stacked vertically, full-width --}}
        <nav class="flex flex-col gap-4 [&>a]:w-full [&>a]:text-center [&>form]:w-full [&>form>button]:w-full">
            {{ $slot }}
        </nav>
    </div>

</div>
