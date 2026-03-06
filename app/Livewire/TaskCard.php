<?php

namespace App\Livewire;

use Livewire\Component;

class TaskCard extends Component
{
    public int $kidId;

    public int $taskId;

    public string $title;

    public ?string $description = null;

    public int $points = 0;

    public bool $completed = false;

    public function render()
    {
        return view('livewire.task-card');
    }
}
