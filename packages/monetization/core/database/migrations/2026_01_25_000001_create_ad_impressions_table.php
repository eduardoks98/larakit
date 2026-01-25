<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('monetization.tables.impressions', 'ad_impressions'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 50)->index();
            $table->string('ad_unit_id', 100)->nullable()->index();
            $table->string('ad_network', 100)->nullable();
            $table->string('ad_type', 50)->nullable();
            $table->string('placement', 100)->nullable();
            $table->string('transaction_id', 255)->nullable()->index();
            $table->decimal('revenue', 15, 6)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('country', 2)->nullable();
            $table->string('platform', 20)->nullable();
            $table->string('device_id', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('impression_at')->useCurrent();
            $table->timestamps();

            $table->index(['provider', 'impression_at']);
            $table->index(['user_id', 'impression_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('monetization.tables.impressions', 'ad_impressions'));
    }
};
