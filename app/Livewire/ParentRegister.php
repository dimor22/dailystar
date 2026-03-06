<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ParentRegister extends Component
{
    public string $name = '';

    public string $email = '';

    public string $timezone = 'America/New_York';

    public string $password = '';

    public string $passwordConfirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'timezone' => ['required', 'timezone'],
            'password' => ['required', 'string', 'min:8'],
            'passwordConfirmation' => ['required', 'same:password'],
        ]);

        $parent = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'parent',
            'timezone' => $validated['timezone'],
        ]);

        session(['parent_user_id' => $parent->id]);

        $this->redirectRoute('parent.dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.parent-register', [
            'timezones' => [
                'America/New_York',
                'America/Chicago',
                'America/Denver',
                'America/Los_Angeles',
                'UTC',
            ],
        ]);
    }
}
