<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetActivityInterval;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AssetActivityIntervalService
{
    /**
     * Get intervals for an asset filtered by date.
     */
    public function getIntervalsForAsset(Asset $asset, string $date): Collection
    {
        return $asset->activityIntervals()
            ->where('local_date', $date)
            ->orderBy('started_at_utc', 'asc')
            ->get();
    }

    /**
     * Ingest an incoming IoT telemetry ping and manage interval state transitions.
     *
     * Handles:
     * 1. Extending the ongoing interval if status is unchanged (heartbeat)
     * 2. Finalizing previous interval and opening a new one on status transition
     * 3. Splitting intervals across midnight based on the machine's local timezone
     *
     * @param int $assetId
     * @param string $status 'active', 'idling', 'ignition_off'
     * @param string|Carbon|null $pingTimestamp
     * @return AssetActivityInterval
     */
    public function recordPing(int $assetId, string $status, $pingTimestamp = null): AssetActivityInterval
    {
        $asset = Asset::findOrFail($assetId);
        $timezone = $asset->timezone ?: 'Asia/Dubai';

        $pingUtc = $pingTimestamp ? Carbon::parse($pingTimestamp)->setTimezone('UTC') : now('UTC');
        $localPing = $pingUtc->copy()->setTimezone($timezone);
        $localDate = $localPing->toDateString();

        // 1. Fetch the latest interval for this machine
        $latest = AssetActivityInterval::where('asset_id', $asset->id)
            ->latest('started_at_utc')
            ->first();

        // Scenario A: First ping ever recorded for this machine
        if (!$latest) {
            return AssetActivityInterval::create([
                'asset_id'         => $asset->id,
                'status'           => $status,
                'local_date'       => $localDate,
                'started_at_utc'   => $pingUtc,
                'ended_at_utc'     => $pingUtc,
                'duration_minutes' => 1,
            ]);
        }

        $latestLocalStart = Carbon::parse($latest->started_at_utc)->setTimezone($timezone);

        // Scenario B: Machine crossed midnight in its local operational timezone!
        if ($latestLocalStart->toDateString() !== $localDate) {
            // Close the previous day's interval at 23:59:59 local
            $localMidnight = $latestLocalStart->copy()->endOfDay();
            $utcMidnight = $localMidnight->copy()->setTimezone('UTC');

            $latest->update([
                'ended_at_utc'     => $utcMidnight,
                'duration_minutes' => max(1, Carbon::parse($latest->started_at_utc)->diffInMinutes($utcMidnight)),
            ]);

            // Open new interval for the new day starting at 00:00:00 local
            $newDayStartUtc = $localPing->copy()->startOfDay()->setTimezone('UTC');

            return AssetActivityInterval::create([
                'asset_id'         => $asset->id,
                'status'           => $status,
                'local_date'       => $localDate,
                'started_at_utc'   => $newDayStartUtc,
                'ended_at_utc'     => $pingUtc,
                'duration_minutes' => max(1, $newDayStartUtc->diffInMinutes($pingUtc)),
            ]);
        }

        // Scenario C: Same status heartbeat within the same day -> extend ended_at_utc
        if ($latest->status === $status) {
            $duration = max(1, Carbon::parse($latest->started_at_utc)->diffInMinutes($pingUtc));

            $latest->update([
                'ended_at_utc'     => $pingUtc,
                'duration_minutes' => $duration,
            ]);

            return $latest;
        }

        // Scenario D: Operational Status Transition (e.g. idling -> active)
        // 1. Finalize previous interval
        $latest->update([
            'ended_at_utc'     => $pingUtc,
            'duration_minutes' => max(1, Carbon::parse($latest->started_at_utc)->diffInMinutes($pingUtc)),
        ]);

        // 2. Insert new interval for the new status
        return AssetActivityInterval::create([
            'asset_id'         => $asset->id,
            'status'           => $status,
            'local_date'       => $localDate,
            'started_at_utc'   => $pingUtc,
            'ended_at_utc'     => $pingUtc,
            'duration_minutes' => 1,
        ]);
    }

    /**
     * Manual creation of an interval (admin override).
     */
    public function createInterval(array $data): AssetActivityInterval
    {
        $asset = Asset::findOrFail($data['asset_id']);
        $timezone = $asset->timezone ?: 'Asia/Dubai';

        $startUtc = Carbon::parse($data['started_at_utc']);
        $endUtc   = Carbon::parse($data['ended_at_utc']);

        $duration = max(1, $startUtc->diffInMinutes($endUtc));
        $localDate = $startUtc->copy()->setTimezone($timezone)->toDateString();

        return AssetActivityInterval::create([
            'asset_id'         => $asset->id,
            'status'           => $data['status'],
            'local_date'       => $localDate,
            'started_at_utc'   => $startUtc,
            'ended_at_utc'     => $endUtc,
            'duration_minutes' => $duration,
        ]);
    }

    /**
     * Update an existing interval.
     */
    public function updateInterval(AssetActivityInterval $interval, array $data): AssetActivityInterval
    {
        $startUtc = isset($data['started_at_utc']) ? Carbon::parse($data['started_at_utc']) : $interval->started_at_utc;
        $endUtc   = isset($data['ended_at_utc']) ? Carbon::parse($data['ended_at_utc']) : $interval->ended_at_utc;

        $data['duration_minutes'] = max(1, $startUtc->diffInMinutes($endUtc));

        $timezone = $interval->asset->timezone ?: 'Asia/Dubai';
        $data['local_date'] = $startUtc->copy()->setTimezone($timezone)->toDateString();

        $interval->update($data);

        return $interval;
    }

    /**
     * Delete an interval.
     */
    public function deleteInterval(AssetActivityInterval $interval): void
    {
        $interval->delete();
    }
}
