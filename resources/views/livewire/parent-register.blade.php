<div
    class="mx-auto max-w-xl kid-card"
    x-data
    x-init="
        const detected = Intl.DateTimeFormat().resolvedOptions().timeZone;
        const timezoneSelect = $refs.timezoneSelect;

        if (!detected || !timezoneSelect) return;

        const hasOption = Array.from(timezoneSelect.options).some((option) => option.value === detected);

        if (!hasOption) return;

        $wire.set('timezone', detected);
    "
>
    <div class="mb-4 flex justify-center">
        <a href="{{ route('marketing.home') }}" aria-label="Go to DailyStars home">
            <x-site-logo class="h-14 w-52" />
        </a>
    </div>

    <h1 class="kid-title text-center">Create Parent Account</h1>
    <p class="mt-2 text-center text-slate-600">Create your account to manage your kids and tasks.</p>

    <form wire:submit="register" class="mt-6 space-y-4" autocomplete="off">
        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">Name</label>
            <input wire:model.live="name" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Parent name">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
            <input wire:model.live="email" type="email" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="you@example.com">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">Timezone</label>
            <select x-ref="timezoneSelect" wire:model.live="timezone" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                @foreach($timezones as $tz)
                    <option value="{{ $tz }}">{{ $tz }}</option>
                @endforeach
            </select>
            @error('timezone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">Password</label>
            <input wire:model.live="password" type="password" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="At least 8 characters">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">Confirm Password</label>
            <input wire:model.live="passwordConfirmation" type="password" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Repeat password">
            @error('passwordConfirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="kid-btn kid-btn-primary w-full">Create Account</button>
    </form>

    <p class="mt-4 text-center text-slate-600">
        Already have an account?
        <a href="{{ route('parent.login') }}" class="font-bold text-blue-600">Sign in</a>
    </p>
</div>
