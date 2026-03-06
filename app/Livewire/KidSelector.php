<?php

namespace App\Livewire;

use App\Models\Kid;
use Livewire\Attributes\On;
use Livewire\Component;

class KidSelector extends Component
{
    public ?int $selectedKidId = null;

    public bool $authenticated = false;

    public int $parentId = 0;

    public function mount(): void
    {
        $this->parentId = (int) session('parent_user_id');

        if ($this->parentId <= 0) {
            $this->switchKid();

            return;
        }

        $sessionKidId = session('kid_id');

        if ($sessionKidId && $this->ownedKids()->whereKey($sessionKidId)->exists()) {
            $this->selectedKidId = (int) $sessionKidId;
            $this->authenticated = true;

            return;
        }

        $this->switchKid();
    }

    public function selectKid(int $kidId): void
    {
        if (! $this->ownedKids()->whereKey($kidId)->exists()) {
            $this->switchKid();

            return;
        }

        $this->selectedKidId = $kidId;
        $this->authenticated = false;
    }

    public function switchKid(): void
    {
        session()->forget('kid_id');
        $this->authenticated = false;
        $this->selectedKidId = null;
    }

    #[On('kid-authenticated')]
    public function kidAuthenticated(int $kidId): void
    {
        if (! $this->ownedKids()->whereKey($kidId)->exists()) {
            $this->switchKid();

            return;
        }

        $this->selectedKidId = $kidId;
        $this->authenticated = true;
    }

    private function ownedKids()
    {
        return Kid::query()->where('parent_id', $this->parentId);
    }

    public function render()
    {
        return view('livewire.kid-selector', [
            'parentMissing' => $this->parentId <= 0,
            'kids' => $this->ownedKids()->orderBy('name')->get(),
        ]);
    }
}
