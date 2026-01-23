# Storage S3 Package

> AWS S3 storage wrapper for Laravel with signed URLs and CloudFront CDN integration.

## Overview

The `eduardoks98/storage-s3` package provides a comprehensive wrapper around AWS S3 with support for file uploads, signed URLs, CloudFront CDN, and file management.

## Installation

```bash
composer require eduardoks98/storage-s3
```

## Configuration

### Environment Variables

```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name

# CloudFront (optional)
AWS_CLOUDFRONT_ENABLED=false
AWS_CLOUDFRONT_URL=https://dxxxxxxx.cloudfront.net
```

### Publish Config

```bash
php artisan vendor:publish --provider="Eduardoks98\StorageS3\StorageS3ServiceProvider" --tag="config"
```

## Usage

### Upload File

```php
use Eduardoks98\StorageS3\Services\S3Service;
use Eduardoks98\StorageS3\Enums\Visibility;
use Eduardoks98\StorageS3\Enums\FileCategory;

$s3 = app(S3Service::class);

// Basic upload
$result = $s3->upload($request->file('document'));

// With options
$result = $s3->upload(
    file: $request->file('image'),
    category: FileCategory::IMAGES,
    visibility: Visibility::PUBLIC
);

// Returns: path, url, size, mime_type, original_name, etag
```

### Download File

```php
$file = $s3->download('path/to/file.pdf');

return response($file['content'])
    ->header('Content-Type', $file['mime_type']);
```

### Signed URLs

```php
// S3 signed URL
$url = $s3->getSignedUrl('private/document.pdf', '+1 hour');

// CloudFront signed URL
$url = $s3->getSignedUrl('private/document.pdf', '+1 hour', useCloudFront: true);
```

### Pre-signed Upload (Client-side)

```php
$uploadData = $s3->getUploadUrl('uploads/file.jpg', 'image/jpeg');
// Frontend uploads directly to S3 using $uploadData
```

### File Management

```php
// Check existence
$exists = $s3->exists('path/to/file.pdf');

// Get metadata
$metadata = $s3->getMetadata('path/to/file.pdf');

// Copy/Move
$s3->copy('source.pdf', 'destination.pdf');
$s3->move('source.pdf', 'destination.pdf');

// Delete
$s3->delete('path/to/file.pdf');
$s3->deleteMultiple(['file1.pdf', 'file2.pdf']);

// List files
$result = $s3->listFiles('images/', recursive: true);
```

## Features

- Single and multipart uploads
- Signed URLs (S3 and CloudFront)
- Pre-signed upload URLs
- File type and size validation
- Automatic path generation
- CloudFront CDN integration
- Comprehensive error handling

## Dependencies

- `aws/aws-sdk-php-laravel` ^3.0
- `eduardoks98/base-api` ^1.0

## Related

- [Media Library](./media-library.md)
- [AWS SDK for PHP](https://github.com/aws/aws-sdk-php-laravel)
