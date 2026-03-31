<x-layouts.app :title="'Settings • Points Store'">
    <x-app-nav>
        <a href="{{ route('parent.dashboard') }}" class="kid-btn kid-btn-primary">Dashboard</a>
        <a href="{{ route('parent.kids') }}" class="kid-btn kid-btn-primary">Manage Kids</a>
        <a href="{{ route('parent.tasks') }}" class="kid-btn kid-btn-primary">Manage Tasks</a>
        <a href="{{ route('parent.settings.points-store') }}" class="kid-btn kid-btn-primary ring-4 ring-blue-200">Settings</a>
        <form action="{{ route('parent.logout') }}" method="POST">
            @csrf
            <button type="submit" class="kid-btn kid-btn-warn">Logout</button>
        </form>
    </x-app-nav>

    <x-settings-tabs />

    <livewire:points-store-manager />
</x-layouts.app>
