<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DailyStars</title>
    <link rel="icon" type="image/png" href="{{ asset('dailystars-favicon.png') }}">
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body
    class="min-h-screen bg-sky-100"
    x-data="{
        toasts: [],
        showToast(detail) {
            const payload = Array.isArray(detail) ? detail[0] : detail;
            const message = payload?.message ?? '';

            if (! message) {
                return;
            }

            const id = Date.now() + Math.random();
            const toast = {
                id,
                message,
                type: payload?.type ?? 'success',
            };

            this.toasts.push(toast);

            setTimeout(() => {
                this.toasts = this.toasts.filter((item) => item.id !== id);
            }, 3200);
        },
        dismissToast(id) {
            this.toasts = this.toasts.filter((item) => item.id !== id);
        }
    }"
    x-on:toast.window="showToast($event.detail)"
>
    <main class="mx-auto 2xl:max-w-[80vw] p-6">
        {{ $slot }}
    </main>

    <div class="pointer-events-none fixed right-4 top-4 z-[100] flex w-full max-w-sm flex-col gap-3">
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-transition:enter="transform ease-out duration-200"
                x-transition:enter-start="translate-y-2 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transform ease-in duration-150"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="translate-y-2 opacity-0"
                class="pointer-events-auto overflow-hidden rounded-2xl border bg-white shadow-lg"
                :class="toast.type === 'error'
                    ? 'border-red-200'
                    : toast.type === 'warning'
                        ? 'border-amber-200'
                        : 'border-emerald-200'"
            >
                <div class="flex items-start justify-between gap-3 px-4 py-3">
                    <p
                        class="text-sm font-semibold"
                        :class="toast.type === 'error'
                            ? 'text-red-700'
                            : toast.type === 'warning'
                                ? 'text-amber-700'
                                : 'text-emerald-700'"
                        x-text="toast.message"
                    ></p>
                    <button
                        type="button"
                        class="text-xs font-bold text-slate-400 hover:text-slate-600"
                        @click="dismissToast(toast.id)"
                    >
                        Close
                    </button>
                </div>
            </div>
        </template>
    </div>

    @livewireScripts
</body>
</html>
