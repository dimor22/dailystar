<div class="space-y-6 grid grid-cols-1 xl:grid-cols-2 gap-4">
    <div class="kid-card h-fit lg:sticky lg:top-6">
        <h1 class="kid-title">Manage Tasks</h1>
        <p class="mt-1 text-slate-600">Create and update tasks for your kids.</p>
        <p class="mt-1 text-xs text-slate-500">New tasks are assigned to each kid for Mon-Fri by default. You can adjust days in Manage Kids.</p>

        <form wire:submit="{{ $editingTaskId ? 'updateTask' : 'createTask' }}" class="mt-6 grid gap-4 lg:grid-cols-2" autocomplete="off">
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                <input wire:model.live="formTitle" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Reading">
                @error('formTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Points</label>
                <div class="rounded-xl border border-slate-300 bg-white px-3 py-3">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Choose task size</span>
                        <span class="rounded-full bg-amber-100 px-2 py-1 text-sm font-bold text-amber-700">{{ (int) $formPoints }} pts</span>
                    </div>

                    <div class="grid gap-2">
                        <label class="flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                            <span class="min-w-[100px]">Small</span>
                            <span class="text-slate-500">5 points</span>
                            <input wire:model.live="formPoints" type="radio" value="5" class="h-4 w-4 border-slate-300 text-blue-500">
                        </label>

                        <label class="flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                            <span class="min-w-[100px]">Medium</span>
                            <span class="text-slate-500">10 points</span>
                            <input wire:model.live="formPoints" type="radio" value="10" class="h-4 w-4 border-slate-300 text-blue-500">
                        </label>

                        <label class="flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                            <span class="min-w-[100px]">Big</span>
                            <span class="text-slate-500">20 points</span>
                            <input wire:model.live="formPoints" type="radio" value="20" class="h-4 w-4 border-slate-300 text-blue-500">
                        </label>
                    </div>
                </div>
                @error('formPoints') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Category</label>
                <select wire:model.live="formCategory" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                    @foreach($categoryOptions as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
                @error('formCategory') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input wire:model.live="formActive" type="checkbox" class="h-4 w-4 rounded border-slate-300">
                    Active task
                </label>
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                <textarea wire:model.live="formDescription" rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Task details"></textarea>
                @error('formDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Task Image (Optional)</label>
                <input wire:model.live="formTaskImage" type="file" accept=".jpeg,.jpg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                <p class="mt-1 text-xs text-slate-500">Accepted: jpeg, jpg, png, webp · Max 1MB</p>
                @error('formTaskImage') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                @if($formTaskImage || $currentTaskImagePath)
                    <div class="mt-3 flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <img
                            src="{{ $formTaskImage ? $formTaskImage->temporaryUrl() : \Illuminate\Support\Facades\Storage::url($currentTaskImagePath) }}"
                            alt="Task image preview"
                            class="h-14 w-14 rounded-xl object-cover bg-white p-1"
                        >
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-500">Task image preview</p>
                            <button
                                type="button"
                                wire:click="removeTaskImage"
                                class="mt-1 rounded-lg border border-red-300 px-2 py-1 text-xs font-bold text-red-600"
                            >
                                Remove image
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="md:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="kid-btn kid-btn-primary">{{ $editingTaskId ? 'Update Task' : 'Add Task' }}</button>

                @if($editingTaskId)
                    <button type="button" wire:click="cancelEdit" class="kid-btn kid-btn-warn">Cancel</button>
                @endif
            </div>
        </form>
    </div>

    <div class="kid-card !m-0">
        <h2 class="text-kid-xl font-bold text-slate-800">Current Tasks</h2>

        <div class="mt-4 grid gap-4 grid-cols-1 2xl:grid-cols-2">
            @forelse($tasks as $task)
                <div class="rounded-2xl border border-slate-200 p-4">
                    @if($task->image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($task->image_path) }}" alt="{{ $task->title }} image" class="mb-3 h-24 w-24 rounded-xl object-cover bg-slate-100 p-1" />
                    @endif
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-lg font-bold text-slate-800">{{ $task->title }}</h3>
                        <span class="rounded-full bg-amber-100 px-2 py-1 text-sm font-bold text-amber-700">{{ $task->points }} pts</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-600">{{ $task->category }}</p>
                    @if($task->description)
                        <p class="mt-2 text-sm text-slate-600">{{ $task->description }}</p>
                    @endif
                    <p class="mt-2 text-sm font-semibold {{ $task->active ? 'text-green-600' : 'text-slate-500' }}">
                        {{ $task->active ? 'Active' : 'Inactive' }}
                    </p>

                    <div class="mt-3 flex gap-2">
                        <button type="button" wire:click.prevent="editTask({{ $task->id }})" class="rounded-xl bg-blue-500 px-3 py-2 text-sm font-bold text-white">Edit</button>
                        <button type="button" wire:click.prevent="deleteTask({{ $task->id }})" wire:confirm="Delete {{ $task->title }}?" class="rounded-xl bg-red-500 px-3 py-2 text-sm font-bold text-white">Delete</button>
                    </div>
                </div>
            @empty
                <p class="text-slate-600">No tasks yet. Add one above.</p>
            @endforelse
        </div>
    </div>
</div>
