<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParentAccountSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'parent@dailystars.app'],
            [
                'name' => 'Parent Account',
                'password' => Hash::make('password'),
                'role' => 'parent',
                'timezone' => 'America/Los_Angeles',
            ]
        );
    }
}
