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
        Schema::create('microsoft_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('microsoft_id')->unique();
            $table->string('email')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('given_name')->nullable();
            $table->string('surname')->nullable();
            $table->string('user_principal_name')->nullable()->index();
            $table->string('job_title')->nullable();
            $table->string('office_location')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->json('business_phones')->nullable();
            $table->string('preferred_language')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('tenant_id')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'microsoft_id']);
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('microsoft_users');
    }
};
