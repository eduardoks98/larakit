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
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('personal_access_tokens', 'type')) {
                $table->string('type', 10)->default('access')->after('abilities')->index(); // 'access' or 'refresh'
            }
            if (!Schema::hasColumn('personal_access_tokens', 'device_id')) {
                $table->string('device_id', 64)->nullable()->after('type')->index();
            }
            if (!Schema::hasColumn('personal_access_tokens', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('last_used_at')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn(['type', 'device_id', 'expires_at']);
        });
    }
};
