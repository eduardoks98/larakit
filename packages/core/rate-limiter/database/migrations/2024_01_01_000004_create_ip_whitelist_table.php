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
        Schema::create('ip_whitelist', function (Blueprint $table) {
            $table->id();
            $table->string('min_range', 45)->nullable()->index();
            $table->string('max_range', 45)->nullable()->index();
            $table->string('unique_ip', 45)->nullable()->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_whitelist');
    }
};
