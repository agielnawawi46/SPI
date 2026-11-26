<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedTinyInteger('report_month')->nullable()->after('tanggal_formulir')->comment('Bulan laporan kinerja (1-12)');
            $table->unsignedSmallInteger('report_year')->nullable()->after('report_month')->comment('Tahun laporan kinerja');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['report_month', 'report_year']);
        });
    }
};
