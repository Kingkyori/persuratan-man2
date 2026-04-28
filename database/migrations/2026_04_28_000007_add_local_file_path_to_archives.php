<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->string('local_file_path')->nullable()->after('google_drive_link');
        });

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->string('local_file_path')->nullable()->after('google_drive_link');
        });

        Schema::table('sppd', function (Blueprint $table) {
            $table->string('local_file_path')->nullable()->after('google_drive_link');
        });
    }

    public function down(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->dropColumn('local_file_path');
        });

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropColumn('local_file_path');
        });

        Schema::table('sppd', function (Blueprint $table) {
            $table->dropColumn('local_file_path');
        });
    }
};
