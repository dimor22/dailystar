<x-layouts.app :title="'Parent Dashboard'">
    <div class="mb-4 flex flex-wrap justify-end gap-3">
        <a href="{{ route('parent.kids') }}" class="kid-btn kid-btn-primary">Manage Kids</a>
        <a href="{{ route('parent.tasks') }}" class="kid-btn kid-btn-primary">Manage Tasks</a>

        <form action="{{ route('parent.logout') }}" method="POST">
            @csrf
            <button type="submit" class="kid-btn kid-btn-warn">Parent Logout</button>
        </form>
    </div>

    <livewire:parent-dashboard />
</x-layouts.app>
