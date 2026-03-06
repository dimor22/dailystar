<div class="space-y-6">
    @php
        $selectedKid = $kids->firstWhere('id', $selectedKidId);
    @endphp

    <div class="text-center">
        <div class="mx-auto inline-flex h-20 w-20 items-center justify-center rounded-full bg-white text-5xl shadow">
            {{ $selectedKid?->avatar ?? '🌟' }}
        </div>
    </div>

    @if($parentMissing)
        <div class="kid-card text-center">
            <h1 class="kid-title">Parent Sign-in Required</h1>
            <p class="mt-2 text-slate-700">Please sign in as a parent first to load your kids.</p>
            <a href="{{ route('parent.login') }}" class="kid-btn kid-btn-primary mt-4 inline-block">Parent Login</a>
        </div>
    @elseif($authenticated && $selectedKidId)
        <div class="flex items-center justify-between">
            <h1 class="kid-title">Today's Missions</h1>
            <button class="kid-btn kid-btn-warn" wire:click="switchKid">Switch Kid</button>
        </div>

        <livewire:kid-dashboard :kidId="$selectedKidId" :key="'kid-dashboard-'.$selectedKidId" />
    @elseif($selectedKidId)
        <h1 class="kid-title text-center">Enter Your PIN</h1>
        <livewire:pin-login :kidId="$selectedKidId" :key="'pin-login-'.$selectedKidId" />
    @elseif($kids->isNotEmpty())
        <h1 class="kid-title text-center">Choose Your Avatar</h1>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($kids as $kid)
                <button
                    type="button"
                    wire:click="selectKid({{ $kid->id }})"
                    class="kid-card {{ $kid->color }} text-center text-black"
                >
                    <div class="text-6xl">{{ $kid->avatar }}</div>
                    <p class="mt-3 text-kid-xl font-bold">{{ $kid->name }}</p>
                </button>
            @endforeach
        </div>
    @else
        <div class="kid-card text-center">
            <h1 class="kid-title">No Kids Yet</h1>
            <p class="mt-2 text-slate-700">Create kid profiles from the parent area first.</p>
            <a href="{{ route('parent.kids') }}" class="kid-btn kid-btn-primary mt-4 inline-block">Manage Kids</a>
        </div>
    @endif
</div>
