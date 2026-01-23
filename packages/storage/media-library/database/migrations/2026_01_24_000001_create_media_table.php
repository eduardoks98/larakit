<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Polymorphic relation
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->index(['model_type', 'model_id']);

            // Collection
            $table->string('collection_name')->default('default');
            $table->index('collection_name');

            // File info
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type');
            $table->string('disk')->default('s3');
            $table->string('path');
            $table->unsignedBigInteger('size');

            // Type
            $table->string('type')->default('other'); // image, video, audio, document, other

            // Dimensions (for images/videos)
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->decimal('aspect_ratio', 8, 4)->nullable();

            // Video/Audio specific
            $table->unsignedInteger('duration')->nullable(); // seconds

            // Conversions status
            $table->json('conversions')->nullable(); // Array of generated conversions
            $table->json('responsive_images')->nullable();

            // Metadata
            $table->json('custom_properties')->nullable();
            $table->json('generated_conversions')->nullable();

            // Ordering
            $table->unsignedInteger('order_column')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // Media conversions table
        Schema::create('media_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->onDelete('cascade');

            $table->string('conversion_name');
            $table->string('file_name');
            $table->string('path');
            $table->string('disk');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');

            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            $table->timestamps();

            $table->unique(['media_id', 'conversion_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_conversions');
        Schema::dropIfExists('media');
    }
};
