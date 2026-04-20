<div class="space-y-8">

    {{-- ── Stats grid ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">

        @foreach ([
            ['👨‍👩‍👧', 'Total Parents',        $stats['totalUsers'],       false],
            ['⭐',       'Early Adopters',     $stats['earlyAdopters'],    false],
            ['💎',       'Pro Subscribers',    $stats['proUsers'],         false],
            ['🧒',       'Total Kids',         $stats['totalKids'],        false],
            ['📋',       'Total Tasks',        $stats['totalTasks'],       false],
            ['🔥',       'Active (7 days)',    $stats['activeUsers'],      false],
            ['✅',       'Completions Today',  $stats['completionsToday'], false],
            ['⏳',       'Pending Approval',   $stats['pendingCount'],     true],
        ] as [$icon, $label, $value, $highlight])
            <div class="kid-card text-center {{ $highlight && $value > 0 ? 'ring-2 ring-amber-300' : '' }}">
                <div class="text-3xl">{{ $icon }}</div>
                <div class="mt-1 text-2xl font-extrabold {{ $highlight && $value > 0 ? 'text-amber-600' : 'text-slate-900' }}" style="font-family:'Baloo 2',cursive">
                    {{ number_format($value) }}
                </div>
                <div class="mt-0.5 text-xs font-extrabold uppercase tracking-wider text-slate-500">
                    {{ $label }}
                </div>
            </div>
        @endforeach

    </div>

    {{-- ── Pending Approvals ───────────────────────────────────────────────── --}}
    @if ($pendingUsers->isNotEmpty())
        <div class="kid-card space-y-4 border-2 border-amber-200 bg-amber-50/50">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⏳</span>
                <h2 class="text-lg font-extrabold text-amber-800">
                    Pending Registrations
                    <span class="ml-2 rounded-full bg-amber-200 px-2.5 py-0.5 text-sm text-amber-900">
                        {{ $pendingUsers->count() }}
                    </span>
                </h2>
            </div>

            <p class="text-sm font-semibold text-amber-700">
                Review and approve new accounts. Assign a role before approving, or reject to permanently remove the registration.
            </p>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-amber-200 text-xs font-extrabold uppercase tracking-wider text-amber-700">
                            <th class="pb-2 pr-4">Name</th>
                            <th class="pb-2 pr-4">Email</th>
                            <th class="pb-2 pr-4">Registered</th>
                            <th class="pb-2 pr-4">Assign Role</th>
                            <th class="pb-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100">
                        @foreach ($pendingUsers as $pendingUser)
                            <tr class="hover:bg-amber-50">
                                <td class="py-3 pr-4 font-semibold text-slate-900">{{ $pendingUser->name }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $pendingUser->email }}</td>
                                <td class="py-3 pr-4 text-slate-500">{{ $pendingUser->created_at->format('M j, Y') }}</td>
                                <td class="py-3 pr-4">
                                    <select
                                        wire:model="pendingRoles.{{ $pendingUser->id }}"
                                        class="rounded-lg border border-amber-300 bg-white px-2 py-1.5 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-300">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->value }}">{{ $role->label() }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <button
                                            wire:click="approveUser({{ $pendingUser->id }})"
                                            wire:confirm="Approve {{ $pendingUser->name }}?"
                                            class="rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-extrabold text-white shadow-sm hover:bg-emerald-600 transition-colors">
                                            ✓ Approve
                                        </button>
                                        <button
                                            wire:click="rejectUser({{ $pendingUser->id }})"
                                            wire:confirm="Reject and permanently delete {{ $pendingUser->name }}&#039;s registration?"
                                            class="rounded-lg bg-red-100 px-3 py-1.5 text-xs font-extrabold text-red-700 shadow-sm hover:bg-red-200 transition-colors">
                                            ✕ Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ── Active user list ────────────────────────────────────────────────── --}}
    <div class="kid-card space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-extrabold text-slate-900">Active Users</h2>

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
