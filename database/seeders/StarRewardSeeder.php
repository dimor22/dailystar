<?php

namespace Database\Seeders;

use App\Services\ParentRewardsProvisioningService;
use App\Models\User;
use Illuminate\Database\Seeder;

class StarRewardSeeder extends Seeder
{
    public function run(): void
    {
        $parent = User::query()
            ->where('role', 'parent')
            ->whereIn('email', ['parent@dailystars.app', 'dimor22@gmail.com'])
            ->first();

        if (! $parent) {
            return;
        }

        app(ParentRewardsProvisioningService::class)->provisionDefaults($parent);
    }
}
