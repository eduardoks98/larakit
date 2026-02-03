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
        Schema::create('ad_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id')->nullable()->index();
            $table->string('name', 100);
            $table->string('slot_id', 50)->comment('AdSense ad slot ID');
            $table->string('format', 30)->default('responsive');
            $table->string('position', 50)->nullable()->comment('e.g., header, sidebar, between_matches');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable()->comment('Additional configuration');
            $table->timestamps();

            $table->index(['game_id', 'is_active']);
            $table->index(['position', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_units');
    }
};
