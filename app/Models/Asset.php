<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Operational state intervals for this machine
     */
    public function activityIntervals(): HasMany
    {
        return $this->hasMany(AssetActivityInterval::class);
    }
}
