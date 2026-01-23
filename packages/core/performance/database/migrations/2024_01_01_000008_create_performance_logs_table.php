<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('route')->index();
            $table->string('method', 10);
            $table->integer('duration_ms')->index();
            $table->integer('query_count')->default(0);
            $table->integer('query_time_ms')->default(0);
            $table->decimal('memory_mb', 8, 2)->default(0);
            $table->integer('response_size')->default(0);
            $table->boolean('is_slow')->default(false)->index();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index(['route', 'is_slow', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_logs');
    }
};
