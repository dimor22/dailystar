<div class="space-y-6" wire:poll.30s="loadDashboard" x-data="{ settingsOpen: false }">
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
            <div class="kid-card">
                <div class="flex items-center justify-between">
                    @if($kid['avatar_display_mode'] === 'image' && $kid['avatar_image_path'])
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($kid['avatar_image_path']) }}" alt="{{ $kid['name'] }} avatar" class="h-12 w-12 rounded-full object-cover bg-white p-1" />
                    @else
                        <p class="text-4xl">{{ $kid['avatar'] }}</p>
                    @endif
                    <span class="rounded-full px-3 py-1 text-sm font-bold text-white {{ $kid['color'] }}">{{ $kid['name'] }}</span>
                </div>
                <p class="mt-3 text-lg font-bold text-slate-700">Points: {{ $kid['points'] }}</p>
                <p class="text-lg font-bold text-slate-700">Stars: {{ $kid['stars'] }} ⭐</p>
                <p class="text-lg font-bold text-slate-700">Streak: {{ $kid['streak'] }} 🔥</p>

                <div class="mt-3">
                    <x-progress-bar :current="$kid['completed']" :total="$kid['total']" />
                </div>
                <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
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
        <h2 class="text-kid-xl font-bold text-slate-800">Activity Log</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-lg">
                <thead>
                    <tr class="border-b-2 border-slate-200">
                        <th class="py-2">Kid</th>
                        <th class="py-2">Task</th>
                        <th class="py-2">Action</th>
                        <th class="py-2">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activityLogs as $log)
                        <tr class="border-b border-slate-100">
                            <td class="py-2">{{ $log['kid'] }}</td>
                            <td class="py-2">{{ $log['task'] }}</td>
                            <td class="py-2">{{ $log['action'] }}</td>
                            <td class="py-2">{{ $log['completed_at'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-3 text-slate-500">No activity yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
