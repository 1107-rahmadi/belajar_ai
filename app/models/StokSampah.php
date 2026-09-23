<?php
/**
 * StokSampah Model
 *
 * Model untuk interaksi dengan tabel stok_sampah
 * Compatible dengan PHP 7.3+
 */

class StokSampah
{
    /** @var PDO */
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? getDbConnection();
    }

    /**
     * Ambil stok untuk bank sampah
     *
     * @param int $bankSampahId
     * @return array
     */
    public function getByBankSampah($bankSampahId): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, k.nama as kategori_nama, k.jenis, k.satuan
            FROM stok_sampah s
            JOIN kategori_sampah k ON s.kategori_id = k.id
            WHERE s.bank_sampah_id = :bank_id
            ORDER BY k.jenis, k.nama ASC
        ");
        $stmt->execute(['bank_id' => $bankSampahId]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil stok untuk satu kategori
     *
     * @param int $bankSampahId
     * @param int $kategoriId
     * @return float
     */
    public function getStok($bankSampahId, $kategoriId): float
    {
        $stmt = $this->db->prepare("
            SELECT berat_kg FROM stok_sampah
            WHERE bank_sampah_id = :bank_id AND kategori_id = :kategori_id
            LIMIT 1
        ");
        $stmt->execute(['bank_id' => $bankSampahId, 'kategori_id' => $kategoriId]);
        $result = $stmt->fetch();
        return $result ? (float) $result['berat_kg'] : 0;
    }

    /**
     * Tambah stok
     *
     * @param int $bankSampahId
     * @param int $kategoriId
     * @param float $berat
     * @return bool
     */
    public function tambah($bankSampahId, $kategoriId, $berat): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO stok_sampah (bank_sampah_id, kategori_id, berat_kg)
            VALUES (:bank_id, :kategori_id, :berat)
            ON DUPLICATE KEY UPDATE berat_kg = berat_kg + :berat2
        ");
        return $stmt->execute([
            'bank_id' => $bankSampahId,
            'kategori_id' => $kategoriId,
            'berat' => $berat,
            'berat2' => $berat
        ]);
    }

    /**
     * Kurangi stok
     *
     * @param int $bankSampahId
     * @param int $kategoriId
     * @param float $berat
     * @return bool
     */
    public function kurang($bankSampahId, $kategoriId, $berat): bool
    {
        $stmt = $this->db->prepare("
            UPDATE stok_sampah
            SET berat_kg = GREATEST(0, berat_kg - :berat)
            WHERE bank_sampah_id = :bank_id AND kategori_id = :kategori_id
        ");
        return $stmt->execute([
            'bank_id' => $bankSampahId,
            'kategori_id' => $kategoriId,
            'berat' => $berat
        ]);
    }
}
