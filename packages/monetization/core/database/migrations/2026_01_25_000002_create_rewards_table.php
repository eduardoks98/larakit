<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('monetization.tables.rewards', 'rewards'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 50)->index();
            $table->string('transaction_id', 255)->index();
            $table->string('ad_unit_id', 100)->nullable();
            $table->string('reward_type', 50)->default('currency');
            $table->string('reward_item', 100);
            $table->unsignedInteger('reward_amount');
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'transaction_id']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'attempts']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('monetization.tables.rewards', 'rewards'));
    }
};
