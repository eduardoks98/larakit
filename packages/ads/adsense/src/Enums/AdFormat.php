<?php

namespace Eduardoks98\AdsAdsense\Enums;

enum AdFormat: string
{
    case BANNER = 'banner';                 // 468x60
    case LEADERBOARD = 'leaderboard';       // 728x90
    case RECTANGLE = 'rectangle';           // 300x250
    case SKYSCRAPER = 'skyscraper';         // 120x600
    case LARGE_RECTANGLE = 'large_rectangle'; // 336x280
    case RESPONSIVE = 'responsive';         // Auto-size

    /**
     * Get human-readable label for the format.
     */
    public function label(): string
    {
        return match($this) {
            self::BANNER => 'Banner (468x60)',
            self::LEADERBOARD => 'Leaderboard (728x90)',
            self::RECTANGLE => 'Rectangle (300x250)',
            self::SKYSCRAPER => 'Skyscraper (120x600)',
            self::LARGE_RECTANGLE => 'Large Rectangle (336x280)',
            self::RESPONSIVE => 'Responsive (Auto)',
        };
    }

    /**
     * Get recommended dimensions for the format.
     */
    public function dimensions(): array
    {
        return match($this) {
            self::BANNER => ['width' => 468, 'height' => 60],
            self::LEADERBOARD => ['width' => 728, 'height' => 90],
            self::RECTANGLE => ['width' => 300, 'height' => 250],
            self::SKYSCRAPER => ['width' => 120, 'height' => 600],
            self::LARGE_RECTANGLE => ['width' => 336, 'height' => 280],
            self::RESPONSIVE => ['width' => 'auto', 'height' => 'auto'],
        };
    }

    /**
     * Get CSS style for the format.
     */
    public function style(): string
    {
        $dims = $this->dimensions();

        if ($this === self::RESPONSIVE) {
            return 'display:block;width:100%;height:auto;';
        }

        return "display:inline-block;width:{$dims['width']}px;height:{$dims['height']}px;";
    }

    /**
     * Check if format is responsive.
     */
    public function isResponsive(): bool
    {
        return $this === self::RESPONSIVE;
    }

    /**
     * Get all formats as options for forms.
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}
