<div class="space-y-6" wire:poll.5s="loadDashboard" x-data="{ settingsOpen: false }">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-start sm:justify-between">
        <div>
            <h1 class="kid-title">Parent Dashboard</h1>
            <p class="text-sm text-slate-500">{{ $dashboardDateTime }}</p>
        </div>
        {{-- Parent's email --}}
        <div class="flex items-center gap-4">
            <div class="sm:text-right">
                <p class="text-slate-500">{{ $parentEmail }}</p>
                <button
                    type="button"
                    class="text-sm text-slate-400 hover:text-slate-600"
                    @click="settingsOpen = true"
                >
                    Timezone: {{ $parentTimezone }}
                </button>
            </div>
        </div>
    </div>

    <div
        x-show="settingsOpen"
        x-cloak
        class="fixed inset-0 z-50 grid place-items-center bg-slate-900/50 p-4"
        @keydown.escape.window="settingsOpen = false"
        @click.self="settingsOpen = false"
    >
        <div class="kid-card w-full max-w-xl">
            <div class="flex items-center justify-between">
                <h2 class="text-kid-xl font-bold text-slate-800">Account Settings</h2>
                <button type="button" class="text-sm font-semibold text-slate-500 hover:text-slate-700" @click="settingsOpen = false">Close</button>
            </div>

            <form wire:submit="updateTimezone" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="w-full">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Timezone</label>
                    <select wire:model.live="timezone" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                        @foreach($timezones as $tz)
                            <option value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </select>
                    @error('timezone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="kid-btn kid-btn-primary">Save Timezone</button>
            </form>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        @forelse($kids as $kid)
            @php
                $cardGradientColor = match ($kid['color']) {
                    'bg-blue-500' => 'rgba(59, 130, 246, 0.24)',
                    'bg-pink-500' => 'rgba(236, 72, 153, 0.24)',
                    'bg-green-500' => 'rgba(34, 197, 94, 0.24)',
                    'bg-yellow-500' => 'rgba(234, 179, 8, 0.24)',
                    'bg-purple-500' => 'rgba(168, 85, 247, 0.24)',
                    'bg-orange-500' => 'rgba(249, 115, 22, 0.24)',
                    default => 'rgba(148, 163, 184, 0.2)',
                };
            @endphp
            <div
                class="kid-card relative overflow-hidden transition-all duration-500"
                x-data="{ flash: @js($kid['just_updated']) }"
                x-init="if (flash) { setTimeout(() => flash = false, 2600) }"
                :class="flash ? 'ring-4 ring-emerald-400 bg-emerald-50/90 shadow-[0_0_0_6px_rgba(52,211,153,0.22)] [animation:pulse_0.45s_ease-in-out_infinite]' : ''"
            >
                <div
                    class="pointer-events-none absolute inset-0"
                    style="background: linear-gradient(to bottom left, {{ $cardGradientColor }} 0%, rgba(255, 255, 255, 0) 62%);"
                ></div>

                <div class="relative z-10 flex items-center justify-between">
                    @if($kid['avatar_display_mode'] === 'image' && $kid['avatar_image_path'])
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($kid['avatar_image_path']) }}" alt="{{ $kid['name'] }} avatar" class="h-12 w-12 rounded-full object-cover bg-white p-1" />
                    @else
                        <p class="text-4xl">{{ $kid['avatar'] }}</p>
                    @endif
                    <span class="rounded-full px-3 py-1 text-2xl font-bold text-white {{ $kid['color'] }}">{{ $kid['name'] }}</span>
                </div>

                <div class="relative z-10 mt-3">
                    <x-progress-bar :current="$kid['completed']" :total="$kid['total']" />
                </div>

                <div class="relative z-10 my-3 grid grid-cols-3 gap-4">
                    <p class="text-lg font-bold text-slate-700">Points: {{ $kid['points'] }}</p>
                    <p class="text-lg font-bold text-slate-700">Stars: {{ $kid['stars'] }} ⭐</p>
                    <p class="text-lg font-bold text-slate-700">Streak: {{ $kid['streak'] }} 🔥</p>
                </div>

                <p
                    x-show="flash"
                    x-transition.opacity.duration.300ms
                    class="relative z-10 mt-2 inline-flex rounded-full bg-emerald-500 px-3 py-1 text-xs font-bold text-white"
                >
                    Task completed
                </p>


                <div class="relative z-10 mt-2 grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-lg bg-emerald-100 px-2 py-2 text-emerald-700">
                        <p class="font-semibold">Completed</p>
                        @if(count($kid['completed_task_names']) > 0)
                            <ul class="mt-1 space-y-1">
                                @foreach($kid['completed_task_names'] as $taskName)
                                    <li class="leading-tight">• {{ $taskName }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-1 font-semibold">None</p>
                        @endif
                    </div>
                    <div class="rounded-lg bg-slate-100 px-2 py-2 text-slate-700">
                        <p class="font-semibold">Not completed</p>
                        @if(count($kid['pending_task_names']) > 0)
                            <ul class="mt-1 space-y-1">
                                @foreach($kid['pending_task_names'] as $taskName)
                                    <li class="leading-tight">• {{ $taskName }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-1 font-semibold">None</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="kid-card md:col-span-2 lg:col-span-4 w-full text-center">
                <a href="{{ route('parent.kids') }}" class="kid-btn kid-btn-primary inline-block">No kids found yet — Add Kids</a>
            </div>
        @endforelse
    </div>

    <div class="kid-card">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-kid-xl font-bold text-slate-800">Activity Log</h2>
                <p class="text-sm text-slate-500">Search by kid, task, action, or time.</p>
            </div>

            <div class="w-full sm:w-80">
                <label for="activity-search" class="mb-1 block text-sm font-semibold text-slate-700">Search Logs</label>
                <input
                    id="activity-search"
                    type="text"
                    wire:model.live.debounce.300ms="activitySearch"
                    placeholder="Try: Ava, homework, 8:30"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2"
                >
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2">
            <button
                type="button"
                wire:click="previousActivityDayPage"
                class="rounded-lg bg-slate-200 px-3 py-1 text-sm font-semibold text-slate-700 transition hover:bg-slate-300 disabled:cursor-not-allowed disabled:opacity-50"
                @disabled($activityDayPage <= 1)
            >
                Previous Day
            </button>
            <p class="text-sm font-semibold text-slate-600">
                Day Page {{ $activityDayPage }} of {{ $activityTotalDayPages }}
                <span class="font-normal">({{ $activityTotalDays }} total days)</span>
            </p>
            <button
                type="button"
                wire:click="nextActivityDayPage"
                class="rounded-lg bg-slate-200 px-3 py-1 text-sm font-semibold text-slate-700 transition hover:bg-slate-300 disabled:cursor-not-allowed disabled:opacity-50"
                @disabled($activityDayPage >= $activityTotalDayPages)
            >
                Next Day
            </button>
        </div>

        <div class="mt-4 space-y-4">
            @forelse($activityLogs as $day)
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-2">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-600">{{ $day['date_label'] }}</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-lg">
                            <thead>
                                <tr class="border-b-2 border-slate-200">
                                    <th class="px-4 py-2">Kid</th>
                                    <th class="px-4 py-2">Task</th>
                                    <th class="px-4 py-2">Action</th>
                                    <th class="px-4 py-2">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($day['logs'] as $log)
                                    <tr class="border-b border-slate-100 last:border-b-0">
                                        <td class="px-4 py-2">{{ $log['kid'] }}</td>
                                        <td class="px-4 py-2">{{ $log['task'] }}</td>
                                        <td class="px-4 py-2">{{ $log['action'] }}</td>
                                        <td class="px-4 py-2">{{ $log['completed_at'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-slate-500">
                    No activity matches your search.
                </div>
            @endforelse
        </div>
    </div>
</div>
