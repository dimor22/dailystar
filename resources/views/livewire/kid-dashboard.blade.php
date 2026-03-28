<div class="space-y-6" wire:poll.20s="loadDashboard">
    <div class="grid gap-4 lg:grid-cols-2">
        @php
            $cardGradientColor = match ($kid['color'] ?? 'bg-blue-500') {
                'bg-blue-500' => 'rgba(59, 130, 246, 0.24)',
                'bg-pink-500' => 'rgba(236, 72, 153, 0.24)',
                'bg-green-500' => 'rgba(34, 197, 94, 0.24)',
                'bg-yellow-500' => 'rgba(234, 179, 8, 0.24)',
                'bg-purple-500' => 'rgba(168, 85, 247, 0.24)',
                'bg-orange-500' => 'rgba(249, 115, 22, 0.24)',
                default => 'rgba(148, 163, 184, 0.2)',
            };
        @endphp

        <div class="kid-card relative overflow-hidden grid grid-cols-2 gap-4">
            <div
                class="pointer-events-none absolute inset-0"
                style="background: linear-gradient(to bottom left, {{ $cardGradientColor }} 0%, rgba(255, 255, 255, 0) 62%);"
            ></div>

            <div class="relative z-10">
                @if($kidAvatarDisplayMode === 'image' && $kidAvatarImagePath)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($kidAvatarImagePath) }}" alt="{{ $kidName }} avatar" class="mt-3 h-20 w-20 rounded-full object-cover bg-white p-1" />
                @else
                    <div class="mt-3 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-5xl">{{ $kidAvatar }}</div>
                @endif
                <p class="kid-title">{{ $kidName }}</p>
                <p class="text-sm text-slate-500">Timezone: {{ config('app.timezone') }}</p>
            </div>

            <div class="relative z-10">
                <div class="">
                    <p class="text-lg font-bold text-slate-700">Points: {{ $points }}</p>
                    <p class="text-lg font-bold text-slate-700">Stars: {{ $kid['stars'] }} ⭐</p>
                    <p class="text-lg font-bold text-slate-700">Streak: {{ $kid['streak'] }} 🔥</p>
                </div>

                <div class="mt-4">
                    <x-progress-bar :current="$completedCount" :total="$taskCount" />
                </div>
            </div>
        </div>

    </div>



    <div class="grid gap-4 md:grid-cols-2">
        @foreach($tasks as $task)
            <livewire:task-card
                :kidId="$kidId"
                :taskId="$task['id']"
                :title="$task['title']"
                :description="$task['description']"
                :taskImagePath="$task['image_path']"
                :points="$task['points']"
                :completed="$task['completed']"
                :key="'task-'.$kidId.'-'.$task['id'].'-'.$task['completed']"
            />
        @endforeach
    </div>

    <livewire:celebration-modal :show="$showCelebration" :kidId="$kidId" :currentDate="$currentDate" :key="'celebration-'.$kidId.'-'.$currentDate.'-'.($showCelebration ? '1' : '0')" />
</div>
