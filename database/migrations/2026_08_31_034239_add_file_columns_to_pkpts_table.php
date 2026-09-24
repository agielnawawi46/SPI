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
        Schema::table('pkpts', function (Blueprint $table) {
            $table->string('file_name')->nullable()->after('description');
            $table->string('file_path')->nullable()->after('file_name');
            $table->unsignedBigInteger('file_size')->default(0)->after('file_path');
            $table->string('file_type', 100)->nullable()->after('file_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pkpts', function (Blueprint $table) {
            $table->dropColumn(['file_name', 'file_path', 'file_size', 'file_type']);
        });
    }
};
