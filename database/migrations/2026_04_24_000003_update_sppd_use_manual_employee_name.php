<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sppd', function (Blueprint $table) {
            $table->string('employee_name')->nullable()->after('employee_id');
        });

        DB::table('sppd')
            ->leftJoin('users', 'sppd.employee_id', '=', 'users.id')
            ->whereNull('sppd.employee_name')
            ->update([
                'sppd.employee_name' => DB::raw('COALESCE(users.name, "-")'),
            ]);

        Schema::table('sppd', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        Schema::table('sppd', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->change();
            $table->index('employee_name');
        });

        Schema::table('sppd', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sppd', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        DB::table('sppd')
            ->leftJoin('users', 'sppd.employee_name', '=', 'users.name')
            ->whereNull('sppd.employee_id')
            ->update([
                'sppd.employee_id' => DB::raw('users.id'),
            ]);

        Schema::table('sppd', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable(false)->change();
            $table->dropIndex(['employee_name']);
            $table->dropColumn('employee_name');
        });

        Schema::table('sppd', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('users')->restrictOnDelete();
        });
    }
};
