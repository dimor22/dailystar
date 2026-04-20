<x-layouts.app :title="'Admin Dashboard'">
    <x-app-nav>
        <a href="{{ route('admin.dashboard') }}" class="kid-btn kid-btn-primary">Admin</a>
        <a href="{{ route('parent.dashboard') }}" class="kid-btn kid-btn-primary">Parent Dashboard</a>
        <form action="{{ route('parent.logout') }}" method="POST">
            @csrf
            <button type="submit" class="kid-btn kid-btn-warn">Logout</button>
        </form>
    </x-app-nav>

    <div class="space-y-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">Admin Dashboard</h1>
            <p class="mt-1 text-sm font-semibold text-slate-500">Overview of all DailyStars users and activity.</p>
        </div>

        <livewire:admin-dashboard />
    </div>
</x-layouts.app>
