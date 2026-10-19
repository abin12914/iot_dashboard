<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetActivityInterval;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AssetActivityIntervalSeeder extends Seeder
{
    public function run(): void
    {
        $bl08 = Asset::where('code', 'BL-08')->first();
        $q47898 = Asset::where('code', 'Q-47898')->first();
        $d26653 = Asset::where('code', 'D-26653')->first();

        if (!$bl08 || !$q47898 || !$d26653) {
            return;
        }

        $date = '2026-03-23'; // The date displayed in the assessment screenshot

        // Helper to insert intervals accurately by local start and end time
        $seedIntervals = function ($asset, $intervals) use ($date) {
            foreach ($intervals as $item) {
                $start = Carbon::parse("{$date} {$item['start']}", $asset->timezone);
                $end   = Carbon::parse("{$date} {$item['end']}", $asset->timezone);
                $duration = $start->diffInMinutes($end);

                AssetActivityInterval::create([
                    'asset_id' => $asset->id,
                    'status' => $item['status'],
                    'local_date' => $date,
                    'started_at_utc' => $start->copy()->setTimezone('UTC'),
                    'ended_at_utc' => $end->copy()->setTimezone('UTC'),
                    'duration_minutes' => max(1, $duration),
                ]);
            }
        };

        // 1. BL-08 Intervals (matching screenshot ribbon)
        $seedIntervals($bl08, [
            ['status' => 'idling',       'start' => '10:45', 'end' => '11:35'],
            ['status' => 'active',       'start' => '11:35', 'end' => '11:55'],
            ['status' => 'idling',       'start' => '11:55', 'end' => '12:10'],
            ['status' => 'ignition_off', 'start' => '12:45', 'end' => '12:50'],
            ['status' => 'active',       'start' => '12:50', 'end' => '15:40'],
            ['status' => 'active',       'start' => '15:50', 'end' => '16:10'],
            ['status' => 'idling',       'start' => '16:10', 'end' => '16:30'],
            ['status' => 'active',       'start' => '16:30', 'end' => '16:55'],
            ['status' => 'active',       'start' => '17:15', 'end' => '17:30'],
        ]);

        // 2. Q-47898 Intervals (matching screenshot ribbon)
        $seedIntervals($q47898, [
            ['status' => 'active',       'start' => '10:45', 'end' => '12:55'],
            ['status' => 'ignition_off', 'start' => '12:55', 'end' => '13:05'],
            ['status' => 'active',       'start' => '14:05', 'end' => '16:30'],
            ['status' => 'ignition_off', 'start' => '16:30', 'end' => '16:40'],
            ['status' => 'active',       'start' => '16:40', 'end' => '17:30'],
            ['status' => 'ignition_off', 'start' => '17:30', 'end' => '17:40'],
            ['status' => 'active',       'start' => '17:50', 'end' => '18:05'],
        ]);

        // 3. D-26653 Intervals (matching screenshot ribbon)
        $seedIntervals($d26653, [
            ['status' => 'active',       'start' => '10:45', 'end' => '11:05'],
            ['status' => 'idling',       'start' => '11:05', 'end' => '13:05'],
            ['status' => 'ignition_off', 'start' => '13:05', 'end' => '13:10'],
            ['status' => 'idling',       'start' => '13:10', 'end' => '13:45'],
            ['status' => 'active',       'start' => '13:45', 'end' => '13:55'],
            ['status' => 'active',       'start' => '14:00', 'end' => '14:20'],
            ['status' => 'idling',       'start' => '14:20', 'end' => '14:50'],
            ['status' => 'active',       'start' => '14:50', 'end' => '15:20'],
            ['status' => 'idling',       'start' => '15:20', 'end' => '15:35'],
            ['status' => 'active',       'start' => '15:35', 'end' => '16:40'],
            ['status' => 'idling',       'start' => '16:40', 'end' => '17:00'],
            ['status' => 'active',       'start' => '17:00', 'end' => '17:15'],
            ['status' => 'active',       'start' => '17:35', 'end' => '17:55'],
            ['status' => 'idling',       'start' => '17:55', 'end' => '18:15'],
            ['status' => 'active',       'start' => '18:15', 'end' => '18:40'],
        ]);
    }
}
