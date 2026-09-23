<?php
/**
 * KategoriSampah Model
 *
 * Model untuk interaksi dengan tabel kategori_sampah
 * Compatible dengan PHP 7.3+
 */

class KategoriSampah
{
    /** @var PDO */
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? getDbConnection();
    }

    /**
     * Ambil semua kategori sampah
     *
     * @param bool|null $aktif Filter berdasarkan status aktif
     * @return array
     */
    public function getAll($aktif = null): array
    {
        $sql = "SELECT * FROM kategori_sampah";
        $params = [];

        if ($aktif !== null) {
            $sql .= " WHERE aktif = :aktif";
            $params['aktif'] = $aktif ? 1 : 0;
        }

        $sql .= " ORDER BY jenis, nama ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cari kategori berdasarkan ID
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM kategori_sampah WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Buat kategori baru
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO kategori_sampah (nama, jenis, satuan, aktif)
            VALUES (:nama, :jenis, :satuan, :aktif)
        ");

        return $stmt->execute([
            'nama' => $data['nama'],
            'jenis' => $data['jenis'],
            'satuan' => $data['satuan'] ?? 'kg',
            'aktif' => isset($data['aktif']) ? ($data['aktif'] ? 1 : 0) : 1
        ]);
    }

    /**
     * Update kategori
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE kategori_sampah
            SET nama = :nama,
                jenis = :jenis,
                satuan = :satuan,
                aktif = :aktif
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'nama' => $data['nama'],
            'jenis' => $data['jenis'],
            'satuan' => $data['satuan'] ?? 'kg',
            'aktif' => isset($data['aktif']) ? ($data['aktif'] ? 1 : 0) : 1
        ]);
    }

    /**
     * Hapus kategori
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM kategori_sampah WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
