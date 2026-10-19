<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            [
                'code' => 'BL-08',
                'name' => 'Backhoe Loader 08',
                'type' => 'loader',
                'timezone' => 'Asia/Dubai',
            ],
            [
                'code' => 'Q-47898',
                'name' => 'Crawler Bulldozer Q-47',
                'type' => 'bulldozer',
                'timezone' => 'Asia/Dubai',
            ],
            [
                'code' => 'D-26653',
                'name' => 'Wheel Loader D-26',
                'type' => 'loader',
                'timezone' => 'Asia/Dubai',
            ],
        ];

        foreach ($assets as $asset) {
            Asset::updateOrCreate(['code' => $asset['code']], $asset);
        }
    }
}
