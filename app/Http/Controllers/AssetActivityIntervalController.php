<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssetActivityInterval\StoreAssetActivityIntervalRequest;
use App\Http\Requests\AssetActivityInterval\UpdateAssetActivityIntervalRequest;
use App\Models\Asset;
use App\Models\AssetActivityInterval;
use App\Services\AssetActivityIntervalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetActivityIntervalController extends Controller
{
    protected AssetActivityIntervalService $intervalService;

    public function __construct(AssetActivityIntervalService $intervalService)
    {
        $this->intervalService = $intervalService;
    }

    /**
     * Display a listing of intervals for an asset.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'asset_id' => ['required', 'integer', 'exists:assets,id'],
            'date'     => ['nullable', 'date'],
        ]);

        $asset = Asset::findOrFail($request->query('asset_id'));
        $date = $request->query('date', '2026-03-23');

        $intervals = $this->intervalService->getIntervalsForAsset($asset, $date);

        return response()->json([
            'asset'     => $asset,
            'date'      => $date,
            'intervals' => $intervals,
        ]);
    }

    /**
     * Ingest an incoming IoT Telemetry Ping.
     * Automatically handles heartbeat extension, state transitions, and midnight rollovers.
     */
    public function ping(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset_id'  => ['required', 'integer', 'exists:assets,id'],
            'status'    => ['required', 'string', Rule::in(['active', 'idling', 'ignition_off'])],
            'timestamp' => ['nullable', 'date'],
        ]);

        $interval = $this->intervalService->recordPing(
            $validated['asset_id'],
            $validated['status'],
            $validated['timestamp'] ?? null
        );

        return response()->json([
            'message'  => 'Telemetry ping processed',
            'interval' => $interval,
        ]);
    }

    /**
     * Store a manually created interval.
     */
    public function store(StoreAssetActivityIntervalRequest $request)
    {
        $interval = $this->intervalService->createInterval($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Activity interval logged successfully',
                'data'    => $interval,
            ], 201);
        }

        return redirect()->back()->with('success', 'Activity interval logged successfully.');
    }

    /**
     * Display the specified interval.
     */
    public function show(AssetActivityInterval $assetActivityInterval): JsonResponse
    {
        return response()->json($assetActivityInterval->load('asset'));
    }

    /**
     * Update the specified interval.
     */
    public function update(UpdateAssetActivityIntervalRequest $request, AssetActivityInterval $assetActivityInterval)
    {
        $interval = $this->intervalService->updateInterval($assetActivityInterval, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Activity interval updated successfully',
                'data'    => $interval,
            ]);
        }

        return redirect()->back()->with('success', 'Activity interval updated successfully.');
    }

    /**
     * Remove the specified interval.
     */
    public function destroy(Request $request, AssetActivityInterval $assetActivityInterval)
    {
        $this->intervalService->deleteInterval($assetActivityInterval);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Activity interval deleted successfully',
            ]);
        }

        return redirect()->back()->with('success', 'Activity interval deleted successfully.');
    }
}
