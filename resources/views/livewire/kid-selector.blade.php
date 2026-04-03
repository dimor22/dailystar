<div class="space-y-6">
    @php
        $selectedKid = $kids->firstWhere('id', $selectedKidId);
    @endphp

    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center justify-center">
            <x-site-logo class="w-64" />
        </div>
    </div>
    @if($parentMissing)
        <div class="kid-card text-center">
            <h1 class="kid-title">Parent Sign-in Required</h1>
            <p class="mt-2 text-slate-700">Please sign in as a parent first to load your kids.</p>
            <a href="{{ route('parent.login') }}" class="kid-btn kid-btn-primary mt-4 inline-block">Parent Login</a>
        </div>
    @elseif($authenticated && $selectedKidId)
        <div class="flex flex-col 2xs:flex-row justify-between items-center gap-2">
            <div>
                <h1 class="kid-title">Today's Missions</h1>
                <p class="mt-2 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-gradient-to-r from-sky-100 via-emerald-100 to-amber-100 px-4 py-1 text-sm font-bold text-slate-700 shadow-sm">
                    <span aria-hidden="true">📅</span>
                    <span>{{ $missionsDateTime }}</span>
                </p>
            </div>

        </div>

        <livewire:kid-dashboard :kidId="$selectedKidId" :key="'kid-dashboard-'.$selectedKidId" />
    @elseif($selectedKidId)
        <div class="grid gap-6 lg:grid-cols-2 max-w-[60%] mx-auto">
            <div class="flex flex-col gap-4 justify-center items-center mb-6">
                @if($selectedKid->avatar_display_mode === 'image' && $selectedKid->avatar_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($selectedKid->avatar_image_path) }}" alt="{{ $selectedKid->name }} avatar" class="bg-white rounded-xl p-2 h-30 w-30 object-contain" />
                @else
                    <span class="text-6xl bg-white rounded-full p-4 flex justify-center items-center h-30 w-30">{{ $selectedKid->avatar }}</span>
                @endif

                <h1 class="kid-title text-center text-6xl">{{ $selectedKid->name }}</h1>
            </div>

            <livewire:pin-login :kidId="$selectedKidId" :key="'pin-login-'.$selectedKidId" />
        </div>

    @elseif($kids->isNotEmpty())
        <h1 class="kid-title text-center">Choose Your Avatar</h1>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($kids as $kid)
                <button
                    type="button"
                    wire:click="selectKid({{ $kid->id }})"
                    class="kid-card {{ $kid->color }} text-center text-black"
                >
                    @if($kid->avatar_display_mode === 'image' && $kid->avatar_image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($kid->avatar_image_path) }}" alt="{{ $kid->name }} avatar" class="mx-auto h-20 w-20 rounded-full object-cover bg-white p-1" />
                    @else
                        <div class="text-6xl">{{ $kid->avatar }}</div>
                    @endif
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
