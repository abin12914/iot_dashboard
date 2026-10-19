<?php

namespace App\Jobs;

use App\Models\AssetActivityInterval;
use App\Models\TelematicsDailyMetric;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AggregateDailyTelematicsJob implements ShouldQueue
{
    use Queueable;

    public string $targetDate;

    /**
     * Create a new job instance.
     *
     * @param string|null $date Target aggregation date (defaults to yesterday)
     */
    public function __construct(?string $date = null)
    {
        $this->targetDate = $date ?: Carbon::yesterday()->toDateString();
    }

    /**
     * Execute the daily metric rollup aggregation.
     */
    public function handle(): void
    {
        $date = $this->targetDate;
        $carbon = Carbon::parse($date);
        $dateLabel = $carbon->format('j M'); // e.g., '23 Mar'

        // 1. Fetch intervals for this date across all fleet assets
        $intervals = AssetActivityInterval::where('local_date', $date)->get();

        // 2. Sum Active & Idle minutes and convert to decimal engine hours (e.g. 16.5 hrs)
        $activeMinutes = $intervals->where('status', 'active')->sum('duration_minutes');
        $idleMinutes   = $intervals->where('status', 'idling')->sum('duration_minutes');

        $engineHoursActive = round($activeMinutes / 60, 1);
        $engineHoursIdle   = round($idleMinutes / 60, 1);

        // 3. Count total messages / state intervals recorded today
        $messagesCount = $intervals->count() * 120; // Simulated IoT frequency packet volume
        if ($messagesCount === 0 && $carbon->isWeekend()) {
            $messagesCount = 50; // Standby heartbeat
        }

        // 4. Retrieve or compute comparative previous period distance (e.g. 7 days prior)
        $priorWeekDate = $carbon->copy()->subDays(7)->toDateString();
        $priorMetric = TelematicsDailyMetric::where('metric_date', $priorWeekDate)->first();
        $distancePrevious = $priorMetric ? $priorMetric->distance_current_km : 12;

        // 5. Compute current traveled distance from active intervals
        // (Average telematics machinery travel: ~1.8 km per active hour)
        $distanceCurrent = (int) round($engineHoursActive * 1.8);

        // 6. Upsert the aggregate rollup record
        $dailyMetric = TelematicsDailyMetric::updateOrCreate(
            ['metric_date' => $date],
            [
                'date_label'            => $dateLabel,
                'distance_current_km'   => $distanceCurrent,
                'distance_previous_km'  => $distancePrevious,
                'engine_hours_active'   => $engineHoursActive,
                'engine_hours_idle'     => $engineHoursIdle,
                'messages_received'     => max(50, $messagesCount),
            ]
        );

        Log::info("Daily telematics metric aggregated successfully for {$date}", [
            'active_hours' => $engineHoursActive,
            'idle_hours'   => $engineHoursIdle,
            'distance_km'  => $distanceCurrent,
        ]);
    }
}
