<?php

namespace App\Livewire;

use Livewire\Component;

class CelebrationModal extends Component
{
    public bool $show = false;

    public function render()
    {
        return view('livewire.celebration-modal');
    }
}
