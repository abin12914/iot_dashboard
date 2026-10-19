<?php

namespace App\Http\Controllers;

use App\Http\Requests\Asset\StoreAssetRequest;
use App\Http\Requests\Asset\UpdateAssetRequest;
use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssetController extends Controller
{
    /**
     * Display a listing of assets with their latest known operational status and last ping time.
     * Uses optimized latestActivityInterval (latestOfMany) backed by compound index [asset_id, ended_at_utc].
     * If the machine has not emitted telemetry in > 24 hours, it is marked as "offline".
     */
    public function index(Request $request)
    {
        $assets = Asset::with('latestActivityInterval')
            ->orderBy('code')
            ->paginate(15);

        $assets->getCollection()->transform(function ($asset) {
            $latestInterval = $asset->latestActivityInterval;
            $timezone = $asset->timezone ?: 'Asia/Dubai';

            if ($latestInterval) {
                $endedAtUtc = Carbon::parse($latestInterval->ended_at_utc);
                $lastSeen = $endedAtUtc->copy()->setTimezone($timezone);

                // Check if device is offline (> 24 hours since last telemetry ping)
                $isOffline = $endedAtUtc->diffInHours(now('UTC')) > 24;

                $asset->last_status = $isOffline ? 'offline' : $latestInterval->status;
                $asset->raw_status = $latestInterval->status;
                $asset->is_offline = $isOffline;
                $asset->last_active_time = $lastSeen->format('M d, H:i');
                $asset->last_active_diff = $endedAtUtc->diffForHumans();
            } else {
                $asset->last_status = 'offline';
                $asset->raw_status = 'no_data';
                $asset->is_offline = true;
                $asset->last_active_time = 'Never';
                $asset->last_active_diff = 'No telemetry';
            }

            return $asset;
        });

        if ($request->wantsJson()) {
            return response()->json($assets);
        }

        return Inertia::render('Assets/Index', [
            'assets' => $assets,
        ]);
    }

    /**
     * Store a newly created asset.
     */
    public function store(StoreAssetRequest $request)
    {
        $asset = Asset::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Asset registered successfully',
                'data'    => $asset,
            ], 201);
        }

        return redirect()->back()->with('success', 'Asset registered successfully.');
    }

    /**
     * Display the specified asset.
     */
    public function show(Request $request, Asset $asset)
    {
        if ($request->wantsJson()) {
            return response()->json($asset);
        }

        return Inertia::render('Assets/Show', [
            'asset' => $asset,
        ]);
    }

    /**
     * Update the specified asset.
     */
    public function update(UpdateAssetRequest $request, Asset $asset)
    {
        $asset->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Asset updated successfully',
                'data'    => $asset,
            ]);
        }

        return redirect()->back()->with('success', 'Asset updated successfully.');
    }

    /**
     * Remove the specified asset.
     */
    public function destroy(Request $request, Asset $asset)
    {
        $asset->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Asset deleted successfully',
            ]);
        }

        return redirect()->back()->with('success', 'Asset deleted successfully.');
    }
}
