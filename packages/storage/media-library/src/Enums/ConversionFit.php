<?php

namespace Eduardoks98\MediaLibrary\Enums;

/**
 * Image conversion fit modes.
 */
enum ConversionFit: string
{
    case CROP = 'crop';
    case CONTAIN = 'contain';
    case FILL = 'fill';
    case STRETCH = 'stretch';
    case PAD = 'pad';

    /**
     * Get description for this fit mode.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::CROP => 'Crop to exact dimensions, centered',
            self::CONTAIN => 'Fit within dimensions, maintaining aspect ratio',
            self::FILL => 'Fill dimensions, cropping if necessary',
            self::STRETCH => 'Stretch to exact dimensions, ignoring aspect ratio',
            self::PAD => 'Fit within dimensions with padding',
        };
    }
}
