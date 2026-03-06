<?php

namespace App\Livewire;

use App\Models\Kid;
use Livewire\Component;

class PinLogin extends Component
{
    public int $kidId;

    public int $parentId = 0;

    public string $pin = '';

    public string $errorMessage = '';

    public function mount(): void
    {
        $this->parentId = (int) session('parent_user_id');
    }

    public function submit(): void
    {
        $kid = Kid::query()
            ->where('parent_id', $this->parentId)
            ->find($this->kidId);

        if (! $kid || $kid->getRawOriginal('pin') !== $this->pin) {
            $this->errorMessage = 'That PIN is not correct. Try again!';

            return;
        }

        session(['kid_id' => $kid->id]);
        $this->reset('pin', 'errorMessage');

        $this->dispatch('kid-authenticated', kidId: $kid->id);
    }

    public function render()
    {
        return view('livewire.pin-login');
    }
}
