-- =====================================================
-- Bank Sampah Digital Desa - Sample Data
-- Seeds: 001_sample_data.sql
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- 1. Sample: Desa
-- -----------------------------------------------------
INSERT INTO `desa` (`id`, `kode_wilayah`, `nama`, `kecamatan`, `kabupaten`, `provinsi`) VALUES
(1, '32.07.12.001', 'Sukamaju', 'Cianjur', 'Kabupaten Cianjur', 'Jawa Barat');

-- -----------------------------------------------------
-- 2. Sample: Bank Sampah
-- -----------------------------------------------------
INSERT INTO `bank_sampah` (`id`, `desa_id`, `nama`, `alamat`, `latitude`, `longitude`, `penanggung_jawab`, `no_hp`, `status`) VALUES
(1, 1, 'Bank Sampah Lestari', 'Jl. Raya Sukamaju No. 1', -6.8234567, 107.1234567, 'H. Ahmad Sutisna', '081234567890', 'AKTIF');

-- -----------------------------------------------------
-- 3. Sample: Users
-- Password default: "password123"
-- Hash bcrypt: password_hash('password123', PASSWORD_BCRYPT, ['cost' => 10])
-- Hash generated: $2y$10$7ojaY5Cu2tnAgf0s54qzguCAARv4S6osEE747z8mEWsVH66glP6MG
-- Login menggunakan no_hp + password
-- -----------------------------------------------------
INSERT INTO `users` (`id`, `desa_id`, `bank_sampah_id`, `nama`, `no_hp`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 1, 1, 'Administrator Sistem', '081234567801', 'admin@banksampah.id', '$2y$10$7ojaY5Cu2tnAgf0s54qzguCAARv4S6osEE747z8mEWsVH66glP6MG', 'ADMIN', 'AKTIF'),
(2, 1, 1, 'Dewi Karningsih', '081234567802', 'dewi@banksampah.id', '$2y$10$7ojaY5Cu2tnAgf0s54qzguCAARv4S6osEE747z8mEWsVH66glP6MG', 'PENGELOLA', 'AKTIF'),
(3, 1, NULL, 'H. Ujang Supriatna', '081234567803', 'kades@banksampah.id', '$2y$10$7ojaY5Cu2tnAgf0s54qzguCAARv4S6osEE747z8mEWsVH66glP6MG', 'KADES', 'AKTIF'),
-- User Nasabah
(4, 1, 1, 'Budi Santoso', '081234567811', 'budi@banksampah.id', '$2y$10$7ojaY5Cu2tnAgf0s54qzguCAARv4S6osEE747z8mEWsVH66glP6MG', 'NASABAH', 'AKTIF'),
(5, 1, 1, 'Siti Aminah', '081234567812', 'siti@banksampah.id', '$2y$10$7ojaY5Cu2tnAgf0s54qzguCAARv4S6osEE747z8mEWsVH66glP6MG', 'NASABAH', 'AKTIF'),
(6, 1, 1, 'Warung Makan Sedap', '081234567813', 'warung@banksampah.id', '$2y$10$7ojaY5Cu2tnAgf0s54qzguCAARv4S6osEE747z8mEWsVH66glP6MG', 'NASABAH', 'AKTIF');

-- -----------------------------------------------------
-- 4. Sample: Kategori Sampah
-- -----------------------------------------------------
INSERT INTO `kategori_sampah` (`id`, `nama`, `jenis`, `satuan`, `aktif`) VALUES
(1, 'Kertas Kardus', 'ANORGANIK', 'kg', TRUE),
(2, 'Plastik Botol', 'ANORGANIK', 'kg', TRUE),
(3, 'Plastik Kresek', 'ANORGANIK', 'kg', TRUE),
(4, 'Kaleng Bekas', 'ANORGANIK', 'kg', TRUE),
(5, 'Botol Kaca', 'ANORGANIK', 'kg', TRUE),
(6, 'Besi Logam', 'ANORGANIK', 'kg', TRUE),
(7, 'Seng', 'ANORGANIK', 'kg', TRUE),
(8, 'Kain Bekas', 'ANORGANIK', 'kg', TRUE),
(9, 'Daun Kering', 'ORGANIK', 'kg', TRUE),
(10, 'Sisa Makanan', 'ORGANIK', 'kg', TRUE);

-- -----------------------------------------------------
-- 5. Sample: Harga Sampah (aktif mulai hari ini)
-- -----------------------------------------------------
INSERT INTO `harga_sampah` (`id`, `bank_sampah_id`, `kategori_id`, `harga_beli`, `harga_jual`, `berlaku_mulai`, `aktif`) VALUES
-- Kertas Kardus
(1, 1, 1, 2500.00, 3500.00, CURDATE(), TRUE),
-- Plastik Botol
(2, 1, 2, 5000.00, 7000.00, CURDATE(), TRUE),
-- Plastik Kresek
(3, 1, 3, 1500.00, 2500.00, CURDATE(), TRUE),
-- Kaleng Bekas
(4, 1, 4, 4000.00, 5500.00, CURDATE(), TRUE),
-- Botol Kaca
(5, 1, 5, 2000.00, 3000.00, CURDATE(), TRUE),
-- Besi Logam
(6, 1, 6, 6000.00, 8500.00, CURDATE(), TRUE),
-- Seng
(7, 1, 7, 3500.00, 5000.00, CURDATE(), TRUE),
-- Kain Bekas
(8, 1, 8, 3000.00, 4500.00, CURDATE(), TRUE),
-- Daun Kering
(9, 1, 9, 500.00, 800.00, CURDATE(), TRUE),
-- Sisa Makanan
(10, 1, 10, 300.00, 500.00, CURDATE(), TRUE);

-- -----------------------------------------------------
-- 6. Sample: Nasabah
-- (user_id diisi untuk menghubungkan dengan tabel users)
-- -----------------------------------------------------
INSERT INTO `nasabah` (`id`, `user_id`, `bank_sampah_id`, `no_anggota`, `nama`, `no_hp`, `nik`, `alamat`, `rt`, `rw`, `tipe`, `nama_bank`, `no_rekening`, `saldo`, `status`, `tgl_daftar`) VALUES
(1, 4, 1, 'N-001', 'Budi Santoso', '081234567811', '3207123456780001', 'Jl. Mawar No. 5', '01', '03', 'PERORANGAN', 'Bank BRI', '1234567890', 125000.00, 'AKTIF', '2025-01-15'),
(2, 5, 1, 'N-002', 'Siti Aminah', '081234567812', '3207123456780002', 'Jl. Melati No. 12', '02', '03', 'PERORANGAN', 'Bank Mandiri', '9876543210', 87500.50, 'AKTIF', '2025-02-20'),
(3, 6, 1, 'N-003', 'Warung Makan Sedap', '081234567813', '3207123456780003', 'Jl. Utama No. 8', '01', '02', 'INSTANSI', 'Bank BCA', '5544332211', 250000.00, 'AKTIF', '2025-03-10');

-- -----------------------------------------------------
-- 7. Sample: Pengepul
-- -----------------------------------------------------
INSERT INTO `pengepul` (`id`, `bank_sampah_id`, `nama`, `no_hp`, `alamat`, `status`) VALUES
(1, 1, 'Toko Besi Jaya', '081987654321', 'Jl. Industri No. 5, Cianjur', 'AKTIF'),
(2, 1, 'Pak Hasan Recycling', '081987654322', 'Jl. Pasar Baru No. 10, Cianjur', 'AKTIF'),
(3, 1, 'UD. Daur Ulang', '081987654323', 'Kp. Nagreg, Bandung', 'AKTIF');

-- -----------------------------------------------------
-- 8. Sample: Transaksi Beli
-- -----------------------------------------------------
INSERT INTO `transaksi_beli` (`id`, `no_transaksi`, `bank_sampah_id`, `tipe_penjual`, `nasabah_id`, `nama_penjual`, `metode_bayar`, `tanggal`, `total_berat`, `total_nilai`, `petugas_id`, `status`, `catatan`) VALUES
-- Transaksi 1: Budi Santoso setor kardus & plastik
(1, 'TB-0001', 1, 'NASABAH', 1, NULL, 'TABUNG', '2025-09-01 08:30:00', 5.50, 16250.00, 2, 'SAH', 'Setoran pagi'),
-- Transaksi 2: Non-nasabah setor plastik kresek
(2, 'TB-0002', 1, 'NON_NASABAH', NULL, 'Ibu Jumiah', 'TUNAI', '2025-09-01 09:15:00', 2.00, 3000.00, 2, 'SAH', NULL),
-- Transaksi 3: Siti Aminah setor kaleng & besi
(3, 'TB-0003', 1, 'NASABAH', 2, NULL, 'TABUNG', '2025-09-01 10:00:00', 3.20, 18800.00, 2, 'SAH', NULL),
-- Transaksi 4: Warung Makan Sedap setor kardus
(4, 'TB-0004', 1, 'NASABAH', 3, NULL, 'TABUNG', '2025-09-02 07:45:00', 8.00, 20000.00, 2, 'SAH', 'Kardus dari resto');

-- -----------------------------------------------------
-- 9. Sample: Detail Beli
-- -----------------------------------------------------
INSERT INTO `detail_beli` (`id`, `transaksi_beli_id`, `kategori_id`, `berat_kg`, `harga_per_kg`, `subtotal`) VALUES
-- TB-0001: Kardus 3kg + Plastik Botol 2.5kg
(1, 1, 1, 3.00, 2500.00, 7500.00),
(2, 1, 2, 2.50, 3500.00, 8750.00),
-- TB-0002: Plastik Kresek 2kg
(3, 2, 3, 2.00, 1500.00, 3000.00),
-- TB-0003: Kaleng 1.2kg + Besi 2kg
(4, 3, 4, 1.20, 4000.00, 4800.00),
(5, 3, 6, 2.00, 7000.00, 14000.00),
-- TB-0004: Kardus 8kg
(6, 4, 1, 8.00, 2500.00, 20000.00);

-- -----------------------------------------------------
-- 10. Sample: Stok Sampah (hasil dari transaksi beli)
-- -----------------------------------------------------
INSERT INTO `stok_sampah` (`bank_sampah_id`, `kategori_id`, `berat_kg`) VALUES
(1, 1, 11.00),   -- Kardus: 3+8 = 11kg
(1, 2, 2.50),    -- Plastik Botol: 2.5kg
(1, 3, 2.00),    -- Plastik Kresek: 2kg
(1, 4, 1.20),    -- Kaleng: 1.2kg
(1, 6, 2.00);    -- Besi: 2kg

-- -----------------------------------------------------
-- 11. Sample: Mutasi Saldo
-- -----------------------------------------------------
INSERT INTO `mutasi_saldo` (`id`, `nasabah_id`, `tipe`, `jumlah`, `saldo_setelah`, `referensi_tipe`, `referensi_id`, `keterangan`, `created_at`) VALUES
-- Budi Santoso
(1, 1, 'SETOR', 7500.00, 7500.00, 'transaksi_beli', 1, 'TB-0001', '2025-09-01 08:30:00'),
(2, 1, 'SETOR', 8750.00, 16250.00, 'transaksi_beli', 1, 'TB-0001', '2025-09-01 08:30:00'),
-- Siti Aminah
(3, 2, 'SETOR', 4800.00, 4800.00, 'transaksi_beli', 3, 'TB-0003', '2025-09-01 10:00:00'),
(4, 2, 'SETOR', 14000.00, 18800.00, 'transaksi_beli', 3, 'TB-0003', '2025-09-01 10:00:00'),
-- Warung Sedap
(5, 3, 'SETOR', 20000.00, 20000.00, 'transaksi_beli', 4, 'TB-0004', '2025-09-02 07:45:00'),
(6, 3, 'SETOR', 20000.00, 40000.00, 'transaksi_beli', 4, 'TB-0004', '2025-09-02 07:45:00'),
(7, 3, 'SETOR', 15000.00, 55000.00, 'transaksi_beli', 4, 'TB-0004', '2025-09-03 08:00:00'),
(8, 3, 'SETOR', 25000.00, 80000.00, 'transaksi_beli', 4, 'TB-0004', '2025-09-04 08:30:00'),
(9, 3, 'SETOR', 10000.00, 90000.00, 'transaksi_beli', 4, 'TB-0004', '2025-09-05 09:00:00'),
(10, 3, 'SETOR', 5000.00, 95000.00, 'transaksi_beli', 4, 'TB-0004', '2025-09-06 07:30:00'),
(11, 3, 'SETOR', 50000.00, 145000.00, 'transaksi_beli', 4, 'TB-0004', '2025-09-07 08:00:00'),
(12, 3, 'SETOR', 30000.00, 175000.00, 'transaksi_beli', 4, 'TB-0004', '2025-09-08 08:30:00'),
(13, 3, 'SETOR', 15000.00, 190000.00, 'transaksi_beli', 4, 'TB-0004', '2025-09-09 09:00:00'),
(14, 3, 'SETOR', 10000.00, 200000.00, 'transaksi_beli', 4, 'TB-0004', '2025-09-10 07:45:00'),
(15, 3, 'SETOR', 20000.00, 220000.00, 'transaksi_beli', 4, 'TB-0004', '2025-09-11 08:15:00'),
(16, 3, 'SETOR', 15000.00, 235000.00, 'transaksi_beli', 4, 'TB-0004', '2025-09-12 08:45:00'),
(17, 3, 'SETOR', 15000.00, 250000.00, 'transaksi_beli', 4, 'TB-0004', '2025-09-13 09:15:00'),
-- Koreksi saldo manual untuk Budi dan Siti
(18, 1, 'KOREKSI', 109750.00, 125000.00, NULL, NULL, 'Saldo awal periode 2025', '2025-01-01 00:00:00'),
(19, 2, 'KOREKSI', 68900.50, 87500.50, NULL, NULL, 'Saldo awal periode 2025', '2025-01-01 00:00:00');

-- -----------------------------------------------------
-- 12. Sample: Transaksi Jual
-- -----------------------------------------------------
INSERT INTO `transaksi_jual` (`id`, `no_transaksi`, `bank_sampah_id`, `pengepul_id`, `petugas_id`, `tanggal`, `total_berat`, `total_nilai`, `status_bayar`, `status`, `catatan`) VALUES
(1, 'TJ-0001', 1, 1, 2, '2025-09-10 14:00:00', 5.50, 19250.00, 'LUNAS', 'SAH', 'Jual kardus ke Toko Besi Jaya'),
(2, 'TJ-0002', 1, 2, 2, '2025-09-12 15:30:00', 3.20, 22400.00, 'BELUM', 'SAH', 'Jual kaleng & besi');

-- -----------------------------------------------------
-- 13. Sample: Detail Jual
-- -----------------------------------------------------
INSERT INTO `detail_jual` (`id`, `transaksi_jual_id`, `kategori_id`, `berat_kg`, `harga_per_kg`, `subtotal`) VALUES
-- TJ-0001: Kardus 5.5kg @3500
(1, 1, 1, 5.50, 3500.00, 19250.00),
-- TJ-0002: Kaleng 1.2kg @5500 + Besi 2kg @8450
(2, 2, 4, 1.20, 5500.00, 6600.00),
(3, 2, 6, 2.00, 7900.00, 15800.00);

-- -----------------------------------------------------
-- 14. Sample: Pengajuan Pencairan
-- -----------------------------------------------------
INSERT INTO `pengajuan_pencairan` (`id`, `no_pengajuan`, `nasabah_id`, `jumlah`, `metode`, `tujuan_transfer`, `status`, `alasan_tolak`, `diajukan_at`, `diproses_at`, `dicairkan_at`, `diproses_oleh`) VALUES
(1, 'PC-0001', 1, 50000.00, 'TUNAI', NULL, 'DICAIRKAN', NULL, '2025-09-05 10:00:00', '2025-09-05 11:00:00', '2025-09-05 11:30:00', 2),
(2, 'PC-0002', 2, 25000.00, 'TRANSFER', 'Mandiri 9876543210 a.n. Siti Aminah', 'DISETUJUI', NULL, '2025-09-08 09:00:00', '2025-09-08 10:00:00', NULL, 2),
(3, 'PC-0003', 3, 75000.00, 'TRANSFER', 'BCA 5544332211 a.n. Warung Makan Sedap', 'DIAJUKAN', NULL, '2025-09-13 08:00:00', NULL, NULL, NULL);

-- -----------------------------------------------------
-- 15. Sample: Notifikasi
-- -----------------------------------------------------
INSERT INTO `notifikasi` (`id`, `user_id`, `judul`, `isi`, `tipe`, `dibaca_at`, `created_at`) VALUES
(1, 1, 'Selamat Datang', 'Akun Anda telah dibuat sebagai Administrator. Segera ubah password Anda.', 'INFO', NULL, NOW()),
(2, 2, 'Transaksi Baru', 'Ada setoran sampah baru dari Budi Santoso sebesar Rp 16.250', 'TRANSAKSI', NULL, NOW()),
(3, 3, 'Laporan Bulanan', 'Laporan kegiatan Bank Sampah Lestari bulan September 2025 tersedia.', 'LAPORAN', NULL, NOW());

-- -----------------------------------------------------
-- 16. Sample: Audit Log
-- -----------------------------------------------------
INSERT INTO `audit_log` (`id`, `user_id`, `aksi`, `tabel`, `record_id`, `sebelum`, `sesudah`, `created_at`) VALUES
(1, 1, 'INSERT', 'users', 1, NULL, '{"nama":"Administrator Sistem","role":"ADMIN"}', NOW()),
(2, 1, 'INSERT', 'users', 2, NULL, '{"nama":"Dewi Karningsih","role":"PENGELOLA"}', NOW()),
(3, 2, 'INSERT', 'transaksi_beli', 1, NULL, '{"no_transaksi":"TB-0001","total_nilai":16250}', NOW()),
(4, 2, 'INSERT', 'transaksi_beli', 2, NULL, '{"no_transaksi":"TB-0002","total_nilai":3000}', NOW()),
(5, 2, 'INSERT', 'transaksi_beli', 3, NULL, '{"no_transaksi":"TB-0003","total_nilai":18800}', NOW());

-- -----------------------------------------------------
-- Enable foreign key checks
-- -----------------------------------------------------
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- Catatan Login:
-- Semua user sample menggunakan password: password123
--
-- Login menggunakan No HP + Password:
-- +-------------------+------------+------------+
-- | Role      | No HP        | Password    |
-- +-------------------+------------+------------+
-- | Admin     | 081234567801 | password123 |
-- | Pengelola  | 081234567802 | password123 |
-- | Kades     | 081234567803 | password123 |
-- | Nasabah   | 081234567811 | password123 | (Budi Santoso)
-- | Nasabah   | 081234567812 | password123 | (Siti Aminah)
-- | Nasabah   | 081234567813 | password123 | (Warung Makan Sedap)
-- +-------------------+------------+------------+
--
-- End of Seeds: 001_sample_data.sql
-- =====================================================
