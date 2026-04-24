<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->string('destination')->comment('Tujuan surat');
            $table->date('letter_date')->comment('Tanggal surat');
            $table->string('letter_number')->unique()->comment('Nomor surat keluar');
            $table->string('subject')->comment('Perihal surat');
            $table->enum('status', ['draft', 'review', 'revisi', 'final'])->default('draft');
            $table->text('notes')->nullable()->comment('Catatan tambahan');
            $table->string('google_drive_link')->nullable()->comment('Link file di Google Drive');
            $table->string('file_name')->nullable()->comment('Nama file asli');
            $table->unsignedBigInteger('user_id')->nullable()->comment('User yang membuat entry');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('letter_date');
            $table->index('status');
            $table->index('destination');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keluar');
    }
};
