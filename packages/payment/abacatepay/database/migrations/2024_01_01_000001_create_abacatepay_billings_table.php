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
        Schema::create('abacatepay_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('abacatepay_id')->unique()->nullable()->index();
            $table->string('frequency'); // one_time, monthly, yearly
            $table->json('methods'); // [pix, card]
            $table->bigInteger('amount')->default(0); // Amount in cents
            $table->string('status')->default('pending')->index(); // pending, paid, cancelled, expired, refunded
            $table->text('url')->nullable(); // Payment URL
            $table->json('products')->nullable(); // Product details
            $table->json('customer_data')->nullable(); // Customer information
            $table->json('metadata')->nullable(); // Additional metadata
            $table->string('return_url')->nullable();
            $table->string('completion_url')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abacatepay_billings');
    }
};
