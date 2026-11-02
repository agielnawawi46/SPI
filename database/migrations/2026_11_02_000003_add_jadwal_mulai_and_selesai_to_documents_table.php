<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('documents')) {
            Schema::table('documents', function (Blueprint $table) {
                if (!Schema::hasColumn('documents', 'jadwal_mulai')) {
                    $table->date('jadwal_mulai')->nullable()->after('jadwal_audit');
                }
                if (!Schema::hasColumn('documents', 'jadwal_selesai')) {
                    $table->date('jadwal_selesai')->nullable()->after('jadwal_mulai');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('documents')) {
            Schema::table('documents', function (Blueprint $table) {
                if (Schema::hasColumn('documents', 'jadwal_mulai')) {
                    $table->dropColumn('jadwal_mulai');
                }
                if (Schema::hasColumn('documents', 'jadwal_selesai')) {
                    $table->dropColumn('jadwal_selesai');
                }
            });
        }
    }
};

