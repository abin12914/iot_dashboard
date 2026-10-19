<?php

namespace Database\Seeders;

use App\Models\TelematicsDailyMetric;
use Illuminate\Database\Seeder;

class TelematicsDailyMetricSeeder extends Seeder
{
    public function run(): void
    {
        // Exact historical data matching all 4 charts across 20 Mar - 03 Apr
        $data = [
            ['metric_date' => '2026-03-20', 'date_label' => '20 Mar', 'dist_curr' => 14, 'dist_prev' => 13, 'eng_active' => 1.0,  'eng_idle' => 12.0, 'msgs' => 50],
            ['metric_date' => '2026-03-21', 'date_label' => '21 Mar', 'dist_curr' => 14, 'dist_prev' => 14, 'eng_active' => 18.0, 'eng_idle' => 14.0, 'msgs' => 3100],
            ['metric_date' => '2026-03-22', 'date_label' => '22 Mar', 'dist_curr' => 14, 'dist_prev' => 8,  'eng_active' => 17.0, 'eng_idle' => 10.0, 'msgs' => 3700],
            ['metric_date' => '2026-03-23', 'date_label' => '23 Mar', 'dist_curr' => 29, 'dist_prev' => 6,  'eng_active' => 16.0, 'eng_idle' => 11.0, 'msgs' => 3900],
            ['metric_date' => '2026-03-24', 'date_label' => '24 Mar', 'dist_curr' => 30, 'dist_prev' => 13, 'eng_active' => 11.0, 'eng_idle' => 11.0, 'msgs' => 4000],
            ['metric_date' => '2026-03-25', 'date_label' => '25 Mar', 'dist_curr' => 30, 'dist_prev' => 22, 'eng_active' => 22.0, 'eng_idle' => 9.0,  'msgs' => 4800],
            ['metric_date' => '2026-03-26', 'date_label' => '26 Mar', 'dist_curr' => 29, 'dist_prev' => 18, 'eng_active' => 15.0, 'eng_idle' => 11.0, 'msgs' => 3300],
            ['metric_date' => '2026-03-27', 'date_label' => '27 Mar', 'dist_curr' => 28, 'dist_prev' => 14, 'eng_active' => 20.0, 'eng_idle' => 11.0, 'msgs' => 3600],
            ['metric_date' => '2026-03-28', 'date_label' => '28 Mar', 'dist_curr' => 26, 'dist_prev' => 12, 'eng_active' => 17.0, 'eng_idle' => 11.0, 'msgs' => 3400],
            ['metric_date' => '2026-03-29', 'date_label' => '29 Mar', 'dist_curr' => 24, 'dist_prev' => 14, 'eng_active' => 4.0,  'eng_idle' => 15.0, 'msgs' => 3200],
            ['metric_date' => '2026-03-30', 'date_label' => '30 Mar', 'dist_curr' => 0,  'dist_prev' => 17, 'eng_active' => 0.0,  'eng_idle' => 0.0,  'msgs' => 50],
            ['metric_date' => '2026-03-31', 'date_label' => '31 Mar', 'dist_curr' => 0,  'dist_prev' => 33, 'eng_active' => 0.0,  'eng_idle' => 0.0,  'msgs' => 50],
            ['metric_date' => '2026-04-01', 'date_label' => '1 Apr',  'dist_curr' => 14, 'dist_prev' => 35, 'eng_active' => 4.0,  'eng_idle' => 0.0,  'msgs' => 900],
            ['metric_date' => '2026-04-02', 'date_label' => '2 Apr',  'dist_curr' => 27, 'dist_prev' => 27, 'eng_active' => 14.0, 'eng_idle' => 11.0, 'msgs' => 4700],
            ['metric_date' => '2026-04-03', 'date_label' => '3 Apr',  'dist_curr' => 22, 'dist_prev' => 25, 'eng_active' => 16.0, 'eng_idle' => 20.0, 'msgs' => 4100],
        ];

        foreach ($data as $item) {
            TelematicsDailyMetric::updateOrCreate(
                ['metric_date' => $item['metric_date']],
                [
                    'date_label' => $item['date_label'],
                    'distance_current_km' => $item['dist_curr'],
                    'distance_previous_km' => $item['dist_prev'],
                    'engine_hours_active' => $item['eng_active'],
                    'engine_hours_idle' => $item['eng_idle'],
                    'messages_received' => $item['msgs'],
                ]
            );
        }
    }
}
