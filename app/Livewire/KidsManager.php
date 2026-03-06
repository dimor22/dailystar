<?php

namespace App\Livewire;

use App\Models\Kid;
use Illuminate\Validation\Rule;
use Livewire\Component;

class KidsManager extends Component
{
    public int $parentId = 0;

    public string $formName = '';

    public string $formAvatar = '🦁';

    public string $formColor = 'bg-blue-500';

    public string $formPin = '';

    public ?int $editingKidId = null;

    public function mount(): void
    {
        $this->parentId = (int) session('parent_user_id');

        $this->resetForm();
    }

    public function createKid(): void
    {
        if ($this->parentId <= 0) {
            return;
        }

        $validated = $this->validate($this->rules());

        Kid::query()->create([
            'parent_id' => $this->parentId,
            'name' => $validated['formName'],
            'avatar' => $validated['formAvatar'],
            'color' => $validated['formColor'],
            'pin' => $validated['formPin'],
            'points' => 0,
        ]);

        $this->resetForm();
    }

    public function editKid(int $kidId): void
    {
        $kid = $this->ownedKids()->findOrFail($kidId);

        $this->editingKidId = $kid->id;
        $this->formName = $kid->name;
        $this->formAvatar = $kid->avatar;
        $this->formColor = $kid->color;
        $this->formPin = $kid->getRawOriginal('pin');
    }

    public function updateKid(): void
    {
        if (! $this->editingKidId) {
            return;
        }

        $kid = $this->ownedKids()->findOrFail($this->editingKidId);

        $validated = $this->validate($this->rules());

        $kid->update([
            'name' => $validated['formName'],
            'avatar' => $validated['formAvatar'],
            'color' => $validated['formColor'],
            'pin' => $validated['formPin'],
        ]);

        $this->resetForm();
    }

    public function deleteKid(int $kidId): void
    {
        $this->ownedKids()->whereKey($kidId)->delete();

        if ($this->editingKidId === $kidId) {
            $this->resetForm();
        }
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        $colorOptions = $this->colorOptions();

        return view('livewire.kids-manager', [
            'kids' => $this->ownedKids()->orderBy('name')->get(),
            'avatarOptions' => ['🦁', '🦄', '🚀', '🌈', '🐯', '🐼', '🦊', '🐙'],
            'colorOptions' => $colorOptions,
        ]);
    }

    private function ownedKids()
    {
        return Kid::query()->where('parent_id', $this->parentId);
    }

    private function rules(): array
    {
        $colorClasses = collect($this->colorOptions())
            ->pluck('class')
            ->all();

        return [
            'formName' => ['required', 'string', 'max:100'],
            'formAvatar' => ['required', 'string', 'max:10'],
            'formColor' => ['required', Rule::in($colorClasses)],
            'formPin' => ['required', 'digits:4'],
        ];
    }

    private function colorOptions(): array
    {
        return [
            ['label' => 'Ocean Blue', 'class' => 'bg-blue-500'],
            ['label' => 'Bubblegum Pink', 'class' => 'bg-pink-500'],
            ['label' => 'Lime Green', 'class' => 'bg-green-500'],
            ['label' => 'Sunny Yellow', 'class' => 'bg-yellow-500'],
            ['label' => 'Grape Purple', 'class' => 'bg-purple-500'],
            ['label' => 'Tangerine Orange', 'class' => 'bg-orange-500'],
        ];
    }

    private function resetForm(): void
    {
        $this->editingKidId = null;
        $this->formName = '';
        $this->formAvatar = '🦁';
        $this->formColor = 'bg-blue-500';
        $this->formPin = '';
        $this->resetErrorBag();
    }
}
