<?php

namespace App\Livewire;

use Livewire\Component;

class ProgressBar extends Component
{
    public int $current = 0;

    public int $total = 0;

    public function getPercentProperty(): int
    {
        if ($this->total <= 0) {
            return 0;
        }

        return (int) round(($this->current / $this->total) * 100);
    }

    public function render()
    {
        return view('livewire.progress-bar');
    }
}
