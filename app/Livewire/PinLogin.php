<?php

namespace App\Livewire;

use App\Models\Kid;
use Livewire\Component;

class PinLogin extends Component
{
    public int $kidId;

    public string $pin = '';

    public string $errorMessage = '';

    public function submit(): void
    {
        $kid = Kid::query()->find($this->kidId);

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
