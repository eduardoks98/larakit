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
        Schema::create('twilio_messages', function (Blueprint $table) {
            $table->id();

            // Twilio identifiers
            $table->string('message_sid', 34)->unique()->comment('Twilio message SID (SM...)');

            // Phone numbers (E.164 format)
            $table->string('from', 20)->comment('Sender phone number');
            $table->string('to', 20)->index()->comment('Recipient phone number');

            // Message content
            $table->text('body')->comment('Message text content');

            // Status tracking
            $table->string('status', 20)->index()->comment('Current message status');
            $table->string('direction', 20)->comment('Message direction');

            // Delivery information
            $table->integer('num_segments')->nullable()->comment('Number of SMS segments');
            $table->decimal('price', 10, 4)->nullable()->comment('Message cost');
            $table->string('price_unit', 3)->nullable()->comment('Currency code (USD, BRL, etc)');

            // Error handling
            $table->integer('error_code')->nullable()->comment('Twilio error code');
            $table->text('error_message')->nullable()->comment('Error description');

            // Additional data
            $table->json('metadata')->nullable()->comment('Custom metadata');

            // Timestamps
            $table->timestamp('sent_at')->nullable()->comment('When message was sent');
            $table->timestamp('delivered_at')->nullable()->comment('When message was delivered');
            $table->timestamp('failed_at')->nullable()->comment('When message failed');
            $table->timestamps();

            // Indexes for common queries
            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index(['to', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('twilio_messages');
    }
};
