# AWS S3 Storage Package

> AWS S3 storage wrapper for Laravel with signed URLs, CloudFront CDN integration, and file management.

## Features

- Upload files (single and batch)
- Download files
- Signed URLs (S3 and CloudFront)
- Pre-signed upload URLs (client-side upload)
- File management (copy, move, delete)
- CloudFront CDN integration
- Multipart upload for large files
- File type validation
- File size limits
- Automatic path generation
- Comprehensive error handling

## Installation

```bash
composer require eduardoks98/storage-s3
```

## Configuration

### Environment Variables

```env
# AWS Credentials
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name

# CloudFront (optional)
AWS_CLOUDFRONT_ENABLED=false
AWS_CLOUDFRONT_URL=https://dxxxxxxx.cloudfront.net
AWS_CLOUDFRONT_KEY_PAIR_ID=KXXXXXXX
AWS_CLOUDFRONT_PRIVATE_KEY_PATH=/path/to/private_key.pem

# Signed URLs
AWS_SIGNED_URL_EXPIRATION="+60 minutes"
AWS_SIGNED_URL_USE_CLOUDFRONT=false

# Upload Settings
AWS_DEFAULT_VISIBILITY=private
AWS_DEFAULT_ACL=private
AWS_CACHE_CONTROL="max-age=31536000"
AWS_MULTIPART_THRESHOLD=104857600

# File Size Limits (bytes)
AWS_MAX_SIZE_IMAGES=10485760
AWS_MAX_SIZE_DOCUMENTS=52428800
AWS_MAX_SIZE_VIDEOS=524288000
AWS_MAX_SIZE_AUDIO=104857600
```

### Publish Config

```bash
php artisan vendor:publish --provider="Eduardoks98\StorageS3\StorageS3ServiceProvider" --tag="config"
```

## Usage

### Basic Upload

```php
use Eduardoks98\StorageS3\Services\S3Service;
use Eduardoks98\StorageS3\Enums\Visibility;
use Eduardoks98\StorageS3\Enums\FileCategory;

$s3 = app(S3Service::class);

// Upload from request
$result = $s3->upload(
    file: $request->file('image'),
    category: FileCategory::IMAGES,
    visibility: Visibility::PUBLIC
);

// Result:
// [
//     'success' => true,
//     'path' => 'images/2026/01/24/uuid-filename.jpg',
//     'url' => 'https://bucket.s3.amazonaws.com/images/...',
//     'size' => 1234567,
//     'mime_type' => 'image/jpeg',
//     'original_name' => 'photo.jpg',
//     'etag' => '"abc123..."',
// ]
```

### Upload with Custom Path

```php
$result = $s3->upload(
    file: $request->file('document'),
    path: 'users/123/documents/contract.pdf',
    visibility: Visibility::PRIVATE
);
```

### Upload Multiple Files

```php
$results = $s3->uploadMultiple(
    files: $request->file('images'),
    category: FileCategory::IMAGES,
    visibility: Visibility::PUBLIC
);

foreach ($results as $result) {
    if ($result['success']) {
        echo "Uploaded: {$result['path']}";
    } else {
        echo "Failed: {$result['error']}";
    }
}
```

### Using Facade

```php
use Eduardoks98\StorageS3\Facades\S3Storage;

// Upload
$result = S3Storage::upload($file);

// Download
$data = S3Storage::download('path/to/file.pdf');

// Delete
S3Storage::delete('path/to/file.pdf');
```

### Download File

```php
$file = $s3->download('images/2026/01/24/photo.jpg');

// $file contains:
// [
//     'content' => '...binary content...',
//     'mime_type' => 'image/jpeg',
//     'size' => 1234567,
//     'last_modified' => DateTime,
//     'metadata' => [...],
// ]

// Return as response
return response($file['content'])
    ->header('Content-Type', $file['mime_type'])
    ->header('Content-Disposition', 'attachment; filename="photo.jpg"');
```

### Signed URLs

```php
// S3 signed URL (default)
$url = $s3->getSignedUrl('private/document.pdf');

// Custom expiration
$url = $s3->getSignedUrl('private/document.pdf', '+2 hours');

// CloudFront signed URL
$url = $s3->getSignedUrl('private/document.pdf', '+1 hour', useCloudFront: true);
```

### Pre-signed Upload URL (Client-side Upload)

```php
// Get pre-signed URL for direct upload from browser
$uploadData = $s3->getUploadUrl(
    path: 'uploads/user-123/avatar.jpg',
    contentType: 'image/jpeg',
    expiration: '+30 minutes'
);

return response()->json($uploadData);

// Frontend can use this to upload directly to S3:
// POST to uploadData['url'] with uploadData['fields'] + file
```

### File Management

```php
// Check if file exists
if ($s3->exists('path/to/file.pdf')) {
    // File exists
}

// Get file metadata
$metadata = $s3->getMetadata('path/to/file.pdf');
// Returns: size, mime_type, last_modified, etag, metadata

// Copy file
$s3->copy('source/file.pdf', 'destination/file.pdf');

// Move file
$s3->move('source/file.pdf', 'destination/file.pdf');

// Delete file
$s3->delete('path/to/file.pdf');

// Delete multiple files
$result = $s3->deleteMultiple([
    'path/to/file1.pdf',
    'path/to/file2.pdf',
]);
```

### List Files

```php
// List files in directory
$result = $s3->listFiles('images/2026/01/');

// List recursively
$result = $s3->listFiles('images/', recursive: true);

// Limit results
$result = $s3->listFiles('images/', limit: 100);

// Result:
// [
//     'files' => [
//         ['path' => 'images/file1.jpg', 'size' => 1234, ...],
//         ['path' => 'images/file2.jpg', 'size' => 5678, ...],
//     ],
//     'directories' => ['images/subdir1', 'images/subdir2'],
//     'truncated' => false,
// ]
```

## File Categories

Files are automatically organized by category:

| Category | Path | Max Size | Allowed Types |
|----------|------|----------|---------------|
| `IMAGES` | `images/` | 10MB | jpeg, png, gif, webp, avif, svg |
| `DOCUMENTS` | `documents/` | 50MB | pdf, doc, docx, xls, xlsx, txt, csv |
| `VIDEOS` | `videos/` | 500MB | mp4, mpeg, mov, webm, avi |
| `AUDIO` | `audio/` | 100MB | mp3, wav, ogg, flac |
| `TEMP` | `temp/` | 100MB | any |

```php
use Eduardoks98\StorageS3\Enums\FileCategory;

// Upload to specific category
$s3->upload($file, category: FileCategory::DOCUMENTS);

// Auto-detect category from MIME type
$category = FileCategory::fromMimeType('image/jpeg'); // IMAGES
$category = FileCategory::fromExtension('pdf'); // DOCUMENTS
```

## Visibility

```php
use Eduardoks98\StorageS3\Enums\Visibility;

// Private (default) - requires signed URL
$s3->upload($file, visibility: Visibility::PRIVATE);

// Public - accessible via direct URL
$s3->upload($file, visibility: Visibility::PUBLIC);

// Authenticated read - AWS authenticated users only
$s3->upload($file, visibility: Visibility::AUTHENTICATED_READ);
```

## CloudFront Integration

For better performance, use CloudFront CDN:

1. Create a CloudFront distribution pointing to your S3 bucket
2. Configure environment variables:

```env
AWS_CLOUDFRONT_ENABLED=true
AWS_CLOUDFRONT_URL=https://dxxxxxxx.cloudfront.net
AWS_CLOUDFRONT_KEY_PAIR_ID=KXXXXXXX
AWS_CLOUDFRONT_PRIVATE_KEY_PATH=/path/to/private_key.pem
```

3. Use CloudFront signed URLs:

```php
$url = $s3->getSignedUrl('path/to/file.pdf', '+1 hour', useCloudFront: true);
```

## Error Handling

```php
use Eduardoks98\StorageS3\Exceptions\S3Exception;

try {
    $result = $s3->upload($file);
} catch (S3Exception $e) {
    // Handle specific errors
    match ($e->getCode()) {
        404 => 'File not found',
        422 => 'Invalid file type or size',
        500 => 'Upload failed',
        default => 'Unknown error',
    };
}
```

## Dependencies

- `aws/aws-sdk-php-laravel` ^3.0
- `eduardoks98/base-api` ^1.0

## AWS IAM Policy

Minimum IAM permissions required:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:PutObject",
                "s3:GetObject",
                "s3:DeleteObject",
                "s3:ListBucket",
                "s3:GetObjectAcl",
                "s3:PutObjectAcl"
            ],
            "Resource": [
                "arn:aws:s3:::your-bucket-name",
                "arn:aws:s3:::your-bucket-name/*"
            ]
        }
    ]
}
```

## Related

- [Media Library](../media-library/README.md) - Image processing and media management
- [AWS SDK for PHP](https://github.com/aws/aws-sdk-php-laravel) - Official AWS SDK

## License

MIT License
