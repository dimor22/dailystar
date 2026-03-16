<div
    class="kid-card relative"
    x-data="{
        openConfirm: false,
        isDone: @js($completed),
        showStars: false,
        async completeTask() {
            if (this.isDone) return;

            const token = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
            const response = await fetch('/api/complete-task', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                },
                body: JSON.stringify({ kid_id: @js($kidId), task_id: @js($taskId) }),
            });

            this.openConfirm = false;

            if (!response.ok) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Unable to complete task.',
                        type: 'error',
                    },
                }));
                return;
            }

            this.isDone = true;
            this.showStars = true;
            setTimeout(() => this.showStars = false, 1900);

            if (window.Livewire?.dispatch) {
                window.Livewire.dispatch('task-completed');
            } else {
                this.$dispatch('task-completed');
            }
        }
    }"
>
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-kid-xl font-bold text-slate-800">{{ $title }}</h3>
        <span class="rounded-full bg-amber-200 px-3 py-1 text-lg font-bold text-amber-700">+{{ $points }}</span>
    </div>
    @if($description)
        <p class="mt-2 text-lg text-slate-600">{{ $description }}</p>
    @endif

    <button
        type="button"
        class="mt-4 w-full kid-btn"
        :class="isDone ? 'bg-slate-400 cursor-not-allowed' : 'bg-emerald-500'"
        @click="openConfirm = !isDone"
        :disabled="isDone"
    >
        <span x-show="!isDone">Mark Complete ⭐</span>
        <span x-show="isDone">Completed ✅</span>
    </button>

    <div x-show="openConfirm" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-slate-900/50 p-4">
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-xl">
            <p class="text-kid-xl font-bold text-slate-800">Complete this task?</p>
            <div class="mt-5 flex gap-3">
                <button class="kid-btn kid-btn-success w-full" @click="completeTask">Yes</button>
                <button class="kid-btn kid-btn-warn w-full" @click="openConfirm = false">No</button>
            </div>
        </div>
    </div>

    <div x-show="showStars" x-transition class="pointer-events-none absolute inset-0 grid place-items-center text-5xl">
        <span class="animate-pulse">✨⭐✨</span>
    </div>
</div>
