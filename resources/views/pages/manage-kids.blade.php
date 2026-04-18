<x-layouts.app :title="'Manage Kids'">
    <x-app-nav>
        <a href="{{ route('parent.dashboard') }}" class="kid-btn kid-btn-primary">Back to Dashboard</a>
        <a href="{{ route('parent.tasks') }}" class="kid-btn kid-btn-primary">Manage Tasks</a>
        <a href="{{ route('parent.settings.points-store') }}" class="kid-btn kid-btn-primary">Settings</a>
        <form action="{{ route('parent.logout') }}" method="POST">
            @csrf
            <button type="submit" class="kid-btn kid-btn-warn">Logout</button>
        </form>
    </x-app-nav>

    <livewire:upgrade-banner />

    <livewire:kids-manager />
</x-layouts.app>
