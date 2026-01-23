# Media Library Package

> Media management for Laravel with image processing, automatic conversions, and file organization.

## Overview

The `eduardoks98/media-library` package provides comprehensive media management with automatic image conversions, watermarking, and collection-based organization.

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
MEDIA_LIBRARY_DISK=s3
MEDIA_LIBRARY_IMAGE_DRIVER=gd
MEDIA_LIBRARY_DEFAULT_FORMAT=webp
MEDIA_LIBRARY_QUEUE_ENABLED=true
```

## Usage

### Add HasMedia Trait

```php
use Eduardoks98\MediaLibrary\Traits\HasMedia;

class User extends Model
{
    use HasMedia;
}
```

### Upload Media

```php
// Single file
$media = $user->addMedia($request->file('avatar'), 'avatars');

// Multiple files
$items = $product->addMediaMultiple($request->file('images'), 'gallery');
```

### Retrieve Media

```php
// Get all in collection
$images = $product->getMedia('gallery');

// Get first
$avatar = $user->getFirstMedia('avatars');

// Get URL
$url = $user->getFirstMediaUrl('avatars', 'thumb');
```

### Delete Media

```php
// Single
$media->delete();

// Clear collection
$user->clearMediaCollection('avatars');
```

## Conversions

Default conversions automatically generated:

| Name | Size | Fit |
|------|------|-----|
| thumb | 150x150 | crop |
| small | 320xauto | contain |
| medium | 640xauto | contain |
| large | 1024xauto | contain |
| xl | 1920xauto | contain |

## Collections

Define collections with specific rules:

```php
'collections' => [
    'avatars' => [
        'conversions' => ['thumb', 'small'],
        'max_file_size' => 2 * 1024 * 1024,
        'single' => true,
    ],
    'gallery' => [
        'conversions' => ['thumb', 'medium', 'large'],
        'max_file_size' => 20 * 1024 * 1024,
    ],
],
```

## Image Processing

```php
use Eduardoks98\MediaLibrary\Services\ImageService;

$imageService = app(ImageService::class);

// Resize
$image = $imageService->resize($file, 800, 600, 'contain');

// Effects
$image = $imageService->watermark($file, '/path/to/watermark.png');
$image = $imageService->grayscale($file);
$image = $imageService->blur($file, 5);
```

## Features

- Automatic conversions (WebP, AVIF)
- Watermarking
- Image filters
- Collection management
- Background queue processing
- Polymorphic relationships

## Dependencies

- `intervention/image-laravel` ^1.0
- `eduardoks98/storage-s3` ^1.0

## Related

- [Storage S3](./storage-s3.md)
- [Intervention Image](https://image.intervention.io/v3)
