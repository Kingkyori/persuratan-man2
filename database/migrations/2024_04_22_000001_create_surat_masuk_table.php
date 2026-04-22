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
        Schema::create('surat_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('origin')->comment('Asal/Pengirim Surat'); // e.g., Kemenag Kota
            $table->date('reception_date')->comment('Tanggal Penerimaan');
            $table->string('letter_number')->unique()->comment('Nomor Surat');
            $table->string('subject')->comment('Perihal/Isi Surat');
            $table->string('reference_number')->nullable()->comment('Nomor Referensi'); // e.g., SIK/KES-23.12.31
            $table->enum('status', ['pending', 'done', 'disposed'])->default('pending')->comment('Status Surat');
            $table->text('notes')->nullable()->comment('Catatan');
            $table->string('google_drive_link')->nullable()->comment('Link File di Google Drive');
            $table->string('file_name')->nullable()->comment('Nama File Original');
            $table->unsignedBigInteger('user_id')->nullable()->comment('User yang membuat entry');
            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Index
            $table->index('reception_date');
            $table->index('status');
            $table->index('origin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_masuk');
    }
};
