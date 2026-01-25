<?php

namespace Eduardoks98\AdsAdsense\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Eduardoks98\AdsAdsense\Services\AdsenseReportingService;
use Eduardoks98\AdsAdsense\Models\AdUnit;

class AdsenseRevenueWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        $reportingService = app(AdsenseReportingService::class);

        if (!$reportingService->isApiConfigured()) {
            return $this->getBasicStats();
        }

        return $this->getFullStats($reportingService);
    }

    protected function getBasicStats(): array
    {
        $totalUnits = AdUnit::count();
        $activeUnits = AdUnit::active()->count();
        $globalUnits = AdUnit::global()->count();

        return [
            Stat::make('Total Ad Units', $totalUnits)
                ->description('Unidades de anúncio cadastradas')
                ->icon('heroicon-o-rectangle-stack')
                ->color('primary'),

            Stat::make('Ad Units Ativos', $activeUnits)
                ->description("{$activeUnits} de {$totalUnits} ativos")
                ->icon('heroicon-o-check-circle')
                ->color($activeUnits > 0 ? 'success' : 'warning'),

            Stat::make('Ad Units Globais', $globalUnits)
                ->description('Sem associação a jogos')
                ->icon('heroicon-o-globe-alt')
                ->color('info'),
        ];
    }

    protected function getFullStats(AdsenseReportingService $reportingService): array
    {
        $today = $reportingService->getTodayRevenue();
        $month = $reportingService->getMonthRevenue();

        $todayEarnings = $today['totals']['earnings'] ?? 0;
        $monthEarnings = $month['totals']['earnings'] ?? 0;
        $monthPageViews = $month['totals']['page_views'] ?? 0;
        $monthClicks = $month['totals']['clicks'] ?? 0;

        $activeUnits = AdUnit::active()->count();

        return [
            Stat::make('Revenue Hoje', '$' . number_format($todayEarnings, 2))
                ->description('Estimativa do dia')
                ->icon('heroicon-o-currency-dollar')
                ->color($todayEarnings > 0 ? 'success' : 'gray'),

            Stat::make('Revenue Mês', '$' . number_format($monthEarnings, 2))
                ->description(number_format($monthPageViews) . ' page views')
                ->icon('heroicon-o-chart-bar')
                ->color('success'),

            Stat::make('Cliques Mês', number_format($monthClicks))
                ->description('Total de cliques')
                ->icon('heroicon-o-cursor-arrow-rays')
                ->color('info'),

            Stat::make('Ad Units Ativos', $activeUnits)
                ->description('Unidades de anúncio')
                ->icon('heroicon-o-rectangle-stack')
                ->color('primary'),
        ];
    }

    public static function canView(): bool
    {
        return config('adsense.filament.enabled', true);
    }
}
