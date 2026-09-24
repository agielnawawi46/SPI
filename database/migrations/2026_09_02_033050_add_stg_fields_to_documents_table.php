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
            $table->date('tgl_stg')->nullable()->after('tgl_distribusi_lha');
            $table->string('auditee')->nullable()->after('tgl_stg');
            $table->string('jadwal_audit')->nullable()->after('auditee');
            $table->text('lingkup_audit')->nullable()->after('jadwal_audit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['tgl_stg', 'auditee', 'jadwal_audit', 'lingkup_audit']);
        });
    }
};
