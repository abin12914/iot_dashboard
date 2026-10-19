<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $latestInterval = \App\Models\AssetActivityInterval::with('asset')
            ->orderByDesc('ended_at_utc')
            ->first();

        $lastPingData = null;
        if ($latestInterval) {
            $timestamp = $latestInterval->ended_at_utc ?? $latestInterval->started_at_utc;
            $tz = $latestInterval->asset?->timezone ?? config('app.timezone', 'UTC');
            $lastPingData = [
                'asset_code' => $latestInterval->asset?->code ?? 'Unknown',
                'formatted_time' => $timestamp ? $timestamp->copy()->setTimezone($tz)->format('d M, H:i') : null,
                'human_time' => $timestamp ? $timestamp->diffForHumans() : null,
                'timestamp_utc' => $timestamp?->toISOString(),
            ];
        }

        $assetsList = \App\Models\Asset::orderBy('code')->get(['id', 'code', 'name', 'type']);

        return [
            ...parent::share($request),
            'appName' => config('app.name', 'Telematics'),
            'lastPing' => $lastPingData,
            'simulatorAssets' => $assetsList,
            'auth' => [
                'user' => $request->user(),
            ],
        ];
    }
}
