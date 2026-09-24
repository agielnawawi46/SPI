<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();           // Public identifier for QR Code
            $table->string('document_number')->nullable();
            $table->foreignId('document_type_id')->constrained('document_types')->restrictOnDelete();
            $table->foreignId('audit_assignment_id')->nullable()->constrained('audit_assignments')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();    // CKEditor 5 HTML content
            $table->enum('status', [
                'draft',
                'pending_kspi',
                'pending_direktur',
                'approved',
                'rejected',
                'revised',
            ])->default('draft');
            $table->string('qr_code_path')->nullable(); // Generated after final approval
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
