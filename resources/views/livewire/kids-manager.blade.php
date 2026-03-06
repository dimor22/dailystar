<div class="space-y-6">
    <div class="kid-card">
        <h1 class="kid-title">Manage Kids</h1>
        <p class="mt-1 text-slate-600">Add, edit, or remove kid profiles.</p>

        <form wire:submit="{{ $editingKidId ? 'updateKid' : 'createKid' }}" autocomplete="off" class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Name</label>
                <input wire:model.live="formName" type="text" name="kid_name" autocomplete="off" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Kid name">
                @error('formName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">PIN (4 digits)</label>
                <input wire:model.live="formPin" type="text" name="kid_pin" autocomplete="off" maxlength="4" inputmode="numeric" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="1234">
                @error('formPin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Avatar</label>
                <select wire:model.live="formAvatar" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                    @foreach($avatarOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                @error('formAvatar') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Color</label>
                <select wire:model.live="formColor" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                    @foreach($colorOptions as $option)
                        <option value="{{ $option['class'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
                @error('formColor') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="kid-btn kid-btn-primary">
                    {{ $editingKidId ? 'Update Kid' : 'Add Kid' }}
                </button>

                @if($editingKidId)
                    <button type="button" wire:click="cancelEdit" class="kid-btn kid-btn-warn">Cancel</button>
                @endif
            </div>
        </form>
    </div>

    <div class="kid-card">
        <h2 class="text-kid-xl font-bold text-slate-800">Current Kids</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($kids as $kid)
                <div class="rounded-2xl border border-slate-200 p-4">
                    <div class="rounded-2xl p-4 text-center text-white {{ $kid->color }}">
                        <div class="text-4xl">{{ $kid->avatar }}</div>
                        <p class="mt-2 text-lg font-bold">{{ $kid->name }}</p>
                    </div>
                    <p class="mt-3 text-sm text-slate-600">Points: {{ $kid->points }}</p>

                    <div class="mt-3 flex gap-2">
                        <button type="button" wire:click.prevent="editKid({{ $kid->id }})" class="rounded-xl bg-blue-500 px-3 py-2 text-sm font-bold text-white">Edit</button>
                        <button type="button" wire:click.prevent="deleteKid({{ $kid->id }})" wire:confirm="Delete {{ $kid->name }}?" class="rounded-xl bg-red-500 px-3 py-2 text-sm font-bold text-white">Delete</button>
                    </div>
                </div>
            @empty
                <p class="text-slate-600">No kids yet. Add one above.</p>
            @endforelse
        </div>
    </div>
</div>
