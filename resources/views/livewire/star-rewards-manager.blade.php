<div class="space-y-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="kid-card h-fit lg:sticky lg:top-6">
        <h1 class="kid-title">Star Rewards</h1>
        <p class="mt-1 text-slate-600">Create milestone badges and reorder by drag-and-drop.</p>

        <form wire:submit="{{ $editingId ? 'updateReward' : 'createReward' }}" class="mt-6 grid gap-4 md:grid-cols-2" autocomplete="off">
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                <input wire:model.live="formTitle" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Mission Maker">
                @error('formTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Stars Needed</label>
                <input wire:model.live="formStarsNeeded" type="number" min="1" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="3">
                @error('formStarsNeeded') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Order Number</label>
                <input wire:model.live="formOrderNumber" type="number" min="1" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="1">
                @error('formOrderNumber') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input wire:model.live="formActive" type="checkbox" class="h-4 w-4 rounded border-slate-300">
                    Active badge
                </label>
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
                        <img src="{{ $formImage ? $formImage->temporaryUrl() : \Illuminate\Support\Facades\Storage::url($currentImagePath) }}" alt="Star reward image preview" class="h-14 w-14 rounded-xl object-cover bg-white p-1">
                        <button type="button" wire:click="removeImage" class="rounded-lg border border-red-300 px-2 py-1 text-xs font-bold text-red-600">Remove image</button>
                    </div>
                @endif
            </div>

            <div class="md:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="kid-btn kid-btn-primary">{{ $editingId ? 'Update Badge' : 'Add Badge' }}</button>

                @if($editingId)
                    <button type="button" wire:click="cancelEdit" class="kid-btn kid-btn-warn">Cancel</button>
                @endif
            </div>
        </form>
    </div>

    <div class="kid-card !m-0">
        <h2 class="text-kid-xl font-bold text-slate-800">Badge Rewards</h2>
        <p class="mt-1 text-xs text-slate-500">Drag rows using the handle to reorder badges.</p>

        <div
            class="mt-4 grid gap-3"
            x-data="{
                draggingId: null,
                dropOn(targetId) {
                    if (!this.draggingId || this.draggingId === targetId) return;

                    const list = this.$refs.rewardsList;
                    const ids = Array.from(list.querySelectorAll('[data-star-reward-id]')).map((el) => Number(el.dataset.starRewardId));
                    const from = ids.indexOf(this.draggingId);
                    const to = ids.indexOf(targetId);

                    if (from === -1 || to === -1) return;

                    ids.splice(to, 0, ids.splice(from, 1)[0]);
                    this.draggingId = null;
                    $wire.reorderRewards(ids);
                }
            }"
            x-ref="rewardsList"
        >
            @forelse($rewards as $reward)
                <div
                    wire:key="reward-{{ $reward->id }}"
                    data-star-reward-id="{{ $reward->id }}"
                    draggable="true"
                    x-on:dragstart="draggingId = {{ $reward->id }}"
                    x-on:dragover.prevent
                    x-on:drop.prevent="dropOn({{ $reward->id }})"
                    class="rounded-2xl border border-slate-200 p-4"
                >
                    <div class="flex items-start gap-3">
                        <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600" title="Drag to reorder">Drag</span>

                        @if($reward->image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($reward->image_path) }}" alt="{{ $reward->title }} image" class="h-14 w-14 rounded-xl object-cover bg-slate-100 p-1" />
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-lg font-bold text-slate-800">{{ $reward->title }}</h3>
                                <span class="rounded-full bg-amber-100 px-2 py-1 text-sm font-bold text-amber-700">{{ $reward->stars_needed }} stars</span>
                            </div>

                            @if($reward->description)
                                <p class="mt-1 text-sm text-slate-600">{{ $reward->description }}</p>
                            @endif

                            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Order {{ $reward->order_number }}</p>
                            <p class="mt-1 text-sm font-semibold {{ $reward->active ? 'text-green-600' : 'text-slate-500' }}">{{ $reward->active ? 'Active' : 'Inactive' }}</p>

                            <div class="mt-3 flex gap-2">
                                <button type="button" wire:click.prevent="editReward({{ $reward->id }})" class="rounded-xl bg-blue-500 px-3 py-2 text-sm font-bold text-white">Edit</button>
                                <button type="button" wire:click.prevent="deleteReward({{ $reward->id }})" wire:confirm="Delete {{ $reward->title }}?" class="rounded-xl bg-red-500 px-3 py-2 text-sm font-bold text-white">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-slate-600">No badges yet. Add one above.</p>
            @endforelse
        </div>
    </div>
</div>
