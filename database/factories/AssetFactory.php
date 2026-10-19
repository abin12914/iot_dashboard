<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->bothify('??-##')),
            'name' => fake()->randomElement(['Backhoe Loader', 'Crawler Bulldozer', 'Wheel Loader', 'Hydraulic Excavator']) . ' ' . fake()->numberBetween(10, 99),
            'type' => fake()->randomElement(['loader', 'bulldozer', 'excavator']),
            'timezone' => 'Asia/Dubai',
        ];
    }
}
