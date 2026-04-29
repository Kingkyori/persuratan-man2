<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sppd', function (Blueprint $table) {
            $table->string('entry_type')->default('upload')->after('employee_name');
            $table->string('document_number')->nullable()->after('entry_type');
            $table->date('document_date')->nullable()->after('departure_date');
            $table->json('assignment_payload')->nullable()->after('notes');
            $table->string('generated_docx_path')->nullable()->after('local_file_path');
            $table->string('generated_docx_name')->nullable()->after('generated_docx_path');

            $table->index('entry_type');
        });
    }

    public function down(): void
    {
        Schema::table('sppd', function (Blueprint $table) {
            $table->dropIndex(['entry_type']);
            $table->dropColumn([
                'entry_type',
                'document_number',
                'document_date',
                'assignment_payload',
                'generated_docx_path',
                'generated_docx_name',
            ]);
        });
    }
};
