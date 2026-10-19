<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetActivityInterval;
use App\Models\TelematicsDailyMetric;
use App\Services\TelematicsDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelematicsDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TelematicsDashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TelematicsDashboardService();
    }

    public function test_get_asset_day_overview_computes_operational_badges_and_timelines(): void
    {
        $asset = Asset::factory()->create([
            'code' => 'BL-08',
            'timezone' => 'Asia/Dubai',
        ]);

        // Create 2 intervals on 2026-03-23: 60 mins active, 30 mins idling
        AssetActivityInterval::factory()->create([
            'asset_id' => $asset->id,
            'status' => 'active',
            'local_date' => '2026-03-23',
            'started_at_utc' => '2026-03-23 07:00:00', // 11:00 Dubai
            'ended_at_utc' => '2026-03-23 08:00:00',   // 12:00 Dubai
            'duration_minutes' => 60,
        ]);

        AssetActivityInterval::factory()->create([
            'asset_id' => $asset->id,
            'status' => 'idling',
            'local_date' => '2026-03-23',
            'started_at_utc' => '2026-03-23 08:00:00', // 12:00 Dubai
            'ended_at_utc' => '2026-03-23 08:30:00',   // 12:30 Dubai
            'duration_minutes' => 30,
        ]);

        $overview = $this->service->getAssetDayOverview('2026-03-23', '10:30', '18:30');

        $this->assertCount(1, $overview);
        $assetData = $overview[0];

        $this->assertEquals('BL-08', $assetData['code']);
        $this->assertNotEmpty($assetData['badges']);
        $this->assertCount(2, $assetData['timelines']);

        // Check active ratio badge: active (60 mins) / running (90 mins) = 66.7%
        $activeBadge = collect($assetData['badges'])->firstWhere('type', 'active');
        $this->assertNotNull($activeBadge);
        $this->assertEquals(66.7, $activeBadge['value']);

        // Check idle ratio badge: idle (30 mins) / running (90 mins) = 33.3%
        $idleBadge = collect($assetData['badges'])->firstWhere('type', 'idle');
        $this->assertNotNull($idleBadge);
        $this->assertEquals(33.3, $idleBadge['value']);
    }

    public function test_get_dashboard_metrics_computes_distance_growth_and_summary(): void
    {
        TelematicsDailyMetric::factory()->create([
            'metric_date' => '2026-03-22',
            'distance_current_km' => 100,
            'distance_previous_km' => 80,
            'engine_hours_active' => 5.0,
            'engine_hours_idle' => 1.5,
        ]);

        TelematicsDailyMetric::factory()->create([
            'metric_date' => '2026-03-23',
            'distance_current_km' => 120,
            'distance_previous_km' => 100,
            'engine_hours_active' => 6.0,
            'engine_hours_idle' => 2.0,
        ]);

        $metrics = $this->service->getDashboardMetrics();

        $this->assertArrayHasKey('daily_series', $metrics);
        $this->assertArrayHasKey('summary', $metrics);
        $this->assertEquals(220, $metrics['summary']['total_distance_km']);
        // (220 - 180) / 180 = +22%
        $this->assertEquals('+ 22%', $metrics['summary']['distance_change_percentage']);
    }
}
