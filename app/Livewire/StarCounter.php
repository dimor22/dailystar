<?php

namespace App\Livewire;

use Livewire\Component;

class StarCounter extends Component
{
    public int $stars = 0;

    public function render()
    {
        return view('livewire.star-counter');
    }
}
