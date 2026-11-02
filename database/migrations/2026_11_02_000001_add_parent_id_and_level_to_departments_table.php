<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('departments')) {
            Schema::table('departments', function (Blueprint $table) {
                if (!Schema::hasColumn('departments', 'parent_id')) {
                    $table->foreignId('parent_id')->nullable()->after('id')->constrained('departments')->nullOnDelete();
                }
                if (!Schema::hasColumn('departments', 'level')) {
                    $table->string('level', 50)->default('manager')->after('name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('departments')) {
            Schema::table('departments', function (Blueprint $table) {
                if (Schema::hasColumn('departments', 'parent_id')) {
                    $table->dropForeign(['parent_id']);
                    $table->dropColumn('parent_id');
                }
                if (Schema::hasColumn('departments', 'level')) {
                    $table->dropColumn('level');
                }
            });
        }
    }
};

