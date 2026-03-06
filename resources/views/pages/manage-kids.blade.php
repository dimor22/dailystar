<x-layouts.app :title="'Manage Kids'">
    <div class="mb-4 flex flex-wrap justify-between gap-3">
        <a href="{{ route('parent.dashboard') }}" class="kid-btn kid-btn-primary">Back to Dashboard</a>

        <form action="{{ route('parent.logout') }}" method="POST">
            @csrf
            <button type="submit" class="kid-btn kid-btn-warn">Parent Logout</button>
        </form>
    </div>

    <livewire:kids-manager />
</x-layouts.app>
