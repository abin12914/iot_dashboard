<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asset_activity_intervals', function (Blueprint $table) {
            // 1. Compound index for fast latest-status lookups per asset (hasOne latestOfMany / window functions)
            $table->index(['asset_id', 'ended_at_utc'], 'idx_asset_ended_at');
            
            // 2. Compound index for state transitions in AssetActivityIntervalService::recordPing()
            $table->index(['asset_id', 'started_at_utc'], 'idx_asset_started_at');

            // 3. Standalone index for global latest ping in navigation bar (HandleInertiaRequests)
            $table->index('ended_at_utc', 'idx_ended_at_utc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_activity_intervals', function (Blueprint $table) {
            $table->dropIndex('idx_asset_ended_at');
            $table->dropIndex('idx_asset_started_at');
            $table->dropIndex('idx_ended_at_utc');
        });
    }
};
