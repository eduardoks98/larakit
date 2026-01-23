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
        Schema::create('comtele_messages', function (Blueprint $table) {
            $table->id();

            // Comtele identifiers
            $table->uuid('request_unique_id')->unique()->comment('Comtele request UUID');
            $table->string('sender', 100)->index()->comment('Internal sender identifier');

            // Recipients (can be multiple, comma-separated)
            $table->text('receivers')->comment('Phone numbers (DDD+Number, comma-separated)');
            $table->string('phone_number', 20)->nullable()->index()->comment('Individual phone for detailed tracking');

            // Message content
            $table->text('content')->comment('Message text content');

            // Status tracking
            $table->string('status', 20)->index()->default('Pending')->comment('Current message status');
            $table->string('status_date', 50)->nullable()->comment('Status update timestamp from Comtele');

            // Error handling
            $table->text('error_message')->nullable()->comment('Error description if failed');

            // Additional data
            $table->json('metadata')->nullable()->comment('Custom metadata');

            // Timestamps
            $table->timestamp('delivered_at')->nullable()->comment('When message was delivered');
            $table->timestamp('failed_at')->nullable()->comment('When message failed');
            $table->timestamps();

            // Indexes for common queries
            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index(['sender', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comtele_messages');
    }
};
