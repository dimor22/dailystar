<x-layouts.app :title="'Parent Dashboard'">
    <x-app-nav>
        <a href="{{ route('parent.kids') }}" class="kid-btn kid-btn-primary">Manage Students</a>
        <a href="{{ route('parent.tasks') }}" class="kid-btn kid-btn-primary">Manage Tasks</a>
        <a href="{{ route('parent.settings.points-store') }}" class="kid-btn kid-btn-primary">Settings</a>
        @php $sessionUserId = session('parent_user_id'); $sessionUser = $sessionUserId ? \App\Models\User::find($sessionUserId) : null; @endphp
        @if($sessionUser?->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="kid-btn kid-btn-primary">Admin</a>
        @endif
        <form action="{{ route('parent.logout') }}" method="POST">
            @csrf
            <button type="submit" class="kid-btn kid-btn-warn">Logout</button>
        </form>
    </x-app-nav>

    <livewire:parent-dashboard />
</x-layouts.app>
