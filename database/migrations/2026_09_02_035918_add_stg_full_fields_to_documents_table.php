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
            $table->string('header_image')->nullable()->after('penandatangan_stg');
            $table->string('footer_image')->nullable()->after('header_image');
            $table->text('deskripsi_pembuka')->nullable()->after('footer_image');
            $table->text('deskripsi_penutup')->nullable()->after('deskripsi_pembuka');
            $table->json('tim_audit')->nullable()->after('deskripsi_penutup');
            $table->string('jabatan_direktur')->nullable()->after('tim_audit');
            $table->string('nama_direktur')->nullable()->after('jabatan_direktur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['header_image', 'footer_image', 'deskripsi_pembuka', 'deskripsi_penutup', 'tim_audit', 'jabatan_direktur', 'nama_direktur']);
        });
    }
};
