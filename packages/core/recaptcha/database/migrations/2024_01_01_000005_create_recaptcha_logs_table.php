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
        Schema::create('recaptcha_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->index();
            $table->decimal('score', 3, 3)->default(0.000);
            $table->decimal('trust_score', 3, 3)->default(0.000);
            $table->decimal('threshold', 3, 2)->default(0.50);
            $table->boolean('success')->default(false)->index();
            $table->string('decision', 50)->nullable()->index();
            $table->text('decision_reason')->nullable();
            $table->json('context')->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->boolean('login_attempted')->default(false)->index();
            $table->boolean('login_successful')->nullable();
            $table->text('login_failure_reason')->nullable();
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['ip', 'success', 'created_at']);
            $table->index(['user_id', 'login_attempted', 'created_at']);
            $table->index(['decision', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recaptcha_logs');
    }
};
