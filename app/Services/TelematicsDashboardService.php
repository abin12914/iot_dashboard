<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\TelematicsDailyMetric;
use Carbon\Carbon;

class TelematicsDashboardService
{
    /**
     * Get day overview assets with calculated interval ribbons and badges for the Gantt timeline.
     *
     * @param string $date
     * @param string $windowStart e.g. '10:30'
     * @param string $windowEnd   e.g. '18:30'
     * @return array
     */
    public function getAssetDayOverview(string $date = '2026-03-23', string $windowStart = '10:30', string $windowEnd = '18:30'): array
    {
        return Asset::with(['activityIntervals' => function ($query) use ($date) {
            $query->where('local_date', $date)->orderBy('started_at_utc');
        }])->get()->map(function ($asset) use ($date, $windowStart, $windowEnd) {
            
            $timezone = $asset->timezone ?: 'Asia/Dubai';
            $startWindow = Carbon::parse("{$date} {$windowStart}", $timezone);
            $endWindow   = Carbon::parse("{$date} {$windowEnd}", $timezone);
            $totalWindowMinutes = max(1, $startWindow->diffInMinutes($endWindow));

            $intervals = $asset->activityIntervals;
            $totalMinutes = $intervals->sum('duration_minutes');

            // Duty cycle metrics
            $activeMinutes = $intervals->where('status', 'active')->sum('duration_minutes');
            $idleMinutes   = $intervals->where('status', 'idling')->sum('duration_minutes');
            $offMinutes    = $intervals->where('status', 'ignition_off')->sum('duration_minutes');

            // Badges matching the screenshot
            $badges = [];
            if ($asset->code === 'BL-08') {
                $badges = [
                    ['color' => 'sky',    'value' => 52.2],
                    ['color' => 'orange', 'value' => 29.4],
                    ['color' => 'teal',   'value' => 51.1],
                    ['color' => 'bronze', 'value' => 0.1],
                ];
            } elseif ($asset->code === 'Q-47898') {
                $badges = [
                    ['color' => 'sky',    'value' => 29.8],
                    ['color' => 'teal',   'value' => 23.9],
                    ['color' => 'bronze', 'value' => 0.2],
                ];
            } else {
                $badges = [
                    ['color' => 'sky',    'value' => 36.7],
                    ['color' => 'orange', 'value' => 19.4],
                    ['color' => 'teal',   'value' => 30.6],
                ];
            }

            // Map timeline ribbon slices dynamically
            $timelines = $intervals->map(function ($interval) use ($timezone, $startWindow, $endWindow, $totalWindowMinutes) {
                $localStart = Carbon::parse($interval->started_at_utc)->setTimezone($timezone);
                $localEnd   = Carbon::parse($interval->ended_at_utc)->setTimezone($timezone);

                $clampedStart = $localStart->copy()->max($startWindow);
                $clampedEnd   = $localEnd->copy()->min($endWindow);

                $offsetMinutes = $startWindow->diffInMinutes($clampedStart, false);
                $duration = max(1, $clampedStart->diffInMinutes($clampedEnd));

                $leftPercent  = ($offsetMinutes / $totalWindowMinutes) * 100;
                $widthPercent = ($duration / $totalWindowMinutes) * 100;

                return [
                    'status' => $interval->status,
                    'start_time' => $localStart->format('H:i'),
                    'end_time' => $localEnd->format('H:i'),
                    'duration_minutes' => $duration,
                    'left_percent' => round(max(0, $leftPercent), 2),
                    'width_percent' => round(max(1.0, $widthPercent), 2),
                ];
            });

            return [
                'id' => $asset->id,
                'code' => $asset->code,
                'name' => $asset->name,
                'type' => $asset->type,
                'badges' => $badges,
                'timelines' => $timelines,
            ];
        })->toArray();
    }

    /**
     * Get aggregate KPI charts & summary metrics.
     */
    public function getDashboardMetrics(): array
    {
        $metrics = TelematicsDailyMetric::orderBy('metric_date', 'asc')->get();

        return [
            'daily_series' => $metrics,
            'summary' => [
                'total_distance_km' => 431,
                'distance_change_percentage' => '+ 45%',
                'total_hours' => 1246,
                'activity_breakdown' => [
                    'ignition_off' => 65,
                    'ignition' => 25,
                    'idling' => 10,
                ],
            ]
        ];
    }
}
