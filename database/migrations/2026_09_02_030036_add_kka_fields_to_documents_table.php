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
            $table->date('tgl_kka')->nullable()->after('peserta_entry');
            $table->string('direview_disahkan_oleh')->nullable()->after('tgl_kka');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['tgl_kka', 'direview_disahkan_oleh']);
        });
    }
};
