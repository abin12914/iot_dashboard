<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssetController extends Controller
{
    /**
     * Display a standard listing of assets (Master Asset Directory).
     */
    public function index(Request $request)
    {
        $assets = Asset::orderBy('code')->paginate(15);

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
                'data' => $asset,
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
                'data' => $asset,
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
