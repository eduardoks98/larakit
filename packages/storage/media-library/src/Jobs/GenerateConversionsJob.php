<?php

namespace Eduardoks98\MediaLibrary\Jobs;

use Eduardoks98\MediaLibrary\Models\Media;
use Eduardoks98\MediaLibrary\Services\MediaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\UploadedFile;

/**
 * Job to generate image conversions in background.
 */
class GenerateConversionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Media $media,
        public array $conversionNames,
        public UploadedFile|string|null $originalFile = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MediaService $mediaService): void
    {
        $mediaService->processConversions(
            $this->media,
            $this->conversionNames,
            $this->originalFile
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error('GenerateConversionsJob failed', [
            'media_id' => $this->media->id,
            'conversions' => $this->conversionNames,
            'error' => $exception->getMessage(),
        ]);
    }
}
