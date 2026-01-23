<?php

namespace Eduardoks98\MediaLibrary\Traits;

use Eduardoks98\MediaLibrary\Models\Media;
use Eduardoks98\MediaLibrary\Services\MediaService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;

/**
 * Trait for models that have media attachments.
 *
 * Add this trait to any Eloquent model to enable media management.
 *
 * @method \Illuminate\Database\Eloquent\Relations\MorphMany morphMany(string $related, string $name, string $type = null, string $id = null, string $localKey = null)
 */
trait HasMedia
{
    /**
     * Boot the trait.
     */
    public static function bootHasMedia(): void
    {
        // Delete all media when model is deleted
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                return; // Don't delete media on soft delete
            }

            $model->clearMediaCollection();
        });
    }

    /**
     * Get all media for this model.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model')->ordered();
    }

    /**
     * Add media to a collection.
     *
     * @param UploadedFile|string $file
     * @param string $collection
     * @param array $options
     * @return Media
     */
    public function addMedia(
        UploadedFile|string $file,
        string $collection = 'default',
        array $options = []
    ): Media {
        return app(MediaService::class)->add($file, $this, $collection, $options);
    }

    /**
     * Add multiple media files.
     *
     * @param array $files
     * @param string $collection
     * @param array $options
     * @return array
     */
    public function addMediaMultiple(
        array $files,
        string $collection = 'default',
        array $options = []
    ): array {
        return app(MediaService::class)->addMultiple($files, $this, $collection, $options);
    }

    /**
     * Get media from a collection.
     */
    public function getMedia(string $collection = 'default'): \Illuminate\Database\Eloquent\Collection
    {
        return $this->media()->where('collection_name', $collection)->get();
    }

    /**
     * Get first media from a collection.
     */
    public function getFirstMedia(string $collection = 'default'): ?Media
    {
        return $this->media()->where('collection_name', $collection)->first();
    }

    /**
     * Get first media URL.
     */
    public function getFirstMediaUrl(string $collection = 'default', string $conversion = ''): ?string
    {
        $media = $this->getFirstMedia($collection);

        if (!$media) {
            return null;
        }

        if ($conversion) {
            return $media->getConversionUrl($conversion);
        }

        return $media->getUrl();
    }

    /**
     * Check if model has media in collection.
     */
    public function hasMedia(string $collection = 'default'): bool
    {
        return $this->media()->where('collection_name', $collection)->exists();
    }

    /**
     * Clear media from a collection.
     */
    public function clearMediaCollection(string $collection = null): int
    {
        if ($collection === null) {
            // Clear all collections
            $count = 0;
            foreach ($this->media as $media) {
                if (app(MediaService::class)->delete($media)) {
                    $count++;
                }
            }
            return $count;
        }

        return app(MediaService::class)->clearCollection($this, $collection);
    }

    /**
     * Update media order.
     */
    public function updateMediaOrder(array $mediaIds): void
    {
        app(MediaService::class)->reorder($mediaIds);
    }

    /**
     * Get avatar URL (convenience method for avatars collection).
     */
    public function getAvatarUrl(string $conversion = 'thumb'): ?string
    {
        return $this->getFirstMediaUrl('avatars', $conversion);
    }

    /**
     * Set avatar (convenience method).
     */
    public function setAvatar(UploadedFile|string $file): Media
    {
        return $this->addMedia($file, 'avatars');
    }
}
