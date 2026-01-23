<?php

namespace Eduardoks98\StorageS3\Enums;

/**
 * File categories for organizing uploads.
 */
enum FileCategory: string
{
    case IMAGES = 'images';
    case DOCUMENTS = 'documents';
    case VIDEOS = 'videos';
    case AUDIO = 'audio';
    case TEMP = 'temp';
    case OTHER = 'other';

    /**
     * Get the path prefix for this category.
     */
    public function getPath(): string
    {
        return config("storage-s3.paths.{$this->value}", $this->value);
    }

    /**
     * Get allowed MIME types for this category.
     */
    public function getAllowedMimeTypes(): array
    {
        return config("storage-s3.allowed_types.{$this->value}", []);
    }

    /**
     * Get maximum file size for this category.
     */
    public function getMaxSize(): int
    {
        return config("storage-s3.max_size.{$this->value}", config('storage-s3.max_size.default', 104857600));
    }

    /**
     * Check if a MIME type is allowed for this category.
     */
    public function isAllowedMimeType(string $mimeType): bool
    {
        $allowed = $this->getAllowedMimeTypes();

        if (empty($allowed)) {
            return true; // No restrictions
        }

        return in_array($mimeType, $allowed);
    }

    /**
     * Detect category from MIME type.
     */
    public static function fromMimeType(string $mimeType): self
    {
        $type = explode('/', $mimeType)[0] ?? '';

        return match ($type) {
            'image' => self::IMAGES,
            'video' => self::VIDEOS,
            'audio' => self::AUDIO,
            'application', 'text' => self::DOCUMENTS,
            default => self::OTHER,
        };
    }

    /**
     * Detect category from file extension.
     */
    public static function fromExtension(string $extension): self
    {
        $extension = strtolower($extension);

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg', 'bmp', 'ico'];
        $videoExtensions = ['mp4', 'mpeg', 'mov', 'webm', 'avi', 'mkv', 'wmv'];
        $audioExtensions = ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a'];
        $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf'];

        return match (true) {
            in_array($extension, $imageExtensions) => self::IMAGES,
            in_array($extension, $videoExtensions) => self::VIDEOS,
            in_array($extension, $audioExtensions) => self::AUDIO,
            in_array($extension, $documentExtensions) => self::DOCUMENTS,
            default => self::OTHER,
        };
    }
}
