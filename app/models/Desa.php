<?php
/**
 * Desa Model
 *
 * Model untuk interaksi dengan tabel desa
 * Compatible dengan PHP 7.3+
 */

class Desa
{
    /** @var PDO */
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? getDbConnection();
    }

    /**
     * Ambil semua data desa
     *
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM desa ORDER BY nama ASC");
        return $stmt->fetchAll();
    }

    /**
     * Cari desa berdasarkan ID
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM desa WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Buat desa baru
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO desa (kode_wilayah, nama, kecamatan, kabupaten, provinsi)
            VALUES (:kode_wilayah, :nama, :kecamatan, :kabupaten, :provinsi)
        ");

        return $stmt->execute([
            'kode_wilayah' => $data['kode_wilayah'] ?? null,
            'nama' => $data['nama'],
            'kecamatan' => $data['kecamatan'],
            'kabupaten' => $data['kabupaten'],
            'provinsi' => $data['provinsi'] ?? 'Jawa Barat'
        ]);
    }

    /**
     * Update desa
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE desa
            SET kode_wilayah = :kode_wilayah,
                nama = :nama,
                kecamatan = :kecamatan,
                kabupaten = :kabupaten,
                provinsi = :provinsi
            WHERE id = :id
        ");

        $params = [
            'id' => $id,
            'kode_wilayah' => $data['kode_wilayah'] ?? null,
            'nama' => $data['nama'],
            'kecamatan' => $data['kecamatan'],
            'kabupaten' => $data['kabupaten'],
            'provinsi' => $data['provinsi'] ?? 'Jawa Barat'
        ];

        return $stmt->execute($params);
    }

    /**
     * Hapus desa
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM desa WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Hitung total desa
     *
     * @return int
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM desa");
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }
}
