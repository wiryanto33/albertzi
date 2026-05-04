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
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropForeign(['assignment_id']);
            $table->dropUnique('daily_reports_assignment_id_unique');
            $table->unique(['assignment_id', 'tanggal'], 'daily_reports_assignment_tanggal_unique');
            $table->foreign('assignment_id')->references('id')->on('assignments')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropForeign(['assignment_id']);
            $table->dropUnique('daily_reports_assignment_tanggal_unique');
            $table->unique(['assignment_id']);
            $table->foreign('assignment_id')->references('id')->on('assignments')->cascadeOnDelete();
        });
    }
};
