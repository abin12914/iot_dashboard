<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetActivityInterval;
use App\Services\AssetActivityIntervalService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetActivityIntervalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AssetActivityIntervalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AssetActivityIntervalService();
    }

    public function test_first_ping_creates_new_interval(): void
    {
        $asset = Asset::factory()->create(['timezone' => 'Asia/Dubai']);

        $interval = $this->service->recordPing($asset->id, 'active', '2026-03-23 10:00:00');

        $this->assertInstanceOf(AssetActivityInterval::class, $interval);
        $this->assertEquals($asset->id, $interval->asset_id);
        $this->assertEquals('active', $interval->status);
        $this->assertEquals('2026-03-23', Carbon::parse($interval->local_date)->toDateString());
        $this->assertEquals(1, $interval->duration_minutes);
    }

    public function test_same_status_heartbeat_extends_existing_interval(): void
    {
        $asset = Asset::factory()->create(['timezone' => 'Asia/Dubai']);

        // First ping at 10:00:00 UTC
        $this->service->recordPing($asset->id, 'active', '2026-03-23 10:00:00');

        // Heartbeat ping at 10:25:00 UTC (same day, same status)
        $extended = $this->service->recordPing($asset->id, 'active', '2026-03-23 10:25:00');

        $this->assertEquals(1, AssetActivityInterval::where('asset_id', $asset->id)->count());
        $this->assertEquals(25, $extended->duration_minutes);
    }

    public function test_status_transition_finalizes_previous_and_opens_new_interval(): void
    {
        $asset = Asset::factory()->create(['timezone' => 'Asia/Dubai']);

        // First interval: active from 10:00 to 10:30 UTC
        $this->service->recordPing($asset->id, 'active', '2026-03-23 10:00:00');
        $this->service->recordPing($asset->id, 'active', '2026-03-23 10:30:00');

        // Status transition to idling at 10:30:00 UTC
        $newInterval = $this->service->recordPing($asset->id, 'idling', '2026-03-23 10:30:00');

        $this->assertEquals(2, AssetActivityInterval::where('asset_id', $asset->id)->count());
        $this->assertEquals('idling', $newInterval->status);
        $this->assertEquals(1, $newInterval->duration_minutes);
    }

    public function test_midnight_rollover_splits_interval_across_local_days(): void
    {
        $asset = Asset::factory()->create(['timezone' => 'Asia/Dubai']);

        // 23:30 local in Dubai is 19:30 UTC on 2026-03-22
        $this->service->recordPing($asset->id, 'idling', '2026-03-22 19:30:00');

        // Next ping at 00:15 local on next day (20:15 UTC on 2026-03-22, which is 2026-03-23 in Dubai)
        $nextDayInterval = $this->service->recordPing($asset->id, 'idling', '2026-03-22 20:15:00');

        $this->assertEquals(2, AssetActivityInterval::where('asset_id', $asset->id)->count());
        $this->assertEquals('2026-03-23', Carbon::parse($nextDayInterval->local_date)->toDateString());
    }
}
