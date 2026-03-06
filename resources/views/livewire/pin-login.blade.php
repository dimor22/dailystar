<div class="mx-auto max-w-md kid-card text-center" x-data x-init="$nextTick(() => $refs.pinInput?.focus())">
    <form wire:submit="submit" class="space-y-4">
        <label class="block text-kid-xl font-bold text-slate-700">4-digit PIN</label>
        <input
            x-ref="pinInput"
            type="password"
            inputmode="numeric"
            maxlength="4"
            wire:model="pin"
            autofocus
            class="w-full rounded-2xl border-2 border-slate-300 px-4 py-3 text-center text-3xl tracking-[0.7rem]"
            placeholder="••••"
        />
        @if($errorMessage)
            <p class="font-semibold text-red-600">{{ $errorMessage }}</p>
        @endif
        <button type="submit" class="kid-btn kid-btn-primary w-full">Unlock</button>
    </form>
</div>
