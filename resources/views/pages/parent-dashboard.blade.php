<x-layouts.app :title="'Parent Dashboard'">
    <div class="mb-4 flex justify-end">
        <form action="{{ route('parent.logout') }}" method="POST">
            @csrf
            <button type="submit" class="kid-btn kid-btn-warn">Parent Logout</button>
        </form>
    </div>

    <livewire:parent-dashboard />
</x-layouts.app>
