<?php

namespace Eduardoks98\StorageS3\Enums;

/**
 * File visibility options for S3 storage.
 */
enum Visibility: string
{
    case PRIVATE = 'private';
    case PUBLIC = 'public-read';
    case PUBLIC_READ_WRITE = 'public-read-write';
    case AUTHENTICATED_READ = 'authenticated-read';

    /**
     * Get the ACL value for S3.
     */
    public function toAcl(): string
    {
        return $this->value;
    }

    /**
     * Check if the visibility is public.
     */
    public function isPublic(): bool
    {
        return in_array($this, [self::PUBLIC, self::PUBLIC_READ_WRITE]);
    }

    /**
     * Check if the visibility is private.
     */
    public function isPrivate(): bool
    {
        return $this === self::PRIVATE;
    }

    /**
     * Get visibility from string.
     */
    public static function fromString(string $visibility): self
    {
        return match (strtolower($visibility)) {
            'public', 'public-read' => self::PUBLIC,
            'public-read-write' => self::PUBLIC_READ_WRITE,
            'authenticated-read' => self::AUTHENTICATED_READ,
            default => self::PRIVATE,
        };
    }
}
