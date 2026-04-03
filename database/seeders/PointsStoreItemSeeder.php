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
            ['title' => 'Pick Dessert', 'description' => 'Choose tonight\'s dessert.', 'points' => 30, 'image_path' => 'points-store-items/pick-dessert.png', 'active' => true],
            ['title' => 'Extra Play Time', 'description' => '20 minutes extra play time.', 'points' => 50, 'image_path' => 'points-store-items/extra-play-time.png', 'active' => true],
            ['title' => 'Choose Family Game', 'description' => 'Pick the next family board game.', 'points' => 80, 'image_path' => 'points-store-items/family-game.png', 'active' => true],
            ['title' => 'Choose Weekend Activity', 'description' => 'Pick one family outing or activity.', 'points' => 120, 'image_path' => 'points-store-items/points-activities copy 2.png', 'active' => true],
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
