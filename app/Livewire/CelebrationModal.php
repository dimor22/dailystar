<?php

namespace App\Livewire;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class CelebrationModal extends Component
{
    #[Reactive]
    public bool $show = false;

    #[Reactive]
    public int $kidId;

    #[Reactive]
    public string $currentDate = '';

    public function dismiss(): void
    {
        $this->dispatch('celebration-dismissed');
    }

    public function render()
    {
        return view('livewire.celebration-modal');
    }
}
