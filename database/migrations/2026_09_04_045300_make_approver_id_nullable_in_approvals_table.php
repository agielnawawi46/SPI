<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            // Make approver_id nullable so a pending approval record can be created
            // before any specific user acts on it.
            $table->foreignId('approver_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->foreignId('approver_id')->nullable(false)->change();
        });
    }
};
