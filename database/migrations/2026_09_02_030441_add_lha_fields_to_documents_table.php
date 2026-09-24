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
            $table->date('tgl_lha')->nullable()->after('direview_disahkan_oleh');
            $table->string('pengesahan_lha')->nullable()->after('tgl_lha');
            $table->string('distribusi_lha')->nullable()->after('pengesahan_lha');
            $table->date('tgl_distribusi_lha')->nullable()->after('distribusi_lha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['tgl_lha', 'pengesahan_lha', 'distribusi_lha', 'tgl_distribusi_lha']);
        });
    }
};
