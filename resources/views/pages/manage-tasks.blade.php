<x-layouts.app :title="'Manage Tasks'">

    <div class="flex justify-between gap-3 mb-10">
        <div class="flex items-center justify-center shrink-0">
            <x-site-logo class="w-64" />
        </div>
        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('parent.dashboard') }}" class="kid-btn kid-btn-primary">Back to Dashboard</a>
            <a href="{{ route('parent.kids') }}" class="kid-btn kid-btn-primary">Manage Kids</a>
            <form action="{{ route('parent.logout') }}" method="POST">
            @csrf
                <button type="submit" class="kid-btn kid-btn-warn">Parent Logout</button>
            </form>
        </div>
    </div>

    <livewire:tasks-manager />
</x-layouts.app>
