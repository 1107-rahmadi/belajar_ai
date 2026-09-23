<?php
/**
 * Setup Script: Add Nasabah Users
 * Jalankan: php database/add_nasabah.php
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = getDbConnection();

    echo "=== Setup Nasabah Users ===\n\n";

    $users = [
        [4, 'Budi Santoso', '081234567811', 'budi@banksampah.id'],
        [5, 'Siti Aminah', '081234567812', 'siti@banksampah.id'],
        [6, 'Warung Makan Sedap', '081234567813', 'warung@banksampah.id'],
    ];

    $hash = '$2y$10$7ojaY5Cu2tnAgf0s54qzguCAARv4S6osEE747z8mEWsVH66glP6MG';

    // 1. Insert user NASABAH
    echo "1. Inserting users...\n";
    $stmt = $db->prepare("
        INSERT INTO users (id, desa_id, bank_sampah_id, nama, no_hp, email, password_hash, role, status)
        VALUES (:id, 1, 1, :nama, :no_hp, :email, :hash, 'NASABAH', 'AKTIF')
        ON DUPLICATE KEY UPDATE nama = VALUES(nama), role = 'NASABAH'
    ");

    foreach ($users as $u) {
        $stmt->execute(['id' => $u[0], 'nama' => $u[1], 'no_hp' => $u[2], 'email' => $u[3], 'hash' => $hash]);
        echo "   - User {$u[0]}: {$u[1]}\n";
    }

    // 2. Link nasabah to users (use table name 'nasabah')
    echo "\n2. Linking nasabah to users...\n";
    $stmt2 = $db->prepare("UPDATE nasabah SET user_id = :user_id WHERE no_hp = :no_hp");

    foreach ($users as $u) {
        $stmt2->execute(['user_id' => $u[0], 'no_hp' => $u[2]]);
        echo "   - {$u[1]} -> user_id = {$u[0]}\n";
    }

    echo "\n=== DONE ===\n";
    echo "Login: 081234567811 / password123\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
