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
        Schema::create('discord_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('discord_id')->unique();
            $table->string('email')->nullable()->index();
            $table->string('username')->nullable();
            $table->string('discriminator', 4)->nullable();
            $table->string('global_name')->nullable();
            $table->text('avatar')->nullable();
            $table->text('banner')->nullable();
            $table->integer('accent_color')->nullable();
            $table->string('locale', 10)->nullable();
            $table->boolean('verified')->default(false);
            $table->boolean('mfa_enabled')->default(false);
            $table->integer('premium_type')->nullable();
            $table->integer('flags')->nullable();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->integer('expires_in')->nullable();
            $table->string('token_type')->default('Bearer');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'discord_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discord_users');
    }
};
