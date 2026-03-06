<?php

namespace App\Livewire;

use App\Models\Kid;
use Livewire\Attributes\On;
use Livewire\Component;

class KidSelector extends Component
{
    public ?int $selectedKidId = null;

    public bool $authenticated = false;

    public function mount(): void
    {
        $this->selectedKidId = session('kid_id');
        $this->authenticated = (bool) $this->selectedKidId;
    }

    public function selectKid(int $kidId): void
    {
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
        $this->selectedKidId = $kidId;
        $this->authenticated = true;
    }

    public function render()
    {
        return view('livewire.kid-selector', [
            'kids' => Kid::query()->orderBy('name')->get(),
        ]);
    }
}
