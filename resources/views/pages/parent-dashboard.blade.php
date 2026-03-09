<x-layouts.app :title="'Parent Dashboard'">
    <div class="flex justify-between gap-3 mb-10">
        <div class="flex items-center justify-center shrink-0">
            <x-site-logo class="w-64" />
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('parent.kids') }}" class="kid-btn kid-btn-primary">Manage Students</a>
            <a href="{{ route('parent.tasks') }}" class="kid-btn kid-btn-primary">Manage Tasks</a>
            <a href="{{ route('kid.login') }}" class="kid-btn kid-btn-primary">Students List</a>

            <form action="{{ route('parent.logout') }}" method="POST">
                @csrf
                <button type="submit" class="kid-btn kid-btn-warn">Logout</button>
            </form>
        </div>
    </div>

    <livewire:parent-dashboard />
</x-layouts.app>
