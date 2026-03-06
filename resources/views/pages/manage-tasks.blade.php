<x-layouts.app :title="'Manage Tasks'">
    <div class="mb-4 flex flex-wrap justify-between gap-3">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('parent.dashboard') }}" class="kid-btn kid-btn-primary">Back to Dashboard</a>
            <a href="{{ route('parent.kids') }}" class="kid-btn kid-btn-primary">Manage Kids</a>
        </div>

        <form action="{{ route('parent.logout') }}" method="POST">
            @csrf
            <button type="submit" class="kid-btn kid-btn-warn">Parent Logout</button>
        </form>
    </div>

    <livewire:tasks-manager />
</x-layouts.app>
