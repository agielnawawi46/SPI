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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('ketua_tim')->nullable()->after('lingkup_audit');
            $table->text('anggota_tim')->nullable()->after('ketua_tim');
            $table->text('tujuan_audit')->nullable()->after('anggota_tim');
            $table->string('periode_audit')->nullable()->after('tujuan_audit');
            $table->string('penandatangan_stg')->nullable()->after('periode_audit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['ketua_tim', 'anggota_tim', 'tujuan_audit', 'periode_audit', 'penandatangan_stg']);
        });
    }
};
