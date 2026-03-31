<?php

namespace Database\Seeders;

use App\Models\PointsStoreItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class PointsStoreItemSeeder extends Seeder
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

        $items = [
            ['title' => 'Pick Dessert', 'description' => 'Choose tonight\'s dessert.', 'points' => 30, 'active' => true],
            ['title' => 'Extra Play Time', 'description' => '20 minutes extra play time.', 'points' => 50, 'active' => true],
            ['title' => 'Choose Family Game', 'description' => 'Pick the next family board game.', 'points' => 80, 'active' => true],
            ['title' => 'Choose Weekend Activity', 'description' => 'Pick one family outing or activity.', 'points' => 120, 'active' => true],
        ];

        foreach ($items as $item) {
            PointsStoreItem::query()->updateOrCreate(
                [
                    'parent_id' => $parent->id,
                    'title' => $item['title'],
                ],
                $item
            );
        }
    }
}
