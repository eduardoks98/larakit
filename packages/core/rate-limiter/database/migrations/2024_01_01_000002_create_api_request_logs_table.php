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
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->unique();
            $table->string('ip', 45)->index();
            $table->string('route')->index();
            $table->string('method', 10);
            $table->integer('http_code')->index();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->integer('response_time_ms')->default(0);
            $table->text('user_agent')->nullable();
            $table->boolean('success')->default(true)->index();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            // Composite indexes for common queries
            $table->index(['ip', 'route', 'created_at']);
            $table->index(['route', 'success', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};
