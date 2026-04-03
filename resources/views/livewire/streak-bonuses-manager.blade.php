<div class="space-y-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="kid-card h-fit lg:sticky lg:top-6">
        <h1 class="kid-title">Streak Bonuses</h1>
        <p class="mt-1 text-slate-600">Create bonuses triggered by streak day targets.</p>
        <p class="mt-1 text-xs text-slate-500">Type 0 = no bonus (celebration modal only), Type 1 = +10%, Type 2 = +20%, Type 3 = +30% task points.</p>

        <form wire:submit="{{ $editingId ? 'updateBonus' : 'createBonus' }}" class="mt-6 grid gap-4 md:grid-cols-2" autocomplete="off">
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                <input wire:model.live="formTitle" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Weekly Streak Chest">
                @error('formTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Day Target</label>
                <input wire:model.live="formDayTarget" type="number" min="1" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="7">
                @error('formDayTarget') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Bonus Type</label>
                <select wire:model.live="formBonusType" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                    @foreach($bonusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('formBonusType') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                <textarea wire:model.live="formDescription" rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Optional details"></textarea>
                @error('formDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Image (Optional)</label>
                <input wire:model.live="formImage" type="file" accept=".jpeg,.jpg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                <p class="mt-1 text-xs text-slate-500">Accepted: jpeg, jpg, png, webp · Max 1MB</p>
                @error('formImage') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                @if($formImage || $currentImagePath)
                    <div class="mt-3 flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <img src="{{ $formImage ? $formImage->temporaryUrl() : \Illuminate\Support\Facades\Storage::url($currentImagePath) }}" alt="Streak bonus image preview" class="h-14 w-14 rounded-xl object-cover bg-white p-1">
                        <button type="button" wire:click="removeImage" class="rounded-lg border border-red-300 px-2 py-1 text-xs font-bold text-red-600">Remove image</button>
                    </div>
                @endif
            </div>

            <div class="md:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="kid-btn kid-btn-primary">{{ $editingId ? 'Update Bonus' : 'Add Bonus' }}</button>

                @if($editingId)
                    <button type="button" wire:click="cancelEdit" class="kid-btn kid-btn-warn">Cancel</button>
                @endif
            </div>
        </form>
    </div>

    <div class="kid-card !m-0">
        <h2 class="text-kid-xl font-bold text-slate-800">Streak Bonus Rules</h2>

        <div class="mt-4 grid gap-4 grid-cols-1 2xl:grid-cols-2">
            @forelse($bonuses as $bonus)
                <div class="rounded-2xl border border-slate-200 p-4">
                    @if($bonus->image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($bonus->image_path) }}" alt="{{ $bonus->title }} image" class="mb-3 h-24 w-24 object-coverp-1" />
                    @endif
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-lg font-bold text-slate-800">{{ $bonus->title }}</h3>
                        <span class="rounded-full bg-amber-100 px-2 py-1 text-sm font-bold text-amber-700">Day {{ $bonus->day_target }}</span>
                    </div>

                    <p class="mt-1 text-sm font-semibold text-slate-600">{{ $bonusOptions[$bonus->bonus_type] ?? $bonus->bonus_type }}</p>

                    @if($bonus->description)
                        <p class="mt-2 text-sm text-slate-600">{{ $bonus->description }}</p>
                    @endif

                    <div class="mt-3 flex gap-2">
                        <button type="button" wire:click.prevent="editBonus({{ $bonus->id }})" class="rounded-xl bg-blue-500 px-3 py-2 text-sm font-bold text-white">Edit</button>
                        <button type="button" wire:click.prevent="deleteBonus({{ $bonus->id }})" wire:confirm="Delete {{ $bonus->title }}?" class="rounded-xl bg-red-500 px-3 py-2 text-sm font-bold text-white">Delete</button>
                    </div>
                </div>
            @empty
                <p class="text-slate-600">No bonuses yet. Add one above.</p>
            @endforelse
        </div>
    </div>
</div>
