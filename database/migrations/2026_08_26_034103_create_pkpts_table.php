<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkpts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->year('period_year');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'pending_kspi', 'pending_direktur', 'approved', 'rejected'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('qr_code_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkpts');
    }
};
