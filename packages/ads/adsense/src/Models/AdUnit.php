<?php

namespace Eduardoks98\AdsAdsense\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Eduardoks98\AdsAdsense\Enums\AdFormat;

class AdUnit extends Model
{
    protected $fillable = [
        'game_id',
        'name',
        'slot_id',
        'format',
        'position',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'format' => AdFormat::class,
    ];

    /**
     * Get the game that owns the ad unit.
     */
    public function game(): ?BelongsTo
    {
        $gameModel = config('adsense.game_model');

        if (!$gameModel || !class_exists($gameModel)) {
            return null;
        }

        return $this->belongsTo($gameModel, 'game_id');
    }

    /**
     * Scope to active ad units.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to ad units for a specific game.
     */
    public function scopeForGame(Builder $query, ?int $gameId): Builder
    {
        if ($gameId === null) {
            return $query->whereNull('game_id');
        }

        return $query->where(function ($q) use ($gameId) {
            $q->where('game_id', $gameId)
              ->orWhereNull('game_id'); // Include global ad units
        });
    }

    /**
     * Scope to ad units by position.
     */
    public function scopeAtPosition(Builder $query, string $position): Builder
    {
        return $query->where('position', $position);
    }

    /**
     * Scope to global ad units (no game association).
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('game_id');
    }

    /**
     * Get the full ad client ID.
     */
    public function getAdClientAttribute(): string
    {
        return config('adsense.publisher_id', '');
    }

    /**
     * Get the ad format enum instance.
     */
    public function getFormatEnumAttribute(): AdFormat
    {
        return AdFormat::tryFrom($this->attributes['format']) ?? AdFormat::RESPONSIVE;
    }

    /**
     * Get the style for the ad unit.
     */
    public function getStyleAttribute(): string
    {
        return $this->format_enum->style();
    }

    /**
     * Get dimensions for the ad unit.
     */
    public function getDimensionsAttribute(): array
    {
        return $this->format_enum->dimensions();
    }

    /**
     * Check if the ad unit is responsive.
     */
    public function isResponsive(): bool
    {
        return $this->format_enum->isResponsive();
    }

    /**
     * Get the HTML ins tag for the ad unit.
     */
    public function toHtml(): string
    {
        $client = $this->ad_client;
        $slot = $this->slot_id;
        $style = $this->style;
        $format = $this->isResponsive() ? 'data-ad-format="auto" data-full-width-responsive="true"' : '';

        return <<<HTML
<ins class="adsbygoogle"
     style="$style"
     data-ad-client="$client"
     data-ad-slot="$slot"
     $format></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script>
HTML;
    }

    /**
     * Get the ad unit data for API response.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slot_id' => $this->slot_id,
            'format' => $this->format->value,
            'position' => $this->position,
            'ad_client' => $this->ad_client,
            'style' => $this->style,
            'dimensions' => $this->dimensions,
            'is_responsive' => $this->isResponsive(),
        ];
    }
}
