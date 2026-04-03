<?php

namespace App\Livewire;

use App\Models\StreakBonus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class StreakBonusesManager extends Component
{
    use WithFileUploads;

    private const BONUS_OPTIONS = [
        '0' => 'Type 0 - No Bonus (modal only)',
        '1' => 'Type 1 - 10% task points boost',
        '2' => 'Type 2 - 20% task points boost',
        '3' => 'Type 3 - 30% task points boost',
    ];

    public int $parentId = 0;

    public string $formTitle = '';

    public string $formDescription = '';

    public ?UploadedFile $formImage = null;

    public ?string $currentImagePath = null;

    public bool $removeCurrentImage = false;

    public int $formDayTarget = 3;

    public string $formBonusType = '0';

    public ?int $editingId = null;

    public function mount(): void
    {
        $this->parentId = (int) session('parent_user_id');
        $this->resetForm();
    }

    public function createBonus(): void
    {
        if ($this->parentId <= 0) {
            return;
        }

        $validated = $this->validate($this->rules());
        $imagePath = $this->storeImageIfUploaded($this->formImage);

        StreakBonus::query()->create([
            'parent_id' => $this->parentId,
            'title' => $validated['formTitle'],
            'description' => $validated['formDescription'] ?: null,
            'image_path' => $imagePath,
            'day_target' => $validated['formDayTarget'],
            'bonus_type' => (int) $validated['formBonusType'],
        ]);

        $this->resetForm();
        $this->dispatch('toast', message: 'Streak bonus added.', type: 'success');
    }

    public function editBonus(int $id): void
    {
        $bonus = $this->ownedBonuses()->findOrFail($id);

        $this->editingId = $bonus->id;
        $this->formTitle = (string) $bonus->title;
        $this->formDescription = (string) ($bonus->description ?? '');
        $this->formImage = null;
        $this->currentImagePath = $bonus->image_path;
        $this->removeCurrentImage = false;
        $this->formDayTarget = (int) $bonus->day_target;
        $this->formBonusType = (string) (int) $bonus->bonus_type;
    }

    public function updateBonus(): void
    {
        if (! $this->editingId) {
            return;
        }

        $bonus = $this->ownedBonuses()->findOrFail($this->editingId);
        $validated = $this->validate($this->rules());

        $newImagePath = $this->storeImageIfUploaded($this->formImage);
        $activeImagePath = $newImagePath
            ?: ($this->removeCurrentImage ? null : $bonus->image_path);

        if ($newImagePath && $bonus->image_path) {
            Storage::disk('public')->delete($bonus->image_path);
        }

        if ($this->removeCurrentImage && $bonus->image_path && ! $newImagePath) {
            Storage::disk('public')->delete($bonus->image_path);
        }

        $bonus->update([
            'title' => $validated['formTitle'],
            'description' => $validated['formDescription'] ?: null,
            'image_path' => $activeImagePath,
            'day_target' => $validated['formDayTarget'],
            'bonus_type' => (int) $validated['formBonusType'],
        ]);

        $this->resetForm();
        $this->dispatch('toast', message: 'Streak bonus updated.', type: 'success');
    }

    public function deleteBonus(int $id): void
    {
        $bonus = $this->ownedBonuses()->findOrFail($id);

        if ($bonus->image_path) {
            Storage::disk('public')->delete($bonus->image_path);
        }

        $bonus->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        $this->dispatch('toast', message: 'Streak bonus deleted.', type: 'success');
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
        return view('livewire.streak-bonuses-manager', [
            'bonuses' => $this->ownedBonuses()->orderBy('day_target')->orderBy('title')->get(),
            'bonusOptions' => self::BONUS_OPTIONS,
        ]);
    }

    private function ownedBonuses()
    {
        return StreakBonus::query()->where('parent_id', $this->parentId);
    }

    protected function rules(): array
    {
        return [
            'formTitle' => ['required', 'string', 'max:120'],
            'formDescription' => ['nullable', 'string', 'max:1000'],
            'formImage' => ['nullable', 'mimetypes:image/jpeg,image/png,image/webp,image/avif', 'mimes:jpeg,jpg,png,webp,avif', 'max:1024'],
            'formDayTarget' => ['required', 'integer', 'min:1', 'max:365'],
            'formBonusType' => ['required', 'in:0,1,2,3'],
        ];
    }

    private function storeImageIfUploaded(?UploadedFile $uploadedFile): ?string
    {
        if (! $uploadedFile) {
            return null;
        }

        return $uploadedFile->store('streak-bonuses', 'public');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->formTitle = '';
        $this->formDescription = '';
        $this->formImage = null;
        $this->currentImagePath = null;
        $this->removeCurrentImage = false;
        $this->formDayTarget = 3;
        $this->formBonusType = '0';
        $this->resetErrorBag();
    }
}
