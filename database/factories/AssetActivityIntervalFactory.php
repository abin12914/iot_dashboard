<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetActivityInterval;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssetActivityInterval>
 */
class AssetActivityIntervalFactory extends Factory
{
    protected $model = AssetActivityInterval::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-7 days', 'now');
        $startUtc = Carbon::instance($date)->setTime(fake()->numberBetween(6, 17), fake()->randomElement([0, 15, 30, 45]));
        $duration = fake()->numberBetween(15, 180);
        $endUtc = $startUtc->copy()->addMinutes($duration);

        return [
            'asset_id' => Asset::factory(),
            'status' => fake()->randomElement(['active', 'idling', 'ignition_off']),
            'local_date' => $startUtc->toDateString(),
            'started_at_utc' => $startUtc,
            'ended_at_utc' => $endUtc,
            'duration_minutes' => $duration,
        ];
    }

    /**
     * Indicate that the interval is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the interval is idling.
     */
    public function idling(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'idling',
        ]);
    }

    /**
     * Indicate that the interval is off.
     */
    public function off(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ignition_off',
        ]);
    }
}
