<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetActivityInterval;
use App\Models\TelematicsDailyMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_fleet_telematics_dashboard(): void
    {
        $asset = Asset::factory()->create([
            'code' => 'BL-08',
            'timezone' => 'Asia/Dubai',
        ]);

        AssetActivityInterval::factory()->create([
            'asset_id' => $asset->id,
            'local_date' => '2026-03-23',
            'status' => 'active',
            'duration_minutes' => 60,
        ]);

        TelematicsDailyMetric::factory()->create([
            'metric_date' => '2026-03-23',
            'date_label' => '23 Mar',
            'distance_current_km' => 45,
            'distance_previous_km' => 30,
        ]);

        $response = $this->get('/?date=2026-03-23');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
                ->has('assets')
                ->has('dailyMetrics')
                ->has('summary')
                ->where('selectedDate', '2026-03-23')
        );
    }

    public function test_dashboard_shares_app_name_and_last_ping_in_props(): void
    {
        $asset = Asset::factory()->create([
            'code' => 'TEST-01',
            'timezone' => 'Asia/Dubai',
        ]);

        AssetActivityInterval::factory()->create([
            'asset_id' => $asset->id,
            'ended_at_utc' => '2026-03-23 15:30:00',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->has('appName')
                 ->has('lastPing.asset_code')
                 ->where('lastPing.asset_code', 'TEST-01')
        );
    }

    public function test_can_fetch_day_overview_partial_json(): void
    {
        $asset = Asset::factory()->create(['code' => 'Q-47898']);

        $response = $this->getJson('/dashboard/day-overview?date=2026-03-23');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'date',
            'assets' => [
                '*' => ['id', 'code', 'name', 'type', 'badges', 'timelines']
            ]
        ]);
    }

    public function test_can_fetch_metrics_partial_json(): void
    {
        TelematicsDailyMetric::factory()->create([
            'metric_date' => '2026-03-23',
            'distance_current_km' => 50,
        ]);

        $response = $this->getJson('/dashboard/metrics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'daily_series',
            'summary' => [
                'total_distance_km',
                'distance_change_percentage',
                'total_hours',
                'activity_breakdown'
            ]
        ]);
    }
}
