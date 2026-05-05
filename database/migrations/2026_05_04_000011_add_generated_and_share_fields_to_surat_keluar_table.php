<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_keluar', 'entry_type')) {
                $table->string('entry_type')->default('upload')->after('destination');
            }

            if (!Schema::hasColumn('surat_keluar', 'document_type')) {
                $table->string('document_type')->nullable()->after('entry_type');
            }

            if (!Schema::hasColumn('surat_keluar', 'generated_payload')) {
                $table->json('generated_payload')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('surat_keluar', 'generated_docx_path')) {
                $table->string('generated_docx_path')->nullable()->after('local_file_path');
            }

            if (!Schema::hasColumn('surat_keluar', 'generated_docx_name')) {
                $table->string('generated_docx_name')->nullable()->after('generated_docx_path');
            }

            if (!Schema::hasColumn('surat_keluar', 'share_token')) {
                $table->string('share_token', 80)->nullable()->after('file_name');
            }
        });

        DB::table('surat_keluar')
            ->whereNull('share_token')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($record) {
                DB::table('surat_keluar')
                    ->where('id', $record->id)
                    ->update(['share_token' => (string) Str::uuid()]);
            });

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->index('entry_type');
            $table->index('document_type');
            $table->unique('share_token');
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropUnique(['share_token']);
            $table->dropIndex(['entry_type']);
            $table->dropIndex(['document_type']);
            $table->dropColumn([
                'entry_type',
                'document_type',
                'generated_payload',
                'generated_docx_path',
                'generated_docx_name',
                'share_token',
            ]);
        });
    }
};
