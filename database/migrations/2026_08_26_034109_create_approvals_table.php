<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            // Polymorphic: can approve Document, FollowUp, Pkpt, etc.
            $table->morphs('approvable');
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', ['approve', 'reject'])->nullable();
            $table->text('comments')->nullable();
            $table->tinyInteger('approval_level'); // 1 = KSPI, 2 = Direktur
            $table->string('approval_role');       // Role required to approve at this level
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
