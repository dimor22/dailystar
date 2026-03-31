<?php

namespace App\Livewire;

use App\Models\StarReward;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class StarRewardsManager extends Component
{
    use WithFileUploads;

    public int $parentId = 0;

    public string $formTitle = '';

    public string $formDescription = '';

    public ?UploadedFile $formImage = null;

    public ?string $currentImagePath = null;

    public bool $removeCurrentImage = false;

    public bool $formActive = true;

    public int $formOrderNumber = 1;

    public int $formStarsNeeded = 1;

    public ?int $editingId = null;

    public function mount(): void
    {
        $this->parentId = (int) session('parent_user_id');
        $this->resetForm();
    }

    public function createReward(): void
    {
        if ($this->parentId <= 0) {
            return;
        }

        $validated = $this->validate($this->rules());
        $order = max(1, (int) $validated['formOrderNumber']);
        $imagePath = $this->storeImageIfUploaded($this->formImage);

        StarReward::query()
            ->where('parent_id', $this->parentId)
            ->where('order_number', '>=', $order)
            ->increment('order_number');

        StarReward::query()->create([
            'parent_id' => $this->parentId,
            'title' => $validated['formTitle'],
            'description' => $validated['formDescription'] ?: null,
            'image_path' => $imagePath,
            'active' => $validated['formActive'],
            'order_number' => $order,
            'stars_needed' => $validated['formStarsNeeded'],
        ]);

        $this->normalizeOrderNumbers();
        $this->resetForm();
        $this->dispatch('toast', message: 'Star reward added.', type: 'success');
    }

    public function editReward(int $id): void
    {
        $reward = $this->ownedRewards()->findOrFail($id);

        $this->editingId = $reward->id;
        $this->formTitle = (string) $reward->title;
        $this->formDescription = (string) ($reward->description ?? '');
        $this->formImage = null;
        $this->currentImagePath = $reward->image_path;
        $this->removeCurrentImage = false;
        $this->formActive = (bool) $reward->active;
        $this->formOrderNumber = (int) $reward->order_number;
        $this->formStarsNeeded = (int) $reward->stars_needed;
    }

    public function updateReward(): void
    {
        if (! $this->editingId) {
            return;
        }

        $reward = $this->ownedRewards()->findOrFail($this->editingId);
        $validated = $this->validate($this->rules());

        $newImagePath = $this->storeImageIfUploaded($this->formImage);
        $activeImagePath = $newImagePath
            ?: ($this->removeCurrentImage ? null : $reward->image_path);

        if ($newImagePath && $reward->image_path) {
            Storage::disk('public')->delete($reward->image_path);
        }

        if ($this->removeCurrentImage && $reward->image_path && ! $newImagePath) {
            Storage::disk('public')->delete($reward->image_path);
        }

        $oldOrder = (int) $reward->order_number;
        $newOrder = max(1, (int) $validated['formOrderNumber']);

        if ($newOrder !== $oldOrder) {
            if ($newOrder > $oldOrder) {
                StarReward::query()
                    ->where('parent_id', $this->parentId)
                    ->where('id', '!=', $reward->id)
                    ->whereBetween('order_number', [$oldOrder + 1, $newOrder])
                    ->decrement('order_number');
            } else {
                StarReward::query()
                    ->where('parent_id', $this->parentId)
                    ->where('id', '!=', $reward->id)
                    ->whereBetween('order_number', [$newOrder, $oldOrder - 1])
                    ->increment('order_number');
            }
        }

        $reward->update([
            'title' => $validated['formTitle'],
            'description' => $validated['formDescription'] ?: null,
            'image_path' => $activeImagePath,
            'active' => $validated['formActive'],
            'order_number' => $newOrder,
            'stars_needed' => $validated['formStarsNeeded'],
        ]);

        $this->normalizeOrderNumbers();
        $this->resetForm();
        $this->dispatch('toast', message: 'Star reward updated.', type: 'success');
    }

    public function deleteReward(int $id): void
    {
        $reward = $this->ownedRewards()->findOrFail($id);

        if ($reward->image_path) {
            Storage::disk('public')->delete($reward->image_path);
        }

        $reward->delete();
        $this->normalizeOrderNumbers();

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        $this->dispatch('toast', message: 'Star reward deleted.', type: 'success');
    }

    public function reorderRewards(array $orderedIds): void
    {
        $ownedIds = $this->ownedRewards()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $sanitized = collect($orderedIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => in_array($id, $ownedIds, true))
            ->unique()
            ->values()
            ->all();

        if (count($sanitized) !== count($ownedIds)) {
            return;
        }

        foreach ($sanitized as $index => $id) {
            StarReward::query()
                ->where('parent_id', $this->parentId)
                ->where('id', $id)
                ->update(['order_number' => $index + 1]);
        }

        if ($this->editingId) {
            $current = StarReward::query()->where('parent_id', $this->parentId)->find($this->editingId);
            if ($current) {
                $this->formOrderNumber = (int) $current->order_number;
            }
        }
    }

    public function removeImage(): void
    {
        $this->formImage = null;

        if ($this->currentImagePath) {
            $this->removeCurrentImage = true;
            $this->currentImagePath = null;
        }
    }

    public function updatedFormImage(): void
    {
        $this->validateOnly('formImage');

        if ($this->formImage) {
            $this->removeCurrentImage = false;
        }
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.star-rewards-manager', [
            'rewards' => $this->ownedRewards()->orderBy('order_number')->orderBy('id')->get(),
        ]);
    }

    private function ownedRewards()
    {
        return StarReward::query()->where('parent_id', $this->parentId);
    }

    protected function rules(): array
    {
        return [
            'formTitle' => ['required', 'string', 'max:120'],
            'formDescription' => ['nullable', 'string', 'max:1000'],
            'formImage' => ['nullable', 'mimetypes:image/jpeg,image/png,image/webp,image/avif', 'mimes:jpeg,jpg,png,webp,avif', 'max:1024'],
            'formActive' => ['required', 'boolean'],
            'formOrderNumber' => ['required', 'integer', 'min:1', 'max:999'],
            'formStarsNeeded' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }

    private function storeImageIfUploaded(?UploadedFile $uploadedFile): ?string
    {
        if (! $uploadedFile) {
            return null;
        }

        return $uploadedFile->store('star-rewards', 'public');
    }

    private function normalizeOrderNumbers(): void
    {
        $this->ownedRewards()
            ->orderBy('order_number')
            ->orderBy('id')
            ->get()
            ->values()
            ->each(function (StarReward $reward, int $index) {
                $next = $index + 1;

                if ((int) $reward->order_number !== $next) {
                    $reward->update(['order_number' => $next]);
                }
            });
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->formTitle = '';
        $this->formDescription = '';
        $this->formImage = null;
        $this->currentImagePath = null;
        $this->removeCurrentImage = false;
        $this->formActive = true;
        $this->formOrderNumber = max(1, ((int) $this->ownedRewards()->max('order_number')) + 1);
        $this->formStarsNeeded = 1;
        $this->resetErrorBag();
    }
}
