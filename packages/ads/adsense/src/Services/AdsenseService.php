<?php

namespace Eduardoks98\AdsAdsense\Services;

use Eduardoks98\AdsAdsense\Models\AdUnit;
use Eduardoks98\AdsAdsense\Enums\AdFormat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class AdsenseService
{
    /**
     * Check if AdSense is enabled.
     */
    public function isEnabled(): bool
    {
        return config('adsense.enabled', true);
    }

    /**
     * Check if AdSense is configured.
     */
    public function isConfigured(): bool
    {
        return !empty(config('adsense.publisher_id'));
    }

    /**
     * Get the publisher ID.
     */
    public function getPublisherId(): ?string
    {
        return config('adsense.publisher_id');
    }

    /**
     * Check if test mode is enabled.
     */
    public function isTestMode(): bool
    {
        return config('adsense.defaults.test_mode', false);
    }

    /**
     * Get active ad units for a game.
     */
    public function getAdUnitsForGame(?int $gameId = null): Collection
    {
        $cacheKey = $this->getCacheKey("game_{$gameId}_units");

        if ($this->isCacheEnabled()) {
            return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($gameId) {
                return $this->fetchAdUnitsForGame($gameId);
            });
        }

        return $this->fetchAdUnitsForGame($gameId);
    }

    /**
     * Fetch ad units for a game from database.
     */
    protected function fetchAdUnitsForGame(?int $gameId): Collection
    {
        return AdUnit::active()
            ->forGame($gameId)
            ->orderBy('position')
            ->get();
    }

    /**
     * Get ad unit by position.
     */
    public function getAdUnitByPosition(string $position, ?int $gameId = null): ?AdUnit
    {
        $cacheKey = $this->getCacheKey("game_{$gameId}_position_{$position}");

        if ($this->isCacheEnabled()) {
            return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($position, $gameId) {
                return $this->fetchAdUnitByPosition($position, $gameId);
            });
        }

        return $this->fetchAdUnitByPosition($position, $gameId);
    }

    /**
     * Fetch ad unit by position from database.
     */
    protected function fetchAdUnitByPosition(string $position, ?int $gameId): ?AdUnit
    {
        return AdUnit::active()
            ->forGame($gameId)
            ->atPosition($position)
            ->first();
    }

    /**
     * Get all active ad units.
     */
    public function getAllActiveAdUnits(): Collection
    {
        $cacheKey = $this->getCacheKey('all_active_units');

        if ($this->isCacheEnabled()) {
            return Cache::remember($cacheKey, $this->getCacheTtl(), function () {
                return AdUnit::active()->orderBy('game_id')->orderBy('position')->get();
            });
        }

        return AdUnit::active()->orderBy('game_id')->orderBy('position')->get();
    }

    /**
     * Get global ad units (not associated with any game).
     */
    public function getGlobalAdUnits(): Collection
    {
        $cacheKey = $this->getCacheKey('global_units');

        if ($this->isCacheEnabled()) {
            return Cache::remember($cacheKey, $this->getCacheTtl(), function () {
                return AdUnit::active()->global()->orderBy('position')->get();
            });
        }

        return AdUnit::active()->global()->orderBy('position')->get();
    }

    /**
     * Get ad units grouped by position.
     */
    public function getAdUnitsGroupedByPosition(?int $gameId = null): array
    {
        $units = $this->getAdUnitsForGame($gameId);

        return $units->groupBy('position')->toArray();
    }

    /**
     * Create a new ad unit.
     */
    public function createAdUnit(array $data): AdUnit
    {
        $adUnit = AdUnit::create($data);
        $this->clearCache();

        return $adUnit;
    }

    /**
     * Update an ad unit.
     */
    public function updateAdUnit(AdUnit $adUnit, array $data): AdUnit
    {
        $adUnit->update($data);
        $this->clearCache();

        return $adUnit;
    }

    /**
     * Delete an ad unit.
     */
    public function deleteAdUnit(AdUnit $adUnit): bool
    {
        $result = $adUnit->delete();
        $this->clearCache();

        return $result;
    }

    /**
     * Toggle ad unit active status.
     */
    public function toggleAdUnit(AdUnit $adUnit): AdUnit
    {
        $adUnit->update(['is_active' => !$adUnit->is_active]);
        $this->clearCache();

        return $adUnit;
    }

    /**
     * Get the AdSense script tag.
     */
    public function getScriptTag(): string
    {
        $publisherId = $this->getPublisherId();

        if (!$publisherId) {
            return '';
        }

        return <<<HTML
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={$publisherId}"
     crossorigin="anonymous"></script>
HTML;
    }

    /**
     * Get ad unit HTML for a position.
     */
    public function renderAdUnit(string $position, ?int $gameId = null): string
    {
        if (!$this->isEnabled() || !$this->isConfigured()) {
            return '';
        }

        $adUnit = $this->getAdUnitByPosition($position, $gameId);

        if (!$adUnit) {
            return '';
        }

        return $adUnit->toHtml();
    }

    /**
     * Check if cache is enabled.
     */
    protected function isCacheEnabled(): bool
    {
        return config('adsense.cache.enabled', true);
    }

    /**
     * Get cache TTL.
     */
    protected function getCacheTtl(): int
    {
        return config('adsense.cache.ttl', 300);
    }

    /**
     * Get cache key with prefix.
     */
    protected function getCacheKey(string $key): string
    {
        $prefix = config('adsense.cache.prefix', 'adsense_');
        return $prefix . $key;
    }

    /**
     * Clear all cached data.
     */
    public function clearCache(): void
    {
        if ($this->isCacheEnabled()) {
            $prefix = config('adsense.cache.prefix', 'adsense_');

            // Clear known cache keys
            Cache::forget($this->getCacheKey('all_active_units'));
            Cache::forget($this->getCacheKey('global_units'));

            // Clear game-specific caches
            $games = AdUnit::distinct()->pluck('game_id');
            foreach ($games as $gameId) {
                Cache::forget($this->getCacheKey("game_{$gameId}_units"));

                $positions = AdUnit::where('game_id', $gameId)->distinct()->pluck('position');
                foreach ($positions as $position) {
                    Cache::forget($this->getCacheKey("game_{$gameId}_position_{$position}"));
                }
            }
        }
    }
}
