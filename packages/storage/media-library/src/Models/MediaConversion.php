<?php

namespace Eduardoks98\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Media Conversion Model
 *
 * @property int $id
 * @property int $media_id
 * @property string $conversion_name
 * @property string $file_name
 * @property string $path
 * @property string $disk
 * @property string $mime_type
 * @property int $size
 * @property int|null $width
 * @property int|null $height
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class MediaConversion extends Model
{
    protected $table = 'media_conversions';

    protected $fillable = [
        'media_id',
        'conversion_name',
        'file_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'width',
        'height',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    /**
     * Get the parent media.
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * Get the full URL to this conversion.
     */
    public function getUrl(): string
    {
        return app('s3-storage')->getUrl($this->path);
    }

    /**
     * Get signed URL for this conversion.
     */
    public function getSignedUrl(string $expiration = '+60 minutes'): string
    {
        return app('s3-storage')->getSignedUrl($this->path, $expiration);
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
}
