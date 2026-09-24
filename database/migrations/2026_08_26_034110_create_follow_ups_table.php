<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete(); // Related LHA
            $table->foreignId('auditee_department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('finding');          // Temuan audit
            $table->text('recommendation');     // Rekomendasi SPI
            $table->date('due_date');           // Batas waktu TL
            $table->enum('status', [
                'open',         // Belum ditindaklanjuti
                'in_progress',  // Dalam proses
                'completed',    // Selesai (sudah diapprove)
                'overdue',      // Melewati jatuh tempo
            ])->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // Auditee PIC
            $table->enum('approval_status', [
                'pending',           // Menunggu auditee upload
                'pending_auditor',   // Menunggu review auditor
                'pending_kspi',      // Menunggu KSPI approve
                'approved',          // Selesai
                'rejected',          // Dikembalikan
            ])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
