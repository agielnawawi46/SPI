<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkpt_item_id')->nullable()->constrained('pkpt_items')->nullOnDelete();
            $table->string('assignment_number')->unique(); // Nomor Surat Tugas
            $table->string('title');
            $table->enum('audit_type', ['terjadwal', 'tidak_terjadwal'])->default('terjadwal');
            $table->json('auditor_ids');              // Array of user IDs
            $table->foreignId('auditee_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_assignments');
    }
};
