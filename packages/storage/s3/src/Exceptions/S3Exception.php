<?php

namespace Eduardoks98\StorageS3\Exceptions;

use Exception;

/**
 * Base exception for S3 storage operations.
 */
class S3Exception extends Exception
{
    /**
     * Create exception for upload failure.
     */
    public static function uploadFailed(string $path, ?string $reason = null): self
    {
        $message = "Failed to upload file to '{$path}'";
        if ($reason) {
            $message .= ": {$reason}";
        }

        return new self($message, 500);
    }

    /**
     * Create exception for download failure.
     */
    public static function downloadFailed(string $path, ?string $reason = null): self
    {
        $message = "Failed to download file from '{$path}'";
        if ($reason) {
            $message .= ": {$reason}";
        }

        return new self($message, 500);
    }

    /**
     * Create exception for file not found.
     */
    public static function fileNotFound(string $path): self
    {
        return new self("File not found: '{$path}'", 404);
    }

    /**
     * Create exception for invalid file type.
     */
    public static function invalidFileType(string $mimeType, string $category): self
    {
        return new self("File type '{$mimeType}' is not allowed for category '{$category}'", 422);
    }

    /**
     * Create exception for file too large.
     */
    public static function fileTooLarge(int $size, int $maxSize): self
    {
        $sizeFormatted = self::formatBytes($size);
        $maxFormatted = self::formatBytes($maxSize);

        return new self("File size ({$sizeFormatted}) exceeds maximum allowed ({$maxFormatted})", 422);
    }

    /**
     * Create exception for delete failure.
     */
    public static function deleteFailed(string $path, ?string $reason = null): self
    {
        $message = "Failed to delete file '{$path}'";
        if ($reason) {
            $message .= ": {$reason}";
        }

        return new self($message, 500);
    }

    /**
     * Create exception for signed URL failure.
     */
    public static function signedUrlFailed(string $path, ?string $reason = null): self
    {
        $message = "Failed to generate signed URL for '{$path}'";
        if ($reason) {
            $message .= ": {$reason}";
        }

        return new self($message, 500);
    }

    /**
     * Create exception for bucket not configured.
     */
    public static function bucketNotConfigured(): self
    {
        return new self('S3 bucket is not configured. Please set AWS_BUCKET in your environment.', 500);
    }

    /**
     * Create exception for invalid credentials.
     */
    public static function invalidCredentials(): self
    {
        return new self('Invalid AWS credentials. Please check your AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY.', 401);
    }

    /**
     * Format bytes to human readable string.
     */
    protected static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
