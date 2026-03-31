<?php

namespace App\Livewire;

use App\Models\PointsStoreItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class PointsStoreManager extends Component
{
    use WithFileUploads;

    public int $parentId = 0;

    public string $formTitle = '';

    public string $formDescription = '';

    public int $formPoints = 30;

    public ?UploadedFile $formImage = null;

    public ?string $currentImagePath = null;

    public bool $removeCurrentImage = false;

    public bool $formActive = true;

    public ?int $editingId = null;

    public function mount(): void
    {
        $this->parentId = (int) session('parent_user_id');
        $this->resetForm();
    }

    public function createItem(): void
    {
        if ($this->parentId <= 0) {
            return;
        }

        $validated = $this->validate($this->rules());
        $imagePath = $this->storeImageIfUploaded($this->formImage);

        PointsStoreItem::query()->create([
            'parent_id' => $this->parentId,
            'title' => $validated['formTitle'],
            'description' => $validated['formDescription'] ?: null,
            'points' => $validated['formPoints'],
            'image_path' => $imagePath,
            'active' => $validated['formActive'],
        ]);

        $this->resetForm();
        $this->dispatch('toast', message: 'Store item added.', type: 'success');
    }

    public function editItem(int $id): void
    {
        $item = $this->ownedItems()->findOrFail($id);

        $this->editingId = $item->id;
        $this->formTitle = (string) $item->title;
        $this->formDescription = (string) ($item->description ?? '');
        $this->formPoints = (int) $item->points;
        $this->currentImagePath = $item->image_path;
        $this->formImage = null;
        $this->removeCurrentImage = false;
        $this->formActive = (bool) $item->active;
    }

    public function updateItem(): void
    {
        if (! $this->editingId) {
            return;
        }

        $item = $this->ownedItems()->findOrFail($this->editingId);
        $validated = $this->validate($this->rules());

        $newImagePath = $this->storeImageIfUploaded($this->formImage);
        $activeImagePath = $newImagePath
            ?: ($this->removeCurrentImage ? null : $item->image_path);

        if ($newImagePath && $item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        if ($this->removeCurrentImage && $item->image_path && ! $newImagePath) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->update([
            'title' => $validated['formTitle'],
            'description' => $validated['formDescription'] ?: null,
            'points' => $validated['formPoints'],
            'image_path' => $activeImagePath,
            'active' => $validated['formActive'],
        ]);

        $this->resetForm();
        $this->dispatch('toast', message: 'Store item updated.', type: 'success');
    }

    public function deleteItem(int $id): void
    {
        $item = $this->ownedItems()->findOrFail($id);

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        $this->dispatch('toast', message: 'Store item deleted.', type: 'success');
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
        return view('livewire.points-store-manager', [
            'items' => $this->ownedItems()->orderBy('points')->orderBy('title')->get(),
        ]);
    }

    private function ownedItems()
    {
        return PointsStoreItem::query()->where('parent_id', $this->parentId);
    }

    protected function rules(): array
    {
        return [
            'formTitle' => ['required', 'string', 'max:120'],
            'formDescription' => ['nullable', 'string', 'max:1000'],
            'formPoints' => ['required', 'integer', 'min:1', 'max:10000'],
            'formImage' => ['nullable', 'mimetypes:image/jpeg,image/png,image/webp,image/avif', 'mimes:jpeg,jpg,png,webp,avif', 'max:1024'],
            'formActive' => ['required', 'boolean'],
        ];
    }

    private function storeImageIfUploaded(?UploadedFile $uploadedFile): ?string
    {
        if (! $uploadedFile) {
            return null;
        }

        return $uploadedFile->store('points-store-items', 'public');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->formTitle = '';
        $this->formDescription = '';
        $this->formPoints = 30;
        $this->formImage = null;
        $this->currentImagePath = null;
        $this->removeCurrentImage = false;
        $this->formActive = true;
        $this->resetErrorBag();
    }
}
