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

    public int $sharedKidId = 0;

    public function mount(): void
    {
        $this->parentId = (int) session('parent_user_id');
        $this->sharedKidId = (int) session('shared_kid_id');

        if ($this->parentId <= 0 && $this->sharedKidId <= 0) {
            $this->switchKid();

            return;
        }

        $sessionKidId = session('kid_id');
        $preselectedKidId = session('preselected_kid_id');

        if ($sessionKidId && $this->ownedKids()->whereKey($sessionKidId)->exists()) {
            $this->selectedKidId = (int) $sessionKidId;
            $this->authenticated = true;

            return;
        }

        if ($preselectedKidId && $this->ownedKids()->whereKey($preselectedKidId)->exists()) {
            $this->selectedKidId = (int) $preselectedKidId;
            $this->authenticated = false;

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
        session()->forget('preselected_kid_id');
    }

    public function switchKid(): void
    {
        session()->forget(['kid_id', 'preselected_kid_id']);
        $this->authenticated = false;

        if ($this->sharedKidId > 0) {
            $this->selectedKidId = $this->sharedKidId;
            session()->put('preselected_kid_id', $this->sharedKidId);

            return;
        }

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
        session()->forget('preselected_kid_id');
    }

    private function ownedKids()
    {
        if ($this->parentId > 0) {
            return Kid::query()->where('parent_id', $this->parentId);
        }

        if ($this->sharedKidId > 0) {
            return Kid::query()->whereKey($this->sharedKidId);
        }

        return Kid::query()->whereRaw('1 = 0');
    }

    public function render()
    {
        return view('livewire.kid-selector', [
            'parentMissing' => $this->parentId <= 0 && $this->sharedKidId <= 0,
            'kids' => $this->ownedKids()->orderBy('name')->get(),
        ]);
    }
}
