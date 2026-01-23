<?php

namespace Eduardoks98\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Eduardoks98\MediaLibrary\Enums\MediaType;

/**
 * Media Model
 *
 * @property int $id
 * @property string $uuid
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property string $mime_type
 * @property string $disk
 * @property string $path
 * @property int $size
 * @property string $type
 * @property int|null $width
 * @property int|null $height
 * @property float|null $aspect_ratio
 * @property int|null $duration
 * @property array|null $conversions
 * @property array|null $responsive_images
 * @property array|null $custom_properties
 * @property array|null $generated_conversions
 * @property int|null $order_column
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Media extends Model
{
    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'uuid',
        'model_type',
        'model_id',
        'collection_name',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'path',
        'size',
        'type',
        'width',
        'height',
        'aspect_ratio',
        'duration',
        'conversions',
        'responsive_images',
        'custom_properties',
        'generated_conversions',
        'order_column',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'aspect_ratio' => 'float',
        'duration' => 'integer',
        'conversions' => 'array',
        'responsive_images' => 'array',
        'custom_properties' => 'array',
        'generated_conversions' => 'array',
        'order_column' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Media $media) {
            if (empty($media->uuid)) {
                $media->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the parent model.
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the media conversions.
     */
    public function mediaConversions(): HasMany
    {
        return $this->hasMany(MediaConversion::class);
    }

    /**
     * Get the media type enum.
     */
    public function getTypeEnum(): MediaType
    {
        return MediaType::from($this->type);
    }

    /**
     * Check if media is an image.
     */
    public function isImage(): bool
    {
        return $this->type === MediaType::IMAGE->value;
    }

    /**
     * Check if media is a video.
     */
    public function isVideo(): bool
    {
        return $this->type === MediaType::VIDEO->value;
    }

    /**
     * Check if media is an audio file.
     */
    public function isAudio(): bool
    {
        return $this->type === MediaType::AUDIO->value;
    }

    /**
     * Check if media is a document.
     */
    public function isDocument(): bool
    {
        return $this->type === MediaType::DOCUMENT->value;
    }

    /**
     * Get the full URL to the media file.
     */
    public function getUrl(): string
    {
        return app('s3-storage')->getUrl($this->path);
    }

    /**
     * Get the full URL to a specific conversion.
     */
    public function getConversionUrl(string $conversionName): ?string
    {
        $conversion = $this->mediaConversions()->where('conversion_name', $conversionName)->first();

        if ($conversion) {
            return app('s3-storage')->getUrl($conversion->path);
        }

        // Fallback to original if conversion doesn't exist
        return $this->getUrl();
    }

    /**
     * Get signed URL for the media file.
     */
    public function getSignedUrl(string $expiration = '+60 minutes'): string
    {
        return app('s3-storage')->getSignedUrl($this->path, $expiration);
    }

    /**
     * Get signed URL for a specific conversion.
     */
    public function getConversionSignedUrl(string $conversionName, string $expiration = '+60 minutes'): ?string
    {
        $conversion = $this->mediaConversions()->where('conversion_name', $conversionName)->first();

        if ($conversion) {
            return app('s3-storage')->getSignedUrl($conversion->path, $expiration);
        }

        return $this->getSignedUrl($expiration);
    }

    /**
     * Get the thumbnail URL.
     */
    public function getThumbnailUrl(): ?string
    {
        return $this->getConversionUrl('thumb');
    }

    /**
     * Check if a conversion exists.
     */
    public function hasConversion(string $conversionName): bool
    {
        return $this->mediaConversions()->where('conversion_name', $conversionName)->exists();
    }

    /**
     * Get all available conversions.
     */
    public function getAvailableConversions(): array
    {
        return $this->mediaConversions()->pluck('conversion_name')->toArray();
    }

    /**
     * Get a custom property.
     */
    public function getCustomProperty(string $key, mixed $default = null): mixed
    {
        return data_get($this->custom_properties, $key, $default);
    }

    /**
     * Set a custom property.
     */
    public function setCustomProperty(string $key, mixed $value): self
    {
        $properties = $this->custom_properties ?? [];
        data_set($properties, $key, $value);
        $this->custom_properties = $properties;

        return $this;
    }

    /**
     * Get human readable file size.
     */
    public function getHumanReadableSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get dimensions as string.
     */
    public function getDimensions(): ?string
    {
        if ($this->width && $this->height) {
            return "{$this->width}x{$this->height}";
        }

        return null;
    }

    /**
     * Get duration formatted.
     */
    public function getFormattedDuration(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Scope for a specific collection.
     */
    public function scopeInCollection($query, string $collectionName)
    {
        return $query->where('collection_name', $collectionName);
    }

    /**
     * Scope for a specific type.
     */
    public function scopeOfType($query, MediaType|string $type)
    {
        $typeValue = $type instanceof MediaType ? $type->value : $type;
        return $query->where('type', $typeValue);
    }

    /**
     * Scope for images.
     */
    public function scopeImages($query)
    {
        return $query->where('type', MediaType::IMAGE->value);
    }

    /**
     * Scope for videos.
     */
    public function scopeVideos($query)
    {
        return $query->where('type', MediaType::VIDEO->value);
    }

    /**
     * Scope for documents.
     */
    public function scopeDocuments($query)
    {
        return $query->where('type', MediaType::DOCUMENT->value);
    }

    /**
     * Order by position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_column');
    }
}
