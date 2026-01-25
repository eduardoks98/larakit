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
        $pivotTable = config('permissions.tables.pivot', 'profile_permissions');
        $profilesTable = config('permissions.tables.profiles', 'profiles');
        $permissionsTable = config('permissions.tables.permissions', 'permissions');

        Schema::create($pivotTable, function (Blueprint $table) use ($profilesTable, $permissionsTable) {
            $table->id();
            $table->foreignId('profile_id')
                ->constrained($profilesTable)
                ->cascadeOnDelete();
            $table->foreignId('permission_id')
                ->constrained($permissionsTable)
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['profile_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $pivotTable = config('permissions.tables.pivot', 'profile_permissions');
        Schema::dropIfExists($pivotTable);
    }
};
