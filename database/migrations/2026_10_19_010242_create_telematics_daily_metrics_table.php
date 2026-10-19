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
        Schema::create('telematics_daily_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('metric_date')->unique(); // Calendar date
            $table->string('date_label');         // '20 Mar', '23 Mar', '1 Apr', etc.
            
            // 1. Traveled Distance (Km) - Comparative Period
            $table->unsignedInteger('distance_current_km')->default(0);
            $table->unsignedInteger('distance_previous_km')->default(0);

            // 2. Engine Hours Breakdown
            $table->decimal('engine_hours_active', 5, 1)->default(0); // Productive / Running
            $table->decimal('engine_hours_idle', 5, 1)->default(0);   // Idling

            // 3. Messages Received (IoT packet count)
            $table->unsignedInteger('messages_received')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telematics_daily_metrics');
    }
};
