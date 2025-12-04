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
        Schema::create('heavy_equipments', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('tipe')->nullable();
            $table->string('nopol')->nullable();
            $table->year('tahun')->nullable();
            $table->enum('status_kesiapan', ['SIAP', 'TIDAK_SIAP', 'PERBAIKAN'])->default('SIAP');
            $table->unsignedInteger('jam_jalan_total')->default(0);
            $table->date('last_service_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heavy_equipments');
    }
};
