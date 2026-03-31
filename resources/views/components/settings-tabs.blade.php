@props([])

<div class="mb-4 grid gap-2 sm:grid-cols-3">
    <a href="{{ route('parent.settings.points-store') }}" class="kid-btn kid-btn-primary {{ request()->routeIs('parent.settings.points-store') ? 'ring-4 ring-blue-200' : '' }}">Points Store</a>
    <a href="{{ route('parent.settings.star-rewards') }}" class="kid-btn kid-btn-primary {{ request()->routeIs('parent.settings.star-rewards') ? 'ring-4 ring-blue-200' : '' }}">Star Rewards</a>
    <a href="{{ route('parent.settings.streak-bonuses') }}" class="kid-btn kid-btn-primary {{ request()->routeIs('parent.settings.streak-bonuses') ? 'ring-4 ring-blue-200' : '' }}">Streak Bonuses</a>
</div>
