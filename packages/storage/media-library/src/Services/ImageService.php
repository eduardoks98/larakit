<?php

namespace Eduardoks98\MediaLibrary\Services;

use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Interfaces\ImageInterface;
use Eduardoks98\MediaLibrary\Enums\ConversionFit;
use Illuminate\Support\Facades\Log;

/**
 * Image Processing Service
 *
 * Handles image manipulation using Intervention Image v3.
 */
class ImageService
{
    /**
     * Resize an image.
     *
     * @param string|ImageInterface $image Image path or instance
     * @param int|null $width Target width (null to maintain aspect ratio)
     * @param int|null $height Target height (null to maintain aspect ratio)
     * @param ConversionFit|string $fit Fit mode
     * @param int $quality Output quality (1-100)
     * @return ImageInterface
     */
    public function resize(
        string|ImageInterface $image,
        ?int $width = null,
        ?int $height = null,
        ConversionFit|string $fit = ConversionFit::CONTAIN,
        int $quality = 85
    ): ImageInterface {
        $img = $image instanceof ImageInterface ? $image : Image::read($image);
        $fit = $fit instanceof ConversionFit ? $fit : ConversionFit::from($fit);

        return match ($fit) {
            ConversionFit::CROP => $this->cropResize($img, $width, $height),
            ConversionFit::CONTAIN => $this->containResize($img, $width, $height),
            ConversionFit::FILL => $this->fillResize($img, $width, $height),
            ConversionFit::STRETCH => $this->stretchResize($img, $width, $height),
            ConversionFit::PAD => $this->padResize($img, $width, $height),
        };
    }

    /**
     * Crop resize - exact dimensions, centered crop.
     */
    protected function cropResize(ImageInterface $img, ?int $width, ?int $height): ImageInterface
    {
        if ($width && $height) {
            return $img->cover($width, $height);
        }

        if ($width) {
            return $img->scale(width: $width);
        }

        if ($height) {
            return $img->scale(height: $height);
        }

        return $img;
    }

    /**
     * Contain resize - fit within dimensions maintaining aspect ratio.
     */
    protected function containResize(ImageInterface $img, ?int $width, ?int $height): ImageInterface
    {
        if ($width && $height) {
            return $img->scaleDown($width, $height);
        }

        if ($width) {
            return $img->scaleDown(width: $width);
        }

        if ($height) {
            return $img->scaleDown(height: $height);
        }

        return $img;
    }

    /**
     * Fill resize - cover dimensions with crop.
     */
    protected function fillResize(ImageInterface $img, ?int $width, ?int $height): ImageInterface
    {
        if ($width && $height) {
            return $img->cover($width, $height);
        }

        return $this->containResize($img, $width, $height);
    }

    /**
     * Stretch resize - exact dimensions ignoring aspect ratio.
     */
    protected function stretchResize(ImageInterface $img, ?int $width, ?int $height): ImageInterface
    {
        if ($width && $height) {
            return $img->resize($width, $height);
        }

        return $this->containResize($img, $width, $height);
    }

    /**
     * Pad resize - fit within dimensions with padding.
     */
    protected function padResize(ImageInterface $img, ?int $width, ?int $height): ImageInterface
    {
        if ($width && $height) {
            return $img->contain($width, $height, 'ffffff');
        }

        return $this->containResize($img, $width, $height);
    }

    /**
     * Apply watermark to an image.
     *
     * @param string|ImageInterface $image
     * @param string $watermarkPath Path to watermark image
     * @param string $position Position (top-left, top-right, bottom-left, bottom-right, center)
     * @param int $opacity Opacity (0-100)
     * @param int $padding Padding from edges
     * @return ImageInterface
     */
    public function watermark(
        string|ImageInterface $image,
        string $watermarkPath,
        string $position = 'bottom-right',
        int $opacity = 50,
        int $padding = 10
    ): ImageInterface {
        $img = $image instanceof ImageInterface ? $image : Image::read($image);
        $watermark = Image::read($watermarkPath);

        // Scale watermark if needed (max 20% of image width)
        $maxWatermarkWidth = (int) ($img->width() * 0.2);
        if ($watermark->width() > $maxWatermarkWidth) {
            $watermark->scale(width: $maxWatermarkWidth);
        }

        // Calculate position
        [$x, $y] = $this->calculateWatermarkPosition(
            $img->width(),
            $img->height(),
            $watermark->width(),
            $watermark->height(),
            $position,
            $padding
        );

        // Apply opacity and place watermark
        return $img->place($watermark, 'top-left', $x, $y, $opacity);
    }

    /**
     * Calculate watermark position.
     */
    protected function calculateWatermarkPosition(
        int $imageWidth,
        int $imageHeight,
        int $watermarkWidth,
        int $watermarkHeight,
        string $position,
        int $padding
    ): array {
        return match ($position) {
            'top-left' => [$padding, $padding],
            'top-right' => [$imageWidth - $watermarkWidth - $padding, $padding],
            'bottom-left' => [$padding, $imageHeight - $watermarkHeight - $padding],
            'bottom-right' => [$imageWidth - $watermarkWidth - $padding, $imageHeight - $watermarkHeight - $padding],
            'center' => [
                (int) (($imageWidth - $watermarkWidth) / 2),
                (int) (($imageHeight - $watermarkHeight) / 2),
            ],
            default => [$imageWidth - $watermarkWidth - $padding, $imageHeight - $watermarkHeight - $padding],
        };
    }

    /**
     * Crop an image.
     *
     * @param string|ImageInterface $image
     * @param int $width Crop width
     * @param int $height Crop height
     * @param int $x X position
     * @param int $y Y position
     * @return ImageInterface
     */
    public function crop(
        string|ImageInterface $image,
        int $width,
        int $height,
        int $x = 0,
        int $y = 0
    ): ImageInterface {
        $img = $image instanceof ImageInterface ? $image : Image::read($image);

        return $img->crop($width, $height, $x, $y);
    }

    /**
     * Rotate an image.
     *
     * @param string|ImageInterface $image
     * @param float $angle Rotation angle
     * @param string $bgColor Background color for uncovered areas
     * @return ImageInterface
     */
    public function rotate(
        string|ImageInterface $image,
        float $angle,
        string $bgColor = 'ffffff'
    ): ImageInterface {
        $img = $image instanceof ImageInterface ? $image : Image::read($image);

        return $img->rotate($angle, $bgColor);
    }

    /**
     * Flip an image.
     *
     * @param string|ImageInterface $image
     * @param string $direction 'horizontal' or 'vertical'
     * @return ImageInterface
     */
    public function flip(
        string|ImageInterface $image,
        string $direction = 'horizontal'
    ): ImageInterface {
        $img = $image instanceof ImageInterface ? $image : Image::read($image);

        return $direction === 'vertical' ? $img->flip() : $img->flop();
    }

    /**
     * Apply grayscale filter.
     */
    public function grayscale(string|ImageInterface $image): ImageInterface
    {
        $img = $image instanceof ImageInterface ? $image : Image::read($image);

        return $img->greyscale();
    }

    /**
     * Adjust brightness.
     *
     * @param string|ImageInterface $image
     * @param int $level -100 to 100
     */
    public function brightness(string|ImageInterface $image, int $level): ImageInterface
    {
        $img = $image instanceof ImageInterface ? $image : Image::read($image);

        return $img->brightness($level);
    }

    /**
     * Adjust contrast.
     *
     * @param string|ImageInterface $image
     * @param int $level -100 to 100
     */
    public function contrast(string|ImageInterface $image, int $level): ImageInterface
    {
        $img = $image instanceof ImageInterface ? $image : Image::read($image);

        return $img->contrast($level);
    }

    /**
     * Apply blur effect.
     *
     * @param string|ImageInterface $image
     * @param int $amount 0 to 100
     */
    public function blur(string|ImageInterface $image, int $amount = 10): ImageInterface
    {
        $img = $image instanceof ImageInterface ? $image : Image::read($image);

        return $img->blur($amount);
    }

    /**
     * Apply sharpen effect.
     *
     * @param string|ImageInterface $image
     * @param int $amount 0 to 100
     */
    public function sharpen(string|ImageInterface $image, int $amount = 10): ImageInterface
    {
        $img = $image instanceof ImageInterface ? $image : Image::read($image);

        return $img->sharpen($amount);
    }

    /**
     * Optimize an image for web.
     *
     * @param string|ImageInterface $image
     * @param string $format Output format (jpg, png, webp, avif)
     * @param int $quality Quality level
     * @param bool $stripMetadata Remove EXIF data
     * @return ImageInterface
     */
    public function optimize(
        string|ImageInterface $image,
        string $format = 'webp',
        int $quality = 85,
        bool $stripMetadata = true
    ): ImageInterface {
        $img = $image instanceof ImageInterface ? $image : Image::read($image);

        // Strip metadata if requested
        if ($stripMetadata) {
            $img->removeAnimation();
        }

        return $img;
    }

    /**
     * Get image dimensions.
     *
     * @param string|ImageInterface $image
     * @return array{width: int, height: int, aspect_ratio: float}
     */
    public function getDimensions(string|ImageInterface $image): array
    {
        $img = $image instanceof ImageInterface ? $image : Image::read($image);

        $width = $img->width();
        $height = $img->height();

        return [
            'width' => $width,
            'height' => $height,
            'aspect_ratio' => $height > 0 ? round($width / $height, 4) : 0,
        ];
    }

    /**
     * Encode image to specific format.
     *
     * @param ImageInterface $image
     * @param string $format
     * @param int $quality
     * @return string Encoded image data
     */
    public function encode(ImageInterface $image, string $format = 'webp', int $quality = 85): string
    {
        return match ($format) {
            'jpg', 'jpeg' => $image->toJpeg($quality)->toString(),
            'png' => $image->toPng()->toString(),
            'webp' => $image->toWebp($quality)->toString(),
            'gif' => $image->toGif()->toString(),
            'avif' => $image->toAvif($quality)->toString(),
            default => $image->toWebp($quality)->toString(),
        };
    }

    /**
     * Get MIME type for format.
     */
    public function getMimeType(string $format): string
    {
        return match ($format) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'avif' => 'image/avif',
            default => 'image/webp',
        };
    }
}
