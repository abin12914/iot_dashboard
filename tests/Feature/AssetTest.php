<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetActivityInterval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_assets_on_inventory_page(): void
    {
        Asset::factory()->create(['code' => 'BL-01', 'name' => 'Loader 1']);
        Asset::factory()->create(['code' => 'BL-02', 'name' => 'Loader 2']);

        $response = $this->get('/assets');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Assets/Index')
                 ->has('assets.data', 2)
        );
    }

    public function test_can_register_new_heavy_equipment_asset(): void
    {
        $payload = [
            'code' => 'CAT-320',
            'name' => 'Caterpillar 320 Excavator',
            'type' => 'excavator',
            'timezone' => 'Asia/Dubai',
        ];

        $response = $this->post('/assets', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('assets', [
            'code' => 'CAT-320',
            'type' => 'excavator',
        ]);
    }

    public function test_cannot_register_duplicate_asset_code(): void
    {
        Asset::factory()->create(['code' => 'BL-08']);

        $payload = [
            'code' => 'BL-08',
            'name' => 'Duplicate Loader',
            'type' => 'loader',
            'timezone' => 'UTC',
        ];

        $response = $this->post('/assets', $payload);

        $response->assertSessionHasErrors(['code']);
        $this->assertEquals(1, Asset::where('code', 'BL-08')->count());
    }

    public function test_can_update_asset(): void
    {
        $asset = Asset::factory()->create([
            'code' => 'OLD-CODE',
            'name' => 'Old Machine',
            'type' => 'loader',
        ]);

        $response = $this->put("/assets/{$asset->id}", [
            'name' => 'Updated Fleet Machine',
            'type' => 'wheel_loader',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'name' => 'Updated Fleet Machine',
            'type' => 'wheel_loader',
        ]);
    }

    public function test_can_decommission_asset_and_cascade_intervals(): void
    {
        $asset = Asset::factory()->create();
        AssetActivityInterval::factory()->create(['asset_id' => $asset->id]);

        $response = $this->delete("/assets/{$asset->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
        $this->assertDatabaseMissing('asset_activity_intervals', ['asset_id' => $asset->id]);
    }
}
