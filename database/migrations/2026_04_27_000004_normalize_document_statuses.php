<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE surat_masuk
            MODIFY status ENUM(
                'pending','done','disposed',
                'draft','pending_approval','revision','rejected','approved'
            ) NOT NULL DEFAULT 'pending'
        ");

        DB::statement("
            UPDATE surat_masuk
            SET status = CASE
                WHEN status = 'pending' THEN 'pending_approval'
                WHEN status = 'done' THEN 'approved'
                WHEN status = 'disposed' THEN 'approved'
                ELSE status
            END
        ");

        DB::statement("
            ALTER TABLE surat_masuk
            MODIFY status ENUM('draft','pending_approval','revision','rejected','approved')
            NOT NULL DEFAULT 'draft'
        ");

        DB::statement("
            ALTER TABLE surat_keluar
            MODIFY status ENUM(
                'review','revisi','final',
                'draft','pending_approval','revision','rejected','approved'
            ) NOT NULL DEFAULT 'draft'
        ");

        DB::statement("
            UPDATE surat_keluar
            SET status = CASE
                WHEN status = 'review' THEN 'pending_approval'
                WHEN status = 'revisi' THEN 'revision'
                WHEN status = 'final' THEN 'approved'
                ELSE status
            END
        ");

        DB::statement("
            ALTER TABLE surat_keluar
            MODIFY status ENUM('draft','pending_approval','revision','rejected','approved')
            NOT NULL DEFAULT 'draft'
        ");

        DB::statement("
            ALTER TABLE sppd
            MODIFY status ENUM(
                'review','aktif','selesai',
                'draft','pending_approval','revision','rejected','approved'
            ) NOT NULL DEFAULT 'draft'
        ");

        DB::statement("
            UPDATE sppd
            SET status = CASE
                WHEN status = 'review' THEN 'pending_approval'
                WHEN status = 'aktif' THEN 'approved'
                WHEN status = 'selesai' THEN 'approved'
                ELSE status
            END
        ");

        DB::statement("
            ALTER TABLE sppd
            MODIFY status ENUM('draft','pending_approval','revision','rejected','approved')
            NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE surat_masuk
            MODIFY status ENUM(
                'pending','done','disposed',
                'draft','pending_approval','revision','rejected','approved'
            ) NOT NULL DEFAULT 'draft'
        ");

        DB::statement("
            UPDATE surat_masuk
            SET status = CASE
                WHEN status = 'approved' THEN 'disposed'
                ELSE 'pending'
            END
        ");

        DB::statement("
            ALTER TABLE surat_masuk
            MODIFY status ENUM('pending','done','disposed')
            NOT NULL DEFAULT 'pending'
        ");

        DB::statement("
            ALTER TABLE surat_keluar
            MODIFY status ENUM(
                'review','revisi','final',
                'draft','pending_approval','revision','rejected','approved'
            ) NOT NULL DEFAULT 'draft'
        ");

        DB::statement("
            UPDATE surat_keluar
            SET status = CASE
                WHEN status = 'pending_approval' THEN 'review'
                WHEN status = 'revision' THEN 'revisi'
                WHEN status = 'rejected' THEN 'revisi'
                WHEN status = 'approved' THEN 'final'
                ELSE 'draft'
            END
        ");

        DB::statement("
            ALTER TABLE surat_keluar
            MODIFY status ENUM('draft','review','revisi','final')
            NOT NULL DEFAULT 'draft'
        ");

        DB::statement("
            ALTER TABLE sppd
            MODIFY status ENUM(
                'review','aktif','selesai',
                'draft','pending_approval','revision','rejected','approved'
            ) NOT NULL DEFAULT 'draft'
        ");

        DB::statement("
            UPDATE sppd
            SET status = CASE
                WHEN status = 'pending_approval' THEN 'review'
                WHEN status = 'revision' THEN 'review'
                WHEN status = 'rejected' THEN 'review'
                WHEN status = 'approved' THEN 'aktif'
                ELSE 'draft'
            END
        ");

        DB::statement("
            ALTER TABLE sppd
            MODIFY status ENUM('draft','review','aktif','selesai')
            NOT NULL DEFAULT 'draft'
        ");
    }
};
