<x-layouts.app :title="'Parent Dashboard'">
    <x-app-nav>
        <a href="{{ route('parent.kids') }}" class="kid-btn kid-btn-primary">Manage Students</a>
        <a href="{{ route('parent.tasks') }}" class="kid-btn kid-btn-primary">Manage Tasks</a>
        <a href="{{ route('kid.login') }}" class="kid-btn kid-btn-primary">Students List</a>
        <form action="{{ route('parent.logout') }}" method="POST">
            @csrf
            <button type="submit" class="kid-btn kid-btn-warn">Logout</button>
        </form>
    </x-app-nav>

    <livewire:parent-dashboard />
</x-layouts.app>
