<?php

namespace Eduardoks98\AdsAdsense\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Eduardoks98\AdsAdsense\Models\AdUnit;
use Eduardoks98\AdsAdsense\Enums\AdFormat;

class AdUnitController extends Controller
{
    /**
     * Display a listing of ad units.
     */
    public function index()
    {
        $adUnits = AdUnit::orderBy('name')->get();

        $stats = [
            'total' => $adUnits->count(),
            'active' => $adUnits->where('is_active', true)->count(),
            'global' => $adUnits->whereNull('game_id')->count(),
        ];

        $gameModel = config('adsense.game_model');
        $hasGames = $gameModel && class_exists($gameModel);

        return view('adsense::ad-units.index', compact('adUnits', 'stats', 'hasGames'));
    }

    /**
     * Show the form for creating a new ad unit.
     */
    public function create()
    {
        $formats = AdFormat::options();
        $gameModel = config('adsense.game_model');
        $hasGames = $gameModel && class_exists($gameModel);
        $games = $hasGames ? $gameModel::orderBy('name')->get() : collect();

        return view('adsense::ad-units.create', compact('formats', 'hasGames', 'games'));
    }

    /**
     * Store a newly created ad unit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slot_id' => 'required|string|max:50',
            'game_id' => 'nullable|integer',
            'format' => 'required|string|in:' . implode(',', array_keys(AdFormat::options())),
            'position' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'metadata' => 'nullable|array',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        AdUnit::create($validated);

        return redirect()
            ->route('admin.ad-units.index')
            ->with('success', 'Ad Unit criado com sucesso!');
    }

    /**
     * Show the form for editing the specified ad unit.
     */
    public function edit(AdUnit $adUnit)
    {
        $formats = AdFormat::options();
        $gameModel = config('adsense.game_model');
        $hasGames = $gameModel && class_exists($gameModel);
        $games = $hasGames ? $gameModel::orderBy('name')->get() : collect();

        return view('adsense::ad-units.edit', compact('adUnit', 'formats', 'hasGames', 'games'));
    }

    /**
     * Update the specified ad unit.
     */
    public function update(Request $request, AdUnit $adUnit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slot_id' => 'required|string|max:50',
            'game_id' => 'nullable|integer',
            'format' => 'required|string|in:' . implode(',', array_keys(AdFormat::options())),
            'position' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'metadata' => 'nullable|array',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $adUnit->update($validated);

        return redirect()
            ->route('admin.ad-units.index')
            ->with('success', 'Ad Unit atualizado com sucesso!');
    }

    /**
     * Remove the specified ad unit.
     */
    public function destroy(AdUnit $adUnit)
    {
        $adUnit->delete();

        return redirect()
            ->route('admin.ad-units.index')
            ->with('success', 'Ad Unit excluido com sucesso!');
    }

    /**
     * Toggle the active state of an ad unit.
     */
    public function toggle(AdUnit $adUnit)
    {
        $adUnit->update(['is_active' => !$adUnit->is_active]);

        $status = $adUnit->is_active ? 'ativado' : 'desativado';

        return redirect()
            ->route('admin.ad-units.index')
            ->with('success', "Ad Unit {$status} com sucesso!");
    }
}
