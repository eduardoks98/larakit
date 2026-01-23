<?php

namespace Eduardoks98\MediaLibrary\Services;

use Eduardoks98\MediaLibrary\Models\Media;
use Eduardoks98\MediaLibrary\Models\MediaConversion;
use Eduardoks98\MediaLibrary\Enums\MediaType;
use Eduardoks98\MediaLibrary\Enums\ConversionFit;
use Eduardoks98\MediaLibrary\Jobs\GenerateConversionsJob;
use Eduardoks98\StorageS3\Services\S3Service;
use Eduardoks98\StorageS3\Enums\Visibility;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Media Library Service
 *
 * Main service for managing media files with automatic
 * conversions, thumbnails, and organization.
 */
class MediaService
{
    protected S3Service $storage;
    protected ImageService $imageService;
    protected array $config;

    public function __construct(S3Service $storage, ImageService $imageService)
    {
        $this->storage = $storage;
        $this->imageService = $imageService;
        $this->config = config('media-library');
    }

    /**
     * Add media to a model.
     *
     * @param UploadedFile|string $file File to upload
     * @param Model|null $model Model to attach media to
     * @param string $collection Collection name
     * @param array $options Additional options
     * @return Media
     */
    public function add(
        UploadedFile|string $file,
        ?Model $model = null,
        string $collection = 'default',
        array $options = []
    ): Media {
        // Validate collection settings
        $collectionConfig = $this->getCollectionConfig($collection);

        // Get file details
        if ($file instanceof UploadedFile) {
            $content = file_get_contents($file->getRealPath());
            $mimeType = $file->getMimeType();
            $originalName = $file->getClientOriginalName();
            $size = $file->getSize();
            $extension = $file->getClientOriginalExtension();
        } else {
            $content = file_get_contents($file);
            $mimeType = mime_content_type($file);
            $originalName = basename($file);
            $size = filesize($file);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
        }

        // Validate file size
        $maxSize = $collectionConfig['max_file_size'] ?? 10 * 1024 * 1024;
        if ($size > $maxSize) {
            throw new \InvalidArgumentException("File exceeds maximum size for collection '{$collection}'");
        }

        // Determine media type
        $mediaType = MediaType::fromMimeType($mimeType);

        // Generate path
        $uuid = Str::uuid();
        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $path = "media/{$collection}/" . now()->format('Y/m/d') . "/{$uuid}-{$safeName}.{$extension}";

        // Upload to storage
        $uploadResult = $this->storage->upload(
            $file,
            $path,
            null,
            Visibility::fromString($options['visibility'] ?? 'private')
        );

        // Get dimensions for images/videos
        $dimensions = $this->getDimensions($file, $mediaType);

        // Handle single file collections
        if ($model && ($collectionConfig['single'] ?? false)) {
            $this->clearCollection($model, $collection);
        }

        // Create media record
        $media = Media::create([
            'uuid' => $uuid,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'collection_name' => $collection,
            'name' => $options['name'] ?? pathinfo($originalName, PATHINFO_FILENAME),
            'file_name' => $originalName,
            'mime_type' => $mimeType,
            'disk' => $this->config['disk'] ?? 's3',
            'path' => $path,
            'size' => $size,
            'type' => $mediaType->value,
            'width' => $dimensions['width'] ?? null,
            'height' => $dimensions['height'] ?? null,
            'aspect_ratio' => $dimensions['aspect_ratio'] ?? null,
            'custom_properties' => $options['custom_properties'] ?? [],
            'order_column' => $options['order'] ?? Media::where('collection_name', $collection)->max('order_column') + 1,
        ]);

        // Generate conversions
        if ($mediaType->supportsConversions()) {
            $conversions = $collectionConfig['conversions'] ?? ['thumb', 'medium'];
            $this->generateConversions($media, $conversions, $file);
        }

        Log::info('Media added', [
            'media_id' => $media->id,
            'collection' => $collection,
            'type' => $mediaType->value,
        ]);

        return $media;
    }

    /**
     * Add multiple files.
     */
    public function addMultiple(
        array $files,
        ?Model $model = null,
        string $collection = 'default',
        array $options = []
    ): array {
        $results = [];

        foreach ($files as $index => $file) {
            try {
                $mediaOptions = $options;
                $mediaOptions['order'] = ($options['order'] ?? 0) + $index;

                $results[] = $this->add($file, $model, $collection, $mediaOptions);
            } catch (\Exception $e) {
                Log::error('Failed to add media', [
                    'error' => $e->getMessage(),
                    'file' => $file instanceof UploadedFile ? $file->getClientOriginalName() : $file,
                ]);
                $results[] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Generate conversions for a media item.
     */
    public function generateConversions(
        Media $media,
        array $conversionNames,
        UploadedFile|string|null $originalFile = null
    ): void {
        if (!$media->isImage()) {
            return;
        }

        // Queue or process immediately
        if ($this->config['queue']['enabled'] ?? true) {
            dispatch(new GenerateConversionsJob($media, $conversionNames, $originalFile))
                ->onQueue($this->config['queue']['queue'] ?? 'media');
        } else {
            $this->processConversions($media, $conversionNames, $originalFile);
        }
    }

    /**
     * Process conversions immediately.
     */
    public function processConversions(
        Media $media,
        array $conversionNames,
        UploadedFile|string|null $originalFile = null
    ): void {
        // Get original file content
        if ($originalFile) {
            $content = $originalFile instanceof UploadedFile
                ? file_get_contents($originalFile->getRealPath())
                : file_get_contents($originalFile);
        } else {
            $downloaded = $this->storage->download($media->path);
            $content = $downloaded['content'];
        }

        $conversionConfigs = $this->config['conversions'] ?? [];
        $defaultFormat = $this->config['formats']['default'] ?? 'webp';
        $generatedConversions = [];

        foreach ($conversionNames as $conversionName) {
            if (!isset($conversionConfigs[$conversionName])) {
                continue;
            }

            $conversionConfig = $conversionConfigs[$conversionName];

            try {
                // Resize image
                $image = $this->imageService->resize(
                    $content,
                    $conversionConfig['width'] ?? null,
                    $conversionConfig['height'] ?? null,
                    ConversionFit::from($conversionConfig['fit'] ?? 'contain'),
                    $conversionConfig['quality'] ?? 85
                );

                // Apply watermark if configured
                if ($conversionConfig['watermark'] ?? false) {
                    $watermarkConfig = $this->config['watermark'];
                    if ($watermarkConfig['enabled'] && $watermarkConfig['path']) {
                        $image = $this->imageService->watermark(
                            $image,
                            $watermarkConfig['path'],
                            $watermarkConfig['position'] ?? 'bottom-right',
                            $watermarkConfig['opacity'] ?? 50,
                            $watermarkConfig['padding'] ?? 10
                        );
                    }
                }

                // Encode
                $format = $conversionConfig['format'] ?? $defaultFormat;
                $quality = $conversionConfig['quality'] ?? 85;
                $encodedContent = $this->imageService->encode($image, $format, $quality);

                // Get dimensions
                $dimensions = $this->imageService->getDimensions($image);

                // Generate path
                $basePath = pathinfo($media->path, PATHINFO_DIRNAME);
                $baseName = pathinfo($media->path, PATHINFO_FILENAME);
                $conversionPath = "{$basePath}/conversions/{$baseName}-{$conversionName}.{$format}";

                // Upload conversion
                $tempFile = tempnam(sys_get_temp_dir(), 'media_conversion_');
                file_put_contents($tempFile, $encodedContent);

                $this->storage->upload(
                    $tempFile,
                    $conversionPath,
                    null,
                    Visibility::PRIVATE
                );

                unlink($tempFile);

                // Save conversion record
                MediaConversion::updateOrCreate(
                    [
                        'media_id' => $media->id,
                        'conversion_name' => $conversionName,
                    ],
                    [
                        'file_name' => "{$baseName}-{$conversionName}.{$format}",
                        'path' => $conversionPath,
                        'disk' => $media->disk,
                        'mime_type' => $this->imageService->getMimeType($format),
                        'size' => strlen($encodedContent),
                        'width' => $dimensions['width'],
                        'height' => $dimensions['height'],
                    ]
                );

                $generatedConversions[] = $conversionName;

                Log::info('Conversion generated', [
                    'media_id' => $media->id,
                    'conversion' => $conversionName,
                    'dimensions' => $dimensions,
                ]);
            } catch (\Exception $e) {
                Log::error('Conversion failed', [
                    'media_id' => $media->id,
                    'conversion' => $conversionName,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update media record
        $media->update([
            'generated_conversions' => $generatedConversions,
        ]);
    }

    /**
     * Delete a media item.
     */
    public function delete(Media $media): bool
    {
        try {
            // Delete conversions from storage
            if ($this->config['cleanup']['delete_conversions_on_delete'] ?? true) {
                foreach ($media->mediaConversions as $conversion) {
                    $this->storage->delete($conversion->path);
                }
            }

            // Delete original from storage
            if ($this->config['cleanup']['delete_original_on_delete'] ?? true) {
                $this->storage->delete($media->path);
            }

            // Delete database records
            $media->mediaConversions()->delete();
            $media->delete();

            Log::info('Media deleted', ['media_id' => $media->id]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete media', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clear all media from a collection.
     */
    public function clearCollection(Model $model, string $collection): int
    {
        $media = Media::where('model_type', get_class($model))
            ->where('model_id', $model->getKey())
            ->where('collection_name', $collection)
            ->get();

        $count = 0;
        foreach ($media as $item) {
            if ($this->delete($item)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get media for a model.
     */
    public function getMedia(Model $model, ?string $collection = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Media::where('model_type', get_class($model))
            ->where('model_id', $model->getKey());

        if ($collection) {
            $query->where('collection_name', $collection);
        }

        return $query->ordered()->get();
    }

    /**
     * Get first media item.
     */
    public function getFirstMedia(Model $model, string $collection = 'default'): ?Media
    {
        return Media::where('model_type', get_class($model))
            ->where('model_id', $model->getKey())
            ->where('collection_name', $collection)
            ->ordered()
            ->first();
    }

    /**
     * Reorder media items.
     */
    public function reorder(array $mediaIds): void
    {
        foreach ($mediaIds as $order => $id) {
            Media::where('id', $id)->update(['order_column' => $order + 1]);
        }
    }

    /**
     * Get collection configuration.
     */
    protected function getCollectionConfig(string $collection): array
    {
        return $this->config['collections'][$collection] ?? $this->config['collections']['default'] ?? [];
    }

    /**
     * Get image/video dimensions.
     */
    protected function getDimensions(UploadedFile|string $file, MediaType $type): array
    {
        if ($type !== MediaType::IMAGE) {
            return [];
        }

        try {
            $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
            return $this->imageService->getDimensions($path);
        } catch (\Exception $e) {
            return [];
        }
    }
}
