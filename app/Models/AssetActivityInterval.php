<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetActivityInterval extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'status',
        'local_date',
        'started_at_utc',
        'ended_at_utc',
        'duration_minutes',
    ];

    protected $casts = [
        'local_date' => 'date',
        'started_at_utc' => 'datetime',
        'ended_at_utc' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    /**
     * Parent Asset
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
