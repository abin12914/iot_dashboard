<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'timezone',
    ];

    /**
     * All operational state intervals for this machine.
     */
    public function activityIntervals(): HasMany
    {
        return $this->hasMany(AssetActivityInterval::class);
    }

    /**
     * Optimized relationship for single latest interval lookup using Laravel latestOfMany.
     */
    public function latestActivityInterval(): HasOne
    {
        return $this->hasOne(AssetActivityInterval::class)->latestOfMany('ended_at_utc');
    }
}
