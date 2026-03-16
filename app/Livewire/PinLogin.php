<?php

namespace App\Livewire;

use App\Models\Kid;
use Livewire\Component;

class PinLogin extends Component
{
    public int $kidId;

    public int $parentId = 0;

    public int $sharedKidId = 0;

    public string $pin = '';

    public string $errorMessage = '';

    public function mount(): void
    {
        $this->parentId = (int) session('parent_user_id');
        $this->sharedKidId = (int) session('shared_kid_id');
    }

    public function submit(): void
    {
        $kidQuery = Kid::query();

        if ($this->parentId > 0) {
            $kidQuery->where('parent_id', $this->parentId);
        } elseif ($this->sharedKidId > 0) {
            $kidQuery->whereKey($this->sharedKidId);
        } else {
            $kidQuery->whereRaw('1 = 0');
        }

        $kid = $kidQuery->find($this->kidId);

        if (! $kid || $kid->getRawOriginal('pin') !== $this->pin) {
            $this->errorMessage = 'That PIN is not correct. Try again!';

            return;
        }

        session(['kid_id' => $kid->id]);
        session()->forget('preselected_kid_id');
        $this->reset('pin', 'errorMessage');

        $this->dispatch('kid-authenticated', kidId: $kid->id);
    }

    public function render()
    {
        return view('livewire.pin-login');
    }
}
