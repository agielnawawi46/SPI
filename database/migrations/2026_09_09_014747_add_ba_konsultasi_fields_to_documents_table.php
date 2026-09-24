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
            $table->string('nama_jabatan')->nullable();
            $table->string('nip')->nullable();
            $table->string('bidang_tugas')->nullable();
            $table->text('deskripsi_permasalahan')->nullable();
            $table->text('tujuan_konsultasi')->nullable();
            $table->string('waktu_penyelesaian')->nullable();
            $table->text('pengungkapan')->nullable();
            $table->text('review_permohonan')->nullable();
            $table->string('kesimpulan')->nullable();
            $table->text('alasan')->nullable();
            $table->string('qr_code_path_auditee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'nama_jabatan',
                'nip',
                'bidang_tugas',
                'deskripsi_permasalahan',
                'tujuan_konsultasi',
                'waktu_penyelesaian',
                'pengungkapan',
                'review_permohonan',
                'kesimpulan',
                'alasan',
                'qr_code_path_auditee'
            ]);
        });
    }
};
