<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelematicsDailyMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_date',
        'date_label',
        'distance_current_km',
        'distance_previous_km',
        'engine_hours_active',
        'engine_hours_idle',
        'messages_received',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'distance_current_km' => 'integer',
        'distance_previous_km' => 'integer',
        'engine_hours_active' => 'float',
        'engine_hours_idle' => 'float',
        'messages_received' => 'integer',
    ];
}
