<?php

namespace Eduardoks98\AdsAdsense\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Eduardoks98\AdsAdsense\Services\AdsenseService;
use Eduardoks98\AdsAdsense\Services\AdsenseReportingService;
use Carbon\Carbon;

class AdUnitController extends Controller
{
    public function __construct(
        protected AdsenseService $adsenseService,
        protected AdsenseReportingService $reportingService
    ) {}

    /**
     * Get ad units for a game.
     *
     * GET /api/ads/units?game={game_id}
     */
    public function index(Request $request): JsonResponse
    {
        if (!$this->adsenseService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'AdSense is disabled',
                'data' => [],
            ]);
        }

        $gameId = $request->input('game');
        $position = $request->input('position');

        if ($position) {
            $adUnit = $this->adsenseService->getAdUnitByPosition($position, $gameId);

            if (!$adUnit) {
                return response()->json([
                    'success' => false,
                    'message' => 'No ad unit found for this position',
                    'data' => null,
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $adUnit->toApiArray(),
            ]);
        }

        $adUnits = $this->adsenseService->getAdUnitsForGame($gameId);

        return response()->json([
            'success' => true,
            'data' => $adUnits->map(fn ($unit) => $unit->toApiArray())->values(),
            'publisher_id' => $this->adsenseService->getPublisherId(),
        ]);
    }

    /**
     * Get ad units grouped by position.
     *
     * GET /api/ads/units/grouped?game={game_id}
     */
    public function grouped(Request $request): JsonResponse
    {
        if (!$this->adsenseService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'AdSense is disabled',
                'data' => [],
            ]);
        }

        $gameId = $request->input('game');
        $grouped = $this->adsenseService->getAdUnitsGroupedByPosition($gameId);

        return response()->json([
            'success' => true,
            'data' => $grouped,
            'publisher_id' => $this->adsenseService->getPublisherId(),
        ]);
    }

    /**
     * Get revenue report (requires authentication).
     *
     * GET /api/ads/revenue?start={date}&end={date}
     */
    public function revenue(Request $request): JsonResponse
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        $startDate = Carbon::parse($request->input('start'));
        $endDate = Carbon::parse($request->input('end'));

        if (!$this->reportingService->isApiConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'AdSense API is not configured',
                'data' => null,
            ], 503);
        }

        $report = $this->reportingService->getRevenueReport($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $report,
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ]);
    }

    /**
     * Get revenue summary for dashboard.
     *
     * GET /api/ads/revenue/summary
     */
    public function revenueSummary(): JsonResponse
    {
        if (!$this->reportingService->isApiConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'AdSense API is not configured',
                'data' => null,
            ], 503);
        }

        $today = $this->reportingService->getTodayRevenue();
        $week = $this->reportingService->getWeekRevenue();
        $month = $this->reportingService->getMonthRevenue();

        return response()->json([
            'success' => true,
            'data' => [
                'today' => $today['totals'] ?? [],
                'week' => $week['totals'] ?? [],
                'month' => $month['totals'] ?? [],
            ],
        ]);
    }

    /**
     * Get configuration info.
     *
     * GET /api/ads/config
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => $this->adsenseService->isEnabled(),
                'configured' => $this->adsenseService->isConfigured(),
                'publisher_id' => $this->adsenseService->getPublisherId(),
                'test_mode' => $this->adsenseService->isTestMode(),
                'api_enabled' => $this->reportingService->isApiEnabled(),
                'api_configured' => $this->reportingService->isApiConfigured(),
            ],
        ]);
    }
}
