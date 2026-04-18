<x-layouts.app :title="'Billing & Plan'">
    <x-app-nav>
        <a href="{{ route('parent.dashboard') }}" class="kid-btn kid-btn-primary">Back to Dashboard</a>
        <form action="{{ route('parent.logout') }}" method="POST">
            @csrf
            <button type="submit" class="kid-btn kid-btn-warn">Logout</button>
        </form>
    </x-app-nav>

    <div class="mx-auto max-w-2xl space-y-6 py-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">Billing &amp; Plan</h1>
            <p class="mt-1 text-sm font-semibold text-slate-500">Manage your DailyStars subscription.</p>
        </div>

        @if (session('upgrade_prompt'))
            <div class="rounded-2xl border border-sky-200 bg-sky-50 px-5 py-3 text-sm font-semibold text-sky-800">
                {{ session('upgrade_prompt') }}
            </div>
        @endif

        <livewire:subscription-manager />
    </div>
</x-layouts.app>
