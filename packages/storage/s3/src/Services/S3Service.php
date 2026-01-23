<?php

namespace Eduardoks98\StorageS3\Services;

use Aws\S3\S3Client;
use Aws\CloudFront\CloudFrontClient;
use Aws\Exception\AwsException;
use Eduardoks98\StorageS3\Enums\Visibility;
use Eduardoks98\StorageS3\Enums\FileCategory;
use Eduardoks98\StorageS3\Exceptions\S3Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AWS S3 Storage Service
 *
 * Provides a wrapper around AWS S3 SDK with support for:
 * - File uploads (single and multipart)
 * - File downloads
 * - Signed URLs (S3 and CloudFront)
 * - File management (copy, move, delete)
 * - CDN integration
 */
class S3Service
{
    protected S3Client $s3Client;
    protected ?CloudFrontClient $cloudFrontClient = null;
    protected string $bucket;
    protected array $config;

    public function __construct()
    {
        $this->config = config('storage-s3');
        $this->bucket = $this->config['bucket'] ?? '';

        $this->initializeClients();
    }

    /**
     * Initialize AWS clients.
     */
    protected function initializeClients(): void
    {
        $this->s3Client = app('aws')->createClient('s3');

        if ($this->config['cloudfront']['enabled'] ?? false) {
            $this->cloudFrontClient = app('aws')->createClient('CloudFront');
        }
    }

    /**
     * Upload a file to S3.
     *
     * @param UploadedFile|string $file File to upload (UploadedFile or path)
     * @param string|null $path Destination path (auto-generated if null)
     * @param FileCategory|null $category File category for organization
     * @param Visibility $visibility File visibility
     * @param array $options Additional options
     * @return array Upload result with path, url, and metadata
     * @throws S3Exception
     */
    public function upload(
        UploadedFile|string $file,
        ?string $path = null,
        ?FileCategory $category = null,
        Visibility $visibility = Visibility::PRIVATE,
        array $options = []
    ): array {
        $this->validateBucket();

        // Get file details
        if ($file instanceof UploadedFile) {
            $content = file_get_contents($file->getRealPath());
            $mimeType = $file->getMimeType();
            $originalName = $file->getClientOriginalName();
            $size = $file->getSize();
            $extension = $file->getClientOriginalExtension();
        } else {
            if (!file_exists($file)) {
                throw S3Exception::fileNotFound($file);
            }
            $content = file_get_contents($file);
            $mimeType = mime_content_type($file);
            $originalName = basename($file);
            $size = filesize($file);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
        }

        // Auto-detect category if not provided
        if ($category === null) {
            $category = FileCategory::fromMimeType($mimeType);
        }

        // Validate file type
        if (!$category->isAllowedMimeType($mimeType) && !empty($category->getAllowedMimeTypes())) {
            throw S3Exception::invalidFileType($mimeType, $category->value);
        }

        // Validate file size
        $maxSize = $category->getMaxSize();
        if ($size > $maxSize) {
            throw S3Exception::fileTooLarge($size, $maxSize);
        }

        // Generate path if not provided
        if ($path === null) {
            $path = $this->generatePath($category, $extension, $originalName);
        }

        try {
            $params = [
                'Bucket' => $this->bucket,
                'Key' => $path,
                'Body' => $content,
                'ContentType' => $mimeType,
                'ACL' => $visibility->toAcl(),
                'CacheControl' => $this->config['upload']['cache_control'] ?? 'max-age=31536000',
                'Metadata' => array_merge([
                    'original-name' => $originalName,
                    'uploaded-at' => now()->toIso8601String(),
                ], $options['metadata'] ?? []),
            ];

            // Use multipart upload for large files
            if ($size > ($this->config['upload']['multipart_threshold'] ?? 104857600)) {
                $result = $this->multipartUpload($path, $content, $params);
            } else {
                $result = $this->s3Client->putObject($params);
            }

            Log::info('S3 upload successful', [
                'path' => $path,
                'size' => $size,
                'mime_type' => $mimeType,
            ]);

            return [
                'success' => true,
                'path' => $path,
                'url' => $this->getUrl($path, $visibility),
                'size' => $size,
                'mime_type' => $mimeType,
                'original_name' => $originalName,
                'etag' => $result['ETag'] ?? null,
                'version_id' => $result['VersionId'] ?? null,
            ];
        } catch (AwsException $e) {
            Log::error('S3 upload failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            throw S3Exception::uploadFailed($path, $e->getMessage());
        }
    }

    /**
     * Upload multiple files.
     *
     * @param array $files Array of UploadedFile objects
     * @param FileCategory|null $category
     * @param Visibility $visibility
     * @return array Results for each file
     */
    public function uploadMultiple(
        array $files,
        ?FileCategory $category = null,
        Visibility $visibility = Visibility::PRIVATE
    ): array {
        $results = [];

        foreach ($files as $file) {
            try {
                $results[] = $this->upload($file, null, $category, $visibility);
            } catch (S3Exception $e) {
                $results[] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'original_name' => $file instanceof UploadedFile ? $file->getClientOriginalName() : basename($file),
                ];
            }
        }

        return $results;
    }

    /**
     * Download a file from S3.
     *
     * @param string $path S3 path
     * @return array File content and metadata
     * @throws S3Exception
     */
    public function download(string $path): array
    {
        $this->validateBucket();

        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $path,
            ]);

            return [
                'content' => (string) $result['Body'],
                'mime_type' => $result['ContentType'],
                'size' => $result['ContentLength'],
                'last_modified' => $result['LastModified'],
                'etag' => $result['ETag'],
                'metadata' => $result['Metadata'] ?? [],
            ];
        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() === 'NoSuchKey') {
                throw S3Exception::fileNotFound($path);
            }
            throw S3Exception::downloadFailed($path, $e->getMessage());
        }
    }

    /**
     * Get a signed URL for temporary access.
     *
     * @param string $path S3 path
     * @param string|int $expiration Expiration time (string like '+60 minutes' or timestamp)
     * @param bool $useCloudFront Whether to use CloudFront signed URL
     * @return string Signed URL
     * @throws S3Exception
     */
    public function getSignedUrl(
        string $path,
        string|int $expiration = null,
        ?bool $useCloudFront = null
    ): string {
        $this->validateBucket();

        $expiration = $expiration ?? $this->config['signed_urls']['expiration'] ?? '+60 minutes';
        $useCloudFront = $useCloudFront ?? ($this->config['signed_urls']['use_cloudfront'] ?? false);

        if ($useCloudFront && $this->cloudFrontClient && $this->config['cloudfront']['enabled']) {
            return $this->getCloudFrontSignedUrl($path, $expiration);
        }

        return $this->getS3SignedUrl($path, $expiration);
    }

    /**
     * Get S3 pre-signed URL.
     */
    protected function getS3SignedUrl(string $path, string|int $expiration): string
    {
        try {
            $cmd = $this->s3Client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key' => $path,
            ]);

            $request = $this->s3Client->createPresignedRequest($cmd, $expiration);

            return (string) $request->getUri();
        } catch (AwsException $e) {
            throw S3Exception::signedUrlFailed($path, $e->getMessage());
        }
    }

    /**
     * Get CloudFront signed URL.
     */
    protected function getCloudFrontSignedUrl(string $path, string|int $expiration): string
    {
        $cloudFrontUrl = rtrim($this->config['cloudfront']['url'], '/') . '/' . ltrim($path, '/');
        $expirationTime = is_string($expiration) ? strtotime($expiration) : $expiration;

        try {
            $signedUrl = $this->cloudFrontClient->getSignedUrl([
                'url' => $cloudFrontUrl,
                'expires' => $expirationTime,
                'key_pair_id' => $this->config['cloudfront']['key_pair_id'],
                'private_key' => file_get_contents($this->config['cloudfront']['private_key_path']),
            ]);

            return $signedUrl;
        } catch (\Exception $e) {
            // Fallback to S3 signed URL
            Log::warning('CloudFront signed URL failed, falling back to S3', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return $this->getS3SignedUrl($path, $expiration);
        }
    }

    /**
     * Get pre-signed URL for upload (client-side upload).
     *
     * @param string $path Destination path
     * @param string $contentType Expected content type
     * @param string|int $expiration
     * @param array $conditions Additional conditions
     * @return array Pre-signed POST data
     */
    public function getUploadUrl(
        string $path,
        string $contentType,
        string|int $expiration = '+30 minutes',
        array $conditions = []
    ): array {
        $this->validateBucket();

        $formInputs = [
            'key' => $path,
            'Content-Type' => $contentType,
            'acl' => $this->config['upload']['acl'] ?? 'private',
        ];

        $options = [
            ['bucket' => $this->bucket],
            ['starts-with', '$key', ''],
            ['starts-with', '$Content-Type', ''],
        ];

        $options = array_merge($options, $conditions);

        $expirationTime = is_string($expiration) ? date('c', strtotime($expiration)) : date('c', $expiration);

        $postObject = new \Aws\S3\PostObjectV4(
            $this->s3Client,
            $this->bucket,
            $formInputs,
            $options,
            $expirationTime
        );

        return [
            'url' => $postObject->getFormAttributes()['action'],
            'fields' => $postObject->getFormInputs(),
        ];
    }

    /**
     * Delete a file from S3.
     *
     * @param string $path
     * @return bool
     * @throws S3Exception
     */
    public function delete(string $path): bool
    {
        $this->validateBucket();

        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $path,
            ]);

            Log::info('S3 file deleted', ['path' => $path]);

            return true;
        } catch (AwsException $e) {
            throw S3Exception::deleteFailed($path, $e->getMessage());
        }
    }

    /**
     * Delete multiple files.
     *
     * @param array $paths
     * @return array Results
     */
    public function deleteMultiple(array $paths): array
    {
        $this->validateBucket();

        $objects = array_map(fn($path) => ['Key' => $path], $paths);

        try {
            $result = $this->s3Client->deleteObjects([
                'Bucket' => $this->bucket,
                'Delete' => [
                    'Objects' => $objects,
                ],
            ]);

            return [
                'deleted' => $result['Deleted'] ?? [],
                'errors' => $result['Errors'] ?? [],
            ];
        } catch (AwsException $e) {
            throw S3Exception::deleteFailed(implode(', ', $paths), $e->getMessage());
        }
    }

    /**
     * Check if a file exists.
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        try {
            $this->s3Client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $path,
            ]);
            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    /**
     * Get file metadata.
     *
     * @param string $path
     * @return array|null
     */
    public function getMetadata(string $path): ?array
    {
        try {
            $result = $this->s3Client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $path,
            ]);

            return [
                'size' => $result['ContentLength'],
                'mime_type' => $result['ContentType'],
                'last_modified' => $result['LastModified'],
                'etag' => $result['ETag'],
                'metadata' => $result['Metadata'] ?? [],
            ];
        } catch (AwsException $e) {
            return null;
        }
    }

    /**
     * Copy a file to a new location.
     *
     * @param string $source
     * @param string $destination
     * @param Visibility|null $visibility
     * @return bool
     */
    public function copy(string $source, string $destination, ?Visibility $visibility = null): bool
    {
        $this->validateBucket();

        try {
            $params = [
                'Bucket' => $this->bucket,
                'CopySource' => $this->bucket . '/' . $source,
                'Key' => $destination,
            ];

            if ($visibility) {
                $params['ACL'] = $visibility->toAcl();
            }

            $this->s3Client->copyObject($params);

            return true;
        } catch (AwsException $e) {
            Log::error('S3 copy failed', [
                'source' => $source,
                'destination' => $destination,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Move a file to a new location.
     *
     * @param string $source
     * @param string $destination
     * @param Visibility|null $visibility
     * @return bool
     */
    public function move(string $source, string $destination, ?Visibility $visibility = null): bool
    {
        if ($this->copy($source, $destination, $visibility)) {
            return $this->delete($source);
        }
        return false;
    }

    /**
     * List files in a directory.
     *
     * @param string $prefix Directory prefix
     * @param bool $recursive Include subdirectories
     * @param int $limit Maximum number of files
     * @return array
     */
    public function listFiles(string $prefix = '', bool $recursive = false, int $limit = 1000): array
    {
        $this->validateBucket();

        $params = [
            'Bucket' => $this->bucket,
            'Prefix' => $prefix,
            'MaxKeys' => $limit,
        ];

        if (!$recursive) {
            $params['Delimiter'] = '/';
        }

        try {
            $result = $this->s3Client->listObjectsV2($params);

            $files = [];
            foreach ($result['Contents'] ?? [] as $object) {
                $files[] = [
                    'path' => $object['Key'],
                    'size' => $object['Size'],
                    'last_modified' => $object['LastModified'],
                    'etag' => $object['ETag'],
                ];
            }

            return [
                'files' => $files,
                'directories' => array_map(
                    fn($p) => rtrim($p['Prefix'], '/'),
                    $result['CommonPrefixes'] ?? []
                ),
                'truncated' => $result['IsTruncated'] ?? false,
            ];
        } catch (AwsException $e) {
            return ['files' => [], 'directories' => [], 'truncated' => false];
        }
    }

    /**
     * Get the public URL for a file.
     */
    public function getUrl(string $path, ?Visibility $visibility = null): string
    {
        if ($this->config['cloudfront']['enabled'] && $this->config['cloudfront']['url']) {
            return rtrim($this->config['cloudfront']['url'], '/') . '/' . ltrim($path, '/');
        }

        return $this->s3Client->getObjectUrl($this->bucket, $path);
    }

    /**
     * Generate a unique path for upload.
     */
    protected function generatePath(FileCategory $category, string $extension, string $originalName = ''): string
    {
        $prefix = $category->getPath();
        $date = now()->format('Y/m/d');
        $uuid = Str::uuid();
        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));

        if ($safeName) {
            return "{$prefix}/{$date}/{$uuid}-{$safeName}.{$extension}";
        }

        return "{$prefix}/{$date}/{$uuid}.{$extension}";
    }

    /**
     * Multipart upload for large files.
     */
    protected function multipartUpload(string $path, string $content, array $params): array
    {
        $uploader = new \Aws\S3\MultipartUploader($this->s3Client, $content, [
            'bucket' => $this->bucket,
            'key' => $path,
            'acl' => $params['ACL'] ?? 'private',
            'ContentType' => $params['ContentType'],
        ]);

        return $uploader->upload();
    }

    /**
     * Validate that bucket is configured.
     */
    protected function validateBucket(): void
    {
        if (empty($this->bucket)) {
            throw S3Exception::bucketNotConfigured();
        }
    }
}
