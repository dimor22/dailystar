<div class="space-y-6" wire:poll.30s="loadDashboard">
    <div class="flex items-center justify-between">
        <h1 class="kid-title">Parent Dashboard</h1>
        <a href="/login" class="kid-btn kid-btn-primary">Kid Login</a>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        @forelse($kids as $kid)
            <div class="kid-card">
                <div class="flex items-center justify-between">
                    <p class="text-4xl">{{ $kid['avatar'] }}</p>
                    <span class="rounded-full px-3 py-1 text-sm font-bold text-white {{ $kid['color'] }}">{{ $kid['name'] }}</span>
                </div>
                <p class="mt-3 text-lg font-bold text-slate-700">Points: {{ $kid['points'] }}</p>
                <p class="text-lg font-bold text-slate-700">Stars: {{ $kid['stars'] }} ⭐</p>
                <p class="text-lg font-bold text-slate-700">Streak: {{ $kid['streak'] }} 🔥</p>
                <div class="mt-3">
                    <x-progress-bar :current="$kid['completed']" :total="$kid['total']" />
                </div>
            </div>
        @empty
            <div class="kid-card md:col-span-2 lg:col-span-4">
                <p class="text-kid-xl font-bold text-slate-700">No parent/kid data found yet. Run migrations and seeders.</p>
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
                            <td class="py-2">{{ $log['created_at'] }}</td>
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
