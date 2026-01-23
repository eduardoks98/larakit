# Media Library Package

> Media management for Laravel with image processing, automatic conversions, and file organization.

## Features

- Automatic image conversions (thumbnails, responsive images)
- Multiple image formats (WebP, AVIF, JPEG, PNG)
- Watermarking support
- Image manipulation (resize, crop, rotate, filters)
- Collection-based organization
- Polymorphic relationships
- Background processing via queues
- Video thumbnail generation (with FFmpeg)
- S3 storage integration

## Installation

```bash
composer require eduardoks98/media-library
```

## Configuration

### Publish Config & Migrations

```bash
php artisan vendor:publish --provider="Eduardoks98\MediaLibrary\MediaLibraryServiceProvider" --tag="config"
php artisan vendor:publish --provider="Eduardoks98\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"
php artisan migrate
```

### Environment Variables

```env
# Storage
MEDIA_LIBRARY_DISK=s3

# Image Processing
MEDIA_LIBRARY_IMAGE_DRIVER=gd
MEDIA_LIBRARY_DEFAULT_FORMAT=webp
MEDIA_LIBRARY_PRESERVE_ORIGINAL=true
MEDIA_LIBRARY_OPTIMIZE=true
MEDIA_LIBRARY_STRIP_METADATA=true

# Watermark
MEDIA_LIBRARY_WATERMARK_ENABLED=false
MEDIA_LIBRARY_WATERMARK_PATH=/path/to/watermark.png
MEDIA_LIBRARY_WATERMARK_POSITION=bottom-right
MEDIA_LIBRARY_WATERMARK_OPACITY=50

# Queue
MEDIA_LIBRARY_QUEUE_ENABLED=true
MEDIA_LIBRARY_QUEUE_NAME=media

# Video (requires FFmpeg)
FFMPEG_PATH=/usr/bin/ffmpeg
FFPROBE_PATH=/usr/bin/ffprobe
```

## Usage

### Add HasMedia Trait to Model

```php
use Eduardoks98\MediaLibrary\Traits\HasMedia;

class User extends Model
{
    use HasMedia;
}

class Product extends Model
{
    use HasMedia;
}
```

### Upload Media

```php
// Single file
$media = $user->addMedia($request->file('avatar'), 'avatars');

// Multiple files
$mediaItems = $product->addMediaMultiple(
    $request->file('images'),
    'gallery'
);

// With options
$media = $product->addMedia($file, 'images', [
    'name' => 'Product Photo',
    'custom_properties' => [
        'alt' => 'Product main image',
        'caption' => 'Beautiful product',
    ],
]);
```

### Retrieve Media

```php
// Get all media in collection
$images = $product->getMedia('gallery');

// Get first media
$avatar = $user->getFirstMedia('avatars');

// Get media URL
$url = $user->getFirstMediaUrl('avatars');

// Get specific conversion URL
$thumbUrl = $user->getFirstMediaUrl('avatars', 'thumb');

// Check if has media
if ($product->hasMedia('gallery')) {
    // ...
}
```

### Media URLs

```php
$media = $product->getFirstMedia('images');

// Original file URL
$url = $media->getUrl();

// Conversion URL
$thumbUrl = $media->getConversionUrl('thumb');
$mediumUrl = $media->getConversionUrl('medium');

// Signed URL (for private files)
$signedUrl = $media->getSignedUrl('+1 hour');
$signedThumbUrl = $media->getConversionSignedUrl('thumb', '+1 hour');
```

### Delete Media

```php
// Delete single media
$media->delete();

// Clear collection
$user->clearMediaCollection('avatars');

// Clear all media
$user->clearMediaCollection();
```

### Reorder Media

```php
// Update order
$product->updateMediaOrder([3, 1, 2]); // Media IDs in desired order
```

## Collections

Define collections in config to control behavior:

```php
// config/media-library.php
'collections' => [
    'avatars' => [
        'disk' => 's3',
        'conversions' => ['thumb', 'small'],
        'max_file_size' => 2 * 1024 * 1024, // 2MB
        'accepted_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'single' => true, // Only one file per model
    ],
    'gallery' => [
        'conversions' => ['thumb', 'medium', 'large'],
        'max_file_size' => 20 * 1024 * 1024, // 20MB
        'accepted_mimes' => ['image/*'],
    ],
    'documents' => [
        'conversions' => [], // No conversions for documents
        'max_file_size' => 50 * 1024 * 1024, // 50MB
        'accepted_mimes' => ['application/pdf', 'application/msword'],
    ],
],
```

## Image Conversions

Default conversions (configurable):

| Name | Dimensions | Fit | Quality |
|------|------------|-----|---------|
| `thumb` | 150x150 | crop | 80% |
| `small` | 320xauto | contain | 85% |
| `medium` | 640xauto | contain | 85% |
| `large` | 1024xauto | contain | 85% |
| `xl` | 1920xauto | contain | 90% |

### Custom Conversions

```php
// config/media-library.php
'conversions' => [
    'thumb' => [
        'width' => 150,
        'height' => 150,
        'fit' => 'crop',
        'quality' => 80,
        'format' => 'webp',
    ],
    'hero' => [
        'width' => 1920,
        'height' => 1080,
        'fit' => 'fill',
        'quality' => 90,
        'watermark' => true,
    ],
],
```

### Fit Modes

| Mode | Description |
|------|-------------|
| `crop` | Exact dimensions, centered crop |
| `contain` | Fit within dimensions, maintain ratio |
| `fill` | Cover dimensions, crop overflow |
| `stretch` | Exact dimensions, ignore ratio |
| `pad` | Fit within dimensions with padding |

## Image Manipulation Service

Use `ImageService` for advanced manipulation:

```php
use Eduardoks98\MediaLibrary\Services\ImageService;

$imageService = app(ImageService::class);

// Resize
$image = $imageService->resize($file, 800, 600, 'contain');

// Crop
$image = $imageService->crop($file, 400, 400, 50, 50);

// Rotate
$image = $imageService->rotate($file, 90);

// Flip
$image = $imageService->flip($file, 'horizontal');

// Filters
$image = $imageService->grayscale($file);
$image = $imageService->brightness($file, 20);
$image = $imageService->contrast($file, 10);
$image = $imageService->blur($file, 5);
$image = $imageService->sharpen($file, 10);

// Watermark
$image = $imageService->watermark(
    $file,
    '/path/to/watermark.png',
    'bottom-right',
    50, // opacity
    10  // padding
);

// Optimize for web
$image = $imageService->optimize($file, 'webp', 85);

// Encode
$content = $imageService->encode($image, 'webp', 85);
```

## Media Model Properties

```php
$media = $product->getFirstMedia('images');

// Basic properties
$media->id;
$media->uuid;
$media->name;
$media->file_name;
$media->mime_type;
$media->size;
$media->type; // image, video, audio, document, other

// Dimensions
$media->width;
$media->height;
$media->aspect_ratio;
$media->getDimensions(); // "1920x1080"

// Size
$media->getHumanReadableSize(); // "2.5 MB"

// For videos/audio
$media->duration; // seconds
$media->getFormattedDuration(); // "02:35"

// Custom properties
$media->getCustomProperty('alt');
$media->setCustomProperty('caption', 'New caption')->save();

// Type checks
$media->isImage();
$media->isVideo();
$media->isAudio();
$media->isDocument();

// Conversions
$media->hasConversion('thumb');
$media->getAvailableConversions(); // ['thumb', 'medium', 'large']
```

## Queue Processing

Conversions are processed in background by default:

```bash
# Run queue worker
php artisan queue:work --queue=media
```

To disable queue processing:

```env
MEDIA_LIBRARY_QUEUE_ENABLED=false
```

## Dependencies

- `intervention/image-laravel` ^1.0
- `eduardoks98/storage-s3` ^1.0
- `eduardoks98/base-api` ^1.0

## Image Drivers

The package supports GD and Imagick drivers:

### GD (Default)
- Built into most PHP installations
- Good for basic operations
- Limited format support

### Imagick
- Better quality
- More features
- Requires ImageMagick extension

```env
MEDIA_LIBRARY_IMAGE_DRIVER=imagick
```

## Related

- [Storage S3](../s3/README.md) - AWS S3 integration
- [Intervention Image](https://image.intervention.io/v3) - Image processing library

## License

MIT License
