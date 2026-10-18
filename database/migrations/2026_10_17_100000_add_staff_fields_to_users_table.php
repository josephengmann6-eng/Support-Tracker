<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nullable only because SQLite cannot add a NOT NULL column without a default;
            // the application always requires a staff ID.
            $table->string('staff_id')->nullable()->unique()->after('id');
            $table->string('phone')->nullable();
            $table->string('department')->nullable();
            $table->string('role')->default('member');
            $table->boolean('is_active')->default(true);

            $table->index('role');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropUnique(['staff_id']);
            $table->dropColumn(['staff_id', 'phone', 'department', 'role', 'is_active']);
        });
    }
};
