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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();

            // WhatsApp identifiers
            $table->string('message_id', 100)->unique()->comment('WhatsApp message ID (wamid.*)');
            $table->string('phone_number', 20)->index()->comment('Recipient phone number (E.164)');

            // Message type and status
            $table->string('type', 20)->comment('Message type (text, image, template, etc)');
            $table->string('status', 20)->index()->default('queued')->comment('Current message status');

            // Content
            $table->text('text_content')->nullable()->comment('Text content for text messages');
            $table->string('media_url', 500)->nullable()->comment('Media URL for media messages');
            $table->string('media_id', 100)->nullable()->comment('WhatsApp media ID');

            // Template messages
            $table->string('template_name', 100)->nullable()->comment('Template name');
            $table->json('template_params')->nullable()->comment('Template parameters');

            // Additional data
            $table->json('metadata')->nullable()->comment('Custom metadata');

            // Error handling
            $table->string('error_code', 50)->nullable()->comment('WhatsApp error code');
            $table->text('error_message')->nullable()->comment('Error description');

            // Timestamps
            $table->timestamp('sent_at')->nullable()->comment('When message was sent');
            $table->timestamp('delivered_at')->nullable()->comment('When message was delivered');
            $table->timestamp('read_at')->nullable()->comment('When message was read');
            $table->timestamp('failed_at')->nullable()->comment('When message failed');
            $table->timestamps();

            // Indexes for common queries
            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index(['phone_number', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
