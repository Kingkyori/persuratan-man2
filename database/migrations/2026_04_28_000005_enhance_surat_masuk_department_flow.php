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
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->string('department_destination')->nullable()->after('subject');
            $table->enum('department_status', ['pending', 'received'])->default('pending')->after('status');
            $table->text('department_notes')->nullable()->after('notes');
            $table->string('share_token', 80)->nullable()->after('file_name');
        });

        DB::table('surat_masuk')
            ->select('id')
            ->orderBy('id')
            ->get()
            ->each(function ($record) {
                DB::table('surat_masuk')
                    ->where('id', $record->id)
                    ->update([
                        'share_token' => (string) Str::uuid(),
                    ]);
            });

        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->unique('share_token');
            $table->index('department_status');
        });
    }

    public function down(): void
    {
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->dropUnique(['share_token']);
            $table->dropIndex(['department_status']);
            $table->dropColumn([
                'department_destination',
                'department_status',
                'department_notes',
                'share_token',
            ]);
        });
    }
};
