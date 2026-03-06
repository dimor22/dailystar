<div class="space-y-6">
    @if($authenticated && $selectedKidId)
        <div class="flex items-center justify-between">
            <h1 class="kid-title">Today's Missions</h1>
            <button class="kid-btn kid-btn-warn" wire:click="switchKid">Switch Kid</button>
        </div>

        <livewire:kid-dashboard :kidId="$selectedKidId" :key="'kid-dashboard-'.$selectedKidId" />
    @elseif($selectedKidId)
        <h1 class="kid-title text-center">Enter Your PIN</h1>
        <livewire:pin-login :kidId="$selectedKidId" :key="'pin-login-'.$selectedKidId" />
    @else
        <h1 class="kid-title text-center">Choose Your Avatar</h1>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($kids as $kid)
                <button
                    type="button"
                    wire:click="selectKid({{ $kid->id }})"
                    class="kid-card {{ $kid->color }} text-center text-white"
                >
                    <div class="text-6xl">{{ $kid->avatar }}</div>
                    <p class="mt-3 text-kid-xl font-bold">{{ $kid->name }}</p>
                </button>
            @endforeach
        </div>
    @endif
</div>
