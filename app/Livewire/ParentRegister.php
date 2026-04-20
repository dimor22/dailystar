<?php

namespace App\Livewire;

use App\Services\ParentRewardsProvisioningService;
use App\Models\User;
use DateTimeZone;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ParentRegister extends Component
{
    public string $name = '';

    public string $email = '';

    public string $timezone = 'UTC';

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
            'status' => 'pending',
            'timezone' => $validated['timezone'],
        ]);

        app(ParentRewardsProvisioningService::class)->provisionDefaults($parent);

        $adminEmail = config('services.marketing.contact_email');
        $appName    = config('app.name');

        try {
            Mail::raw(
                "New registration on {$appName}\n\n"
                . "Name:  {$parent->name}\n"
                . "Email: {$parent->email}\n"
                . "Time:  {$parent->created_at->toDateTimeString()} UTC\n\n"
                . "Go to the admin dashboard to approve or reject this user:\n"
                . url('/admin'),
                fn ($m) => $m
                    ->to($adminEmail)
                    ->subject("{$appName}: New registration — {$parent->name}")
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Registration notification email failed.', [
                'error' => $e->getMessage(),
                'user_id' => $parent->id,
            ]);
        }

        $this->redirectRoute('parent.pending', navigate: true);
    }

    public function render()
    {
        return view('livewire.parent-register', [
            'timezones' => DateTimeZone::listIdentifiers(),
        ]);
    }
}
