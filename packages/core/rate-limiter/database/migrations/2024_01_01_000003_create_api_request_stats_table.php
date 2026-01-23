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
        Schema::create('api_request_stats', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('route');
            $table->date('date');
            $table->integer('total_requests')->default(0);

            // Composite unique index to prevent duplicates
            $table->unique(['ip_address', 'route', 'date']);

            // Index for common queries
            $table->index(['date', 'total_requests']);
            $table->index('ip_address');
            $table->index('route');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_request_stats');
    }
};
