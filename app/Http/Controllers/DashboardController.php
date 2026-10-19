<?php

namespace App\Http\Controllers;

use App\Services\TelematicsDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    protected TelematicsDashboardService $dashboardService;

    public function __construct(TelematicsDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the fleet overview telematics monitoring dashboard.
     */
    public function showFleetOverview(Request $request): Response
    {
        $selectedDate = $request->query('date', '2026-03-23');
        $windowStart  = $request->query('window_start', '10:30');
        $windowEnd    = $request->query('window_end', '18:30');
        $assetType    = $request->query('asset_type');
        $search       = $request->query('search');

        $assetsOverview = $this->dashboardService->getAssetDayOverview($selectedDate, $windowStart, $windowEnd, $assetType, $search);
        $kpiMetrics     = $this->dashboardService->getDashboardMetrics();

        return Inertia::render('Dashboard', [
            'assets'        => $assetsOverview,
            'dailyMetrics'  => $kpiMetrics['daily_series'],
            'summary'       => $kpiMetrics['summary'],
            'selectedDate'  => $selectedDate,
            'windowStart'   => $windowStart,
            'windowEnd'     => $windowEnd,
            'assetType'     => $assetType ?? 'all',
            'search'        => $search ?? '',
        ]);
    }

    /**
     * Refresh or filter only the Day Overview Gantt timeline.
     * Supports Inertia partial reloads and JSON asynchronous polling.
     */
    public function dayOverview(Request $request)
    {
        $selectedDate = $request->query('date', '2026-03-23');
        $windowStart  = $request->query('window_start', '10:30');
        $windowEnd    = $request->query('window_end', '18:30');
        $assetType    = $request->query('asset_type');
        $search       = $request->query('search');

        $assetsOverview = $this->dashboardService->getAssetDayOverview($selectedDate, $windowStart, $windowEnd, $assetType, $search);

        if ($request->wantsJson()) {
            return response()->json([
                'date'   => $selectedDate,
                'assets' => $assetsOverview,
            ]);
        }

        return Inertia::render('Dashboard', [
            'assets'       => $assetsOverview,
            'selectedDate' => $selectedDate,
        ]);
    }

    /**
     * Refresh or filter the 4 Analytics Cards (Traveled distance, Engine hours, Donut, Messages).
     */
    public function metrics(Request $request)
    {
        $kpiMetrics = $this->dashboardService->getDashboardMetrics();

        if ($request->wantsJson()) {
            return response()->json($kpiMetrics);
        }

        return Inertia::render('Dashboard', [
            'dailyMetrics' => $kpiMetrics['daily_series'],
            'summary'      => $kpiMetrics['summary'],
        ]);
    }
}
