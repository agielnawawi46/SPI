<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkpt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkpt_id')->constrained('pkpts')->cascadeOnDelete();
            $table->string('audit_object');           // Objek audit
            $table->string('audit_objective')->nullable();  // Tujuan audit
            $table->text('audit_scope')->nullable();   // Ruang lingkup
            $table->enum('audit_type', ['terjadwal', 'tidak_terjadwal'])->default('terjadwal');
            $table->date('planned_start')->nullable();
            $table->date('planned_end')->nullable();
            $table->foreignId('auditee_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->integer('planned_days')->nullable(); // Jumlah hari audit
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkpt_items');
    }
};
