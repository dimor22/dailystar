<x-layouts.app :title="'Manage Kids'">
    <div class="flex flex-col lg:flex-row justify-between gap-3 mb-10">
        <div class="flex items-center justify-center shrink-0">
            <x-site-logo class="w-64" />
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('parent.dashboard') }}" class="kid-btn kid-btn-primary">Back to Dashboard</a>
            <a href="{{ route('parent.tasks') }}" class="kid-btn kid-btn-primary">Manage Tasks</a>
            <form action="{{ route('parent.logout') }}" method="POST">
                @csrf
                <button type="submit" class="kid-btn kid-btn-warn">Logout</button>
            </form>
        </div>


    </div>

    <livewire:kids-manager />
</x-layouts.app>
