-- =====================================================
-- Patch: Add Nasabah Users
-- Bank Sampah Digital Desa
-- =====================================================

-- 1. Insert user Nasabah (mulai dari ID 4)
INSERT INTO `users` (`id`, `desa_id`, `bank_sampah_id`, `nama`, `no_hp`, `email`, `password_hash`, `role`, `status`) VALUES
(4, 1, 1, 'Budi Santoso', '081234567811', 'budi@banksampah.id', '$2y$10$7ojaY5Cu2tnAgf0s54qzguCAARv4S6osEE747z8mEWsVH66glP6MG', 'NASABAH', 'AKTIF'),
(5, 1, 1, 'Siti Aminah', '081234567812', 'siti@banksampah.id', '$2y$10$7ojaY5Cu2tnAgf0s54qzguCAARv4S6osEE747z8mEWsVH66glP6MG', 'NASABAH', 'AKTIF'),
(6, 1, 1, 'Warung Makan Sedap', '081234567813', 'warung@banksampah.id', '$2y$10$7ojaY5Cu2tnAgf0s54qzguCAARv4S6osEE747z8mEWsVH66glP6MG', 'NASABAH', 'AKTIF')
ON DUPLICATE KEY UPDATE
    nama = VALUES(nama),
    role = VALUES(role);

-- 2. Update user_id di tabel hidung untuk menghubungkan dengan user
UPDATE `nasabah` SET `user_id` = 4 WHERE `no_hp` = '081234567811';
UPDATE `nasabah` SET `user_id` = 5 WHERE `no_hp` = '081234567812';
UPDATE `nasabah` SET `user_id` = 6 WHERE `no_hp` = '081234567813';

-- =====================================================
-- Verifikasi:
-- Jalankan query ini untuk memastikan:
-- SELECT u.id, u.nama, u.no_hp, u.role, n.id as nariz_id, n.saldo
-- FROM users u
-- JOIN hidung n ON u.id = n.user_id
-- WHERE u.role = 'NASABAH';
-- =====================================================
