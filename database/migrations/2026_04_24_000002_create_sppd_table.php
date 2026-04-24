<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sppd', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->comment('Pegawai tujuan SPPD');
            $table->string('destination')->comment('Tujuan perjalanan dinas');
            $table->date('departure_date')->comment('Tanggal berangkat');
            $table->unsignedInteger('duration_days')->comment('Durasi perjalanan dalam hari');
            $table->text('purpose')->comment('Kepentingan dinas');
            $table->enum('status', ['draft', 'review', 'aktif', 'selesai'])->default('draft');
            $table->text('notes')->nullable()->comment('Catatan tambahan');
            $table->string('google_drive_link')->nullable()->comment('Link file di Google Drive');
            $table->string('file_name')->nullable()->comment('Nama file asli');
            $table->unsignedBigInteger('user_id')->nullable()->comment('User yang membuat entry');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('departure_date');
            $table->index('status');
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sppd');
    }
};
