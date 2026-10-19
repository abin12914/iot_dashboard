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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. 'BL-08', 'Q-47898', 'D-26653'
            $table->string('name');            // e.g. 'Backhoe Loader 08'
            $table->string('type');            // e.g. 'loader', 'bulldozer'
            $table->string('timezone')->default('UTC'); // Deployment site timezone
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
