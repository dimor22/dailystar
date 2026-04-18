<x-layouts.app :title="'Manage Tasks'">

    <x-app-nav>
        <a href="{{ route('parent.dashboard') }}" class="kid-btn kid-btn-primary">Back to Dashboard</a>
        <a href="{{ route('parent.kids') }}" class="kid-btn kid-btn-primary">Manage Kids</a>
        <a href="{{ route('parent.settings.points-store') }}" class="kid-btn kid-btn-primary">Settings</a>
        <form action="{{ route('parent.logout') }}" method="POST">
            @csrf
            <button type="submit" class="kid-btn kid-btn-warn">Parent Logout</button>
        </form>
    </x-app-nav>

    <livewire:upgrade-banner />

    <livewire:tasks-manager />
</x-layouts.app>
