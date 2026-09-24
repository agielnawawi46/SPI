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
        Schema::table('pkpt_items', function (Blueprint $table) {
            $table->string('custom_auditee_name')->nullable()->after('auditee_department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pkpt_items', function (Blueprint $table) {
            $table->dropColumn('custom_auditee_name');
        });
    }
};
