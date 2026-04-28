<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login', function (Blueprint $table) {
            $table->string('role', 30)->default('admin')->after('password');
        });

        DB::table('login')
            ->where('username', 'kepsek')
            ->update(['role' => 'kepala_sekolah']);

        DB::table('login')
            ->where('username', '!=', 'kepsek')
            ->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('login', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
