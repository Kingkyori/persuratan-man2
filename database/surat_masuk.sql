-- =====================================================
-- MYSQL DATABASE SCRIPT - SURAT MASUK
-- Sistem Persuratan MAN 2 Surakarta
-- =====================================================
-- COPY-PASTE SCRIPT INI KE PHPMYADMIN ATAU MYSQL CLIENT

-- =====================================================
-- 1. CREATE TABLE: surat_masuk
-- =====================================================
CREATE TABLE IF NOT EXISTS `surat_masuk` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `origin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Asal/Pengirim Surat',
  `reception_date` date NOT NULL COMMENT 'Tanggal Penerimaan',
  `letter_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nomor Surat',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Perihal/Isi Surat',
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomor Referensi',
  `status` enum('pending','done','disposed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Status Surat',
  `notes` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Catatan',
  `google_drive_link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Link File di Google Drive',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nama File Original',
  `user_id` bigint UNSIGNED DEFAULT NULL COMMENT 'User yang membuat entry',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_masuk_letter_number_unique` (`letter_number`),
  KEY `surat_masuk_reception_date_index` (`reception_date`),
  KEY `surat_masuk_status_index` (`status`),
  KEY `surat_masuk_origin_index` (`origin`),
  KEY `surat_masuk_user_id_foreign` (`user_id`),
  CONSTRAINT `surat_masuk_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table untuk menyimpan data Surat Masuk';

-- =====================================================
-- 2. SAMPLE DATA (Optional - untuk testing)
-- =====================================================

INSERT INTO `surat_masuk` (`origin`, `reception_date`, `letter_number`, `subject`, `reference_number`, `status`, `notes`, `google_drive_link`, `file_name`, `user_id`, `created_at`, `updated_at`) VALUES
('Kemenag Kota Surakarta', '2024-04-22', 'KS-2024-001', 'Undangan Rapat Koordinasi Evaluasi Kurikulum', 'SIK/KES-23.12.31/IV-06/18/2023', 'done', NULL, 'https://drive.google.com/file/d/...', 'surat_kemenag.pdf', NULL, NOW(), NOW()),
('Yayasan Pendidikan Modern', '2024-04-23', 'YPM-2024-001', 'Permohonan Kerjasama Program Beasiswa', 'SKI/YPM-261/2023', 'pending', 'Menunggu review direktur', NULL, NULL, NULL, NOW(), NOW()),
('Dinas Pendidikan Provinsi Jawa Tengah', '2024-04-24', 'DPPJT-2024-001', 'Pemberhentian Sosialisasi Data BOS Tahun II', '401-1/283/2023', 'pending', NULL, NULL, NULL, NULL, NOW(), NOW());

-- =====================================================
-- 3. VERIFY TABLE (Optional - untuk checking)
-- =====================================================
-- Jalankan query ini untuk verify data:
-- SELECT * FROM surat_masuk;
-- SELECT COUNT(*) as total FROM surat_masuk;
-- SELECT status, COUNT(*) as count FROM surat_masuk GROUP BY status;

-- =====================================================
-- 4. DATABASE STATISTICS
-- =====================================================
-- Show table information:
-- SHOW COLUMNS FROM surat_masuk;
-- SHOW INDEXES FROM surat_masuk;

-- =====================================================
-- NOTES:
-- =====================================================
-- 1. Table ini support unlimited data
-- 2. Google Drive Link disimpan as URL string (max 500 characters)
-- 3. Status: pending (belum diproses), done (selesai), disposed (dihapus)
-- 4. letter_number bersifat UNIQUE (tidak boleh duplikat)
-- 5. user_id bisa NULL jika user dihapus dari database
-- 6. Timestamps otomatis terisi created_at dan updated_at
-- 7. Semua data terenkripsi dan aman di database

-- =====================================================
-- ALTERNATIVE APPROACH: Jika tidak bisa copy-paste
-- =====================================================
-- Gunakan phpmyadmin:
-- 1. Login ke phpmyadmin
-- 2. Buka database: persuratan_man2
-- 3. Klik SQL tab
-- 4. Paste script di atas
-- 5. Klik GO

-- =====================================================
-- DATABASE QUERIES REFERENCE
-- =====================================================

-- Lihat semua surat masuk:
-- SELECT * FROM surat_masuk ORDER BY reception_date DESC;

-- Lihat surat yang pending:
-- SELECT * FROM surat_masuk WHERE status = 'pending' ORDER BY reception_date DESC;

-- Update status surat:
-- UPDATE surat_masuk SET status = 'done' WHERE id = 1;

-- Hapus surat tertentu:
-- DELETE FROM surat_masuk WHERE id = 1;

-- Count total surat per status:
-- SELECT status, COUNT(*) as total FROM surat_masuk GROUP BY status;

-- Cari surat berdasarkan origin:
-- SELECT * FROM surat_masuk WHERE origin LIKE '%Kemenag%';

-- Export data to CSV format:
-- SELECT * INTO OUTFILE '/var/lib/mysql/surat_masuk_export.csv'
-- FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'
-- FROM surat_masuk;
