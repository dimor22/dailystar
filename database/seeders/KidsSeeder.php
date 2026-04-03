<?php

namespace Database\Seeders;

use App\Models\Kid;
use App\Models\User;
use Illuminate\Database\Seeder;

class KidsSeeder extends Seeder
{
    public function run(): void
    {
        $parent = User::query()
            ->where('role', 'parent')
            ->where('email', 'parent@dailystars.app')
            ->first();

        if (! $parent) {
            return;
        }

        $kids = [
            ['name' => 'Shaila', 'avatar' => '🦁', 'color' => 'bg-blue-500', 'pin' => '1111'],
            ['name' => 'Amaira', 'avatar' => '🦄', 'color' => 'bg-pink-500', 'pin' => '1111'],
            ['name' => 'Benjamin', 'avatar' => '🚀', 'color' => 'bg-green-500', 'pin' => '1111'],
            ['name' => 'Evelin', 'avatar' => '🌈', 'color' => 'bg-yellow-500', 'pin' => '1111'],
        ];

        foreach ($kids as $kidData) {
            Kid::query()->updateOrCreate(
                [
                    'parent_id' => $parent->id,
                    'name' => $kidData['name'],
                ],
                $kidData + ['points' => 0]
            );
        }
    }
}
