<div
    class="kid-card relative overflow-hidden"
    x-data="{
        openConfirm: false,
        isDone: @js($completed),
        showStars: false,
        completeModalOpen: false,
        completeModalBonusPercent: 0,
        completeModalBonusTypeKey: 'no_bonus',
        completeModalTaskPoints: 0,
        completeModalBonusPoints: 0,
        completeModalTotalPoints: 0,
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

            const payload = await response.json();

            this.isDone = true;
            this.showStars = true;
            setTimeout(() => this.showStars = false, 1900);

            this.completeModalTaskPoints = Number(payload?.task_points ?? @js($points));
            this.completeModalBonusPercent = Number(payload?.bonus_percent ?? 0);
            this.completeModalBonusPoints = Number(payload?.bonus_points ?? 0);
            this.completeModalTotalPoints = Number(payload?.total_task_points ?? this.completeModalTaskPoints);
            this.completeModalBonusTypeKey = String(payload?.bonus_type_key ?? 'no_bonus');
            this.completeModalOpen = true;

            if (window.Livewire?.dispatch) {
                window.Livewire.dispatch('task-completed');
            } else {
                this.$dispatch('task-completed');
            }
        }
    }"
>
    @if($taskImagePath)
        <div class="-mx-6 -mt-6 mb-4 h-44 w-auto overflow-hidden">
            <img src="{{ \Illuminate\Support\Facades\Storage::url($taskImagePath) }}" alt="{{ $title }} image" class="h-full w-full object-cover" />
        </div>
    @endif

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

    <div x-show="completeModalOpen" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-slate-900/50 p-4" @click.self="completeModalOpen = false">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-xl">
            <p class="text-kid-xl font-bold text-slate-800">Task Completed! 🎉</p>
            <p class="mt-2 text-lg text-slate-600" x-show="completeModalBonusTypeKey === 'no_bonus'">
                Amazing consistency. Keep going!
            </p>
            <p class="mt-2 text-lg text-slate-600" x-show="completeModalBonusTypeKey !== 'no_bonus'">
                Streak bonus active: <span class="font-extrabold text-emerald-600" x-text="`+${completeModalBonusPercent}%`"></span>
            </p>

            <div class="mt-4 rounded-xl bg-slate-50 p-4 text-left text-sm text-slate-700">
                <p>Base points: <span class="font-bold" x-text="completeModalTaskPoints"></span></p>
                <p>Bonus points: <span class="font-bold" x-text="completeModalBonusPoints"></span></p>
                <p>Total earned: <span class="font-extrabold text-emerald-700" x-text="completeModalTotalPoints"></span></p>
            </div>

            <button class="kid-btn kid-btn-success mt-5 w-full" @click="completeModalOpen = false">Awesome!</button>
        </div>
    </div>
</div>
