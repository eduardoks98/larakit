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
        Schema::create('mercadopago_webhooks', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Webhook data
            $table->string('topic')->index();
            $table->string('resource_id')->index();
            $table->string('data_id')->nullable()->index();
            $table->string('action')->nullable();

            // Full payload
            $table->json('payload');

            // Processing status
            $table->boolean('processed')->default(false)->index();
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            // Indexes for queries
            $table->index(['topic', 'processed']);
            $table->index(['processed', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercadopago_webhooks');
    }
};
