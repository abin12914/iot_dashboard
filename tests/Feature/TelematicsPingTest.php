<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Notifications\TelematicsPingValidationFailedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TelematicsPingTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_telemetry_ping_creates_or_updates_interval(): void
    {
        $asset = Asset::factory()->create(['timezone' => 'Asia/Dubai']);

        $response = $this->postJson('/telematics/ping', [
            'asset_id'  => $asset->id,
            'status'    => 'active',
            'timestamp' => '2026-03-23T11:35:00Z',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Telemetry ping processed');
        $this->assertDatabaseHas('asset_activity_intervals', [
            'asset_id' => $asset->id,
            'status'   => 'active',
        ]);
    }

    public function test_failing_telemetry_ping_queues_notification_to_admin_and_returns_422(): void
    {
        Notification::fake();
        Cache::flush();

        // Send invalid payload (missing status and invalid asset_id)
        $response = $this->postJson('/telematics/ping', [
            'asset_id' => 99999, // non-existent asset
            'status'   => 'invalid_machine_status',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['asset_id', 'status']);

        // Assert notification was queued
        Notification::assertSentOnDemand(
            TelematicsPingValidationFailedNotification::class,
            function ($notification, $channels, $notifiable) {
                return in_array('mail', $channels) &&
                       $notification->queue === 'notifications' &&
                       isset($notification->errors['asset_id']) &&
                       isset($notification->errors['status']);
            }
        );
    }

    public function test_duplicate_failing_pings_within_hour_skip_subsequent_notifications(): void
    {
        Notification::fake();
        Cache::flush();

        $badPayload = [
            'asset_id' => 88888,
            'status'   => 'corrupt_status',
        ];

        // 1. First failing ping -> should notify
        $response1 = $this->postJson('/telematics/ping', $badPayload);
        $response1->assertStatus(422);
        Notification::assertSentTimes(TelematicsPingValidationFailedNotification::class, 1);

        // 2. Second failing ping immediately after with same anomaly -> should throttle/skip notification
        $response2 = $this->postJson('/telematics/ping', $badPayload);
        $response2->assertStatus(422);

        // Assert still only 1 notification was sent (2nd notification skipped!)
        Notification::assertSentTimes(TelematicsPingValidationFailedNotification::class, 1);
    }
}
