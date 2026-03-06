<div class="space-y-6" wire:poll.20s="loadDashboard">
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="kid-card">
            <p class="text-lg font-semibold text-slate-600">Kid</p>
            <p class="kid-title">{{ $kidName }}</p>
        </div>
        <div class="kid-card">
            <p class="text-lg font-semibold text-slate-600">Points</p>
            <p class="kid-title">{{ $points }}</p>
            <livewire:star-counter :stars="$stars" :key="'stars-'.$kidId.'-'.$stars" />
        </div>
        <div class="kid-card">
            <p class="text-lg font-semibold text-slate-600">Streak</p>
            <p class="kid-title">🔥 {{ $currentStreak }} day(s)</p>
        </div>
    </div>

    <div class="kid-card">
        <div class="mb-2 flex items-center justify-between">
            <p class="text-kid-xl font-bold text-slate-700">Daily Progress</p>
            <p class="text-kid-xl font-bold text-slate-700">{{ $completedCount }}/{{ $taskCount }}</p>
        </div>
        <livewire:progress-bar :current="$completedCount" :total="$taskCount" :key="'progress-'.$kidId.'-'.$completedCount" />
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        @foreach($tasks as $task)
            <livewire:task-card
                :kidId="$kidId"
                :taskId="$task['id']"
                :title="$task['title']"
                :description="$task['description']"
                :points="$task['points']"
                :completed="$task['completed']"
                :key="'task-'.$kidId.'-'.$task['id'].'-'.$task['completed']"
            />
        @endforeach
    </div>

    <livewire:celebration-modal :show="$showCelebration" :key="'celebration-'.$kidId.'-'.$showCelebration" />
</div>
