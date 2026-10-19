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
        Schema::create('asset_activity_intervals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('status'); // 'active', 'idling', 'ignition_off'
            $table->date('local_date'); // Partitioning/lookup date for fast daily queries
            $table->dateTime('started_at_utc'); // Absolute UTC start
            $table->dateTime('ended_at_utc');   // Absolute UTC end
            $table->unsignedInteger('duration_minutes'); // Cached duration in minutes
            $table->timestamps();

            // Compound index for instant single-day timeline lookups
            $table->index(['asset_id', 'local_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_activity_intervals');
    }
};
