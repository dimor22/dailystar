<div class="space-y-6">
    @php
        $selectedKid = $kids->firstWhere('id', $selectedKidId);
    @endphp

    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center justify-center">
            <x-site-logo class="w-64" />
        </div>

        {{-- Show a button for parents to go back to the parent area --}}
        <div class="text-center mb-4">
            <a href="{{ route('parent.dashboard') }}" class="kid-btn kid-btn-primary inline-block">Back to Parent Dashboard</a>
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
        <div class="flex gap-4 justify-center items-center mb-6">
            <span class="text-6xl bg-white rounded-full p-4 flex justify-center items-center h-30 w-30">{{ $selectedKid->avatar }}</span>
            <h1 class="kid-title text-center text-6xl">{{ $selectedKid->name }}</h1>
        </div>
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
