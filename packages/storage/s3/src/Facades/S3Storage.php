<?php

namespace Eduardoks98\StorageS3\Facades;

use Illuminate\Support\Facades\Facade;
use Eduardoks98\StorageS3\Services\S3Service;

/**
 * @method static array upload(\Illuminate\Http\UploadedFile|string $file, ?string $path = null, ?\Eduardoks98\StorageS3\Enums\FileCategory $category = null, \Eduardoks98\StorageS3\Enums\Visibility $visibility = \Eduardoks98\StorageS3\Enums\Visibility::PRIVATE, array $options = [])
 * @method static array uploadMultiple(array $files, ?\Eduardoks98\StorageS3\Enums\FileCategory $category = null, \Eduardoks98\StorageS3\Enums\Visibility $visibility = \Eduardoks98\StorageS3\Enums\Visibility::PRIVATE)
 * @method static array download(string $path)
 * @method static string getSignedUrl(string $path, string|int $expiration = null, ?bool $useCloudFront = null)
 * @method static array getUploadUrl(string $path, string $contentType, string|int $expiration = '+30 minutes', array $conditions = [])
 * @method static bool delete(string $path)
 * @method static array deleteMultiple(array $paths)
 * @method static bool exists(string $path)
 * @method static array|null getMetadata(string $path)
 * @method static bool copy(string $source, string $destination, ?\Eduardoks98\StorageS3\Enums\Visibility $visibility = null)
 * @method static bool move(string $source, string $destination, ?\Eduardoks98\StorageS3\Enums\Visibility $visibility = null)
 * @method static array listFiles(string $prefix = '', bool $recursive = false, int $limit = 1000)
 * @method static string getUrl(string $path, ?\Eduardoks98\StorageS3\Enums\Visibility $visibility = null)
 *
 * @see \Eduardoks98\StorageS3\Services\S3Service
 */
class S3Storage extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return S3Service::class;
    }
}
