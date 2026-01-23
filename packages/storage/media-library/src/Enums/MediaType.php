<?php

namespace Eduardoks98\MediaLibrary\Enums;

/**
 * Media type enumeration.
 */
enum MediaType: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case DOCUMENT = 'document';
    case OTHER = 'other';

    /**
     * Detect type from MIME type.
     */
    public static function fromMimeType(string $mimeType): self
    {
        $type = explode('/', $mimeType)[0] ?? '';

        return match ($type) {
            'image' => self::IMAGE,
            'video' => self::VIDEO,
            'audio' => self::AUDIO,
            'application', 'text' => self::DOCUMENT,
            default => self::OTHER,
        };
    }

    /**
     * Check if this type supports conversions.
     */
    public function supportsConversions(): bool
    {
        return $this === self::IMAGE;
    }

    /**
     * Check if this type supports thumbnails.
     */
    public function supportsThumbnails(): bool
    {
        return in_array($this, [self::IMAGE, self::VIDEO]);
    }

    /**
     * Get icon name for this type.
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::IMAGE => 'image',
            self::VIDEO => 'video',
            self::AUDIO => 'music',
            self::DOCUMENT => 'file-text',
            self::OTHER => 'file',
        };
    }
}
