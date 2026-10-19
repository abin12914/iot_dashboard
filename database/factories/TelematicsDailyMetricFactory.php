<?php

namespace Database\Factories;

use App\Models\TelematicsDailyMetric;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TelematicsDailyMetric>
 */
class TelematicsDailyMetricFactory extends Factory
{
    protected $model = TelematicsDailyMetric::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->unique()->dateTimeBetween('-30 days', 'now');
        $carbon = Carbon::instance($date);

        return [
            'metric_date' => $carbon->toDateString(),
            'date_label' => $carbon->format('j M'), // e.g., '23 Mar'
            'distance_current_km' => fake()->numberBetween(0, 40),
            'distance_previous_km' => fake()->numberBetween(5, 35),
            'engine_hours_active' => fake()->randomFloat(1, 0, 24),
            'engine_hours_idle' => fake()->randomFloat(1, 0, 15),
            'messages_received' => fake()->numberBetween(50, 5000),
        ];
    }
}
