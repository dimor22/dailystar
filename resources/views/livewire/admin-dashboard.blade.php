<div class="space-y-8">

    {{-- ── Stats grid ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">

        @foreach ([
            ['👨‍👩‍👧', 'Total Parents',        $stats['totalUsers']],
            ['⭐',       'Early Adopters',     $stats['earlyAdopters']],
            ['💎',       'Pro Subscribers',    $stats['proUsers']],
            ['🧒',       'Total Kids',         $stats['totalKids']],
            ['📋',       'Total Tasks',        $stats['totalTasks']],
            ['🔥',       'Active (7 days)',    $stats['activeUsers']],
            ['✅',       'Completions Today',  $stats['completionsToday']],
        ] as [$icon, $label, $value])
            <div class="kid-card text-center">
                <div class="text-3xl">{{ $icon }}</div>
                <div class="mt-1 text-2xl font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">
                    {{ number_format($value) }}
                </div>
                <div class="mt-0.5 text-xs font-extrabold uppercase tracking-wider text-slate-500">
                    {{ $label }}
                </div>
            </div>
        @endforeach

    </div>

    {{-- ── User list ───────────────────────────────────────────────────────── --}}
    <div class="kid-card space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-extrabold text-slate-900">Users</h2>

            <div class="flex flex-wrap gap-2">
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search name or email…"
                    class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-200"
                >
                <select
                    wire:model.live="roleFilter"
                    class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-200">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs font-extrabold uppercase tracking-wider text-slate-500">
                        <th class="pb-2 pr-4">Name</th>
                        <th class="pb-2 pr-4">Email</th>
                        <th class="pb-2 pr-4">Role</th>
                        <th class="pb-2 pr-4">Kids</th>
                        <th class="pb-2 pr-4">Joined</th>
                        <th class="pb-2">Change Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($users as $user)
                        <tr class="group hover:bg-sky-50/50">
                            <td class="py-2.5 pr-4 font-semibold text-slate-900">{{ $user->name }}</td>
                            <td class="py-2.5 pr-4 text-slate-600">{{ $user->email }}</td>
                            <td class="py-2.5 pr-4">
                                @php
                                    $badge = match($user->role->value) {
                                        'admin'         => 'bg-red-100 text-red-700',
                                        'early_adopter' => 'bg-amber-100 text-amber-700',
                                        default         => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-extrabold {{ $badge }}">
                                    {{ $user->role->label() }}
                                </span>
                            </td>
                            <td class="py-2.5 pr-4 text-slate-700">{{ $user->kids_count }}</td>
                            <td class="py-2.5 pr-4 text-slate-500">{{ $user->created_at->format('M j, Y') }}</td>
                            <td class="py-2.5">
                                <select
                                    wire:change="setRole({{ $user->id }}, $event.target.value)"
                                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-200">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->value }}" @selected($user->role === $role)>
                                            {{ $role->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-sm font-semibold text-slate-400">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $users->links() }}
        </div>
    </div>

</div>
