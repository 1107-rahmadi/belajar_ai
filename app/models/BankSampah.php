<?php
/**
 * BankSampah Model
 *
 * Model untuk interaksi dengan tabel bank_sampah
 * Compatible dengan PHP 7.3+
 */

class BankSampah
{
    /** @var PDO */
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? getDbConnection();
    }

    /**
     * Ambil semua data bank sampah
     *
     * @param int|null $desaId Filter berdasarkan desa
     * @return array
     */
    public function getAll($desaId = null): array
    {
        $sql = "
            SELECT b.*, d.nama as desa_nama
            FROM bank_sampah b
            LEFT JOIN desa d ON b.desa_id = d.id
        ";
        $params = [];

        if ($desaId !== null) {
            $sql .= " WHERE b.desa_id = :desa_id";
            $params['desa_id'] = $desaId;
        }

        $sql .= " ORDER BY b.nama ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cari bank sampah berdasarkan ID
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT b.*, d.nama as desa_nama
            FROM bank_sampah b
            LEFT JOIN desa d ON b.desa_id = d.id
            WHERE b.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Buat bank sampah baru
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO bank_sampah (desa_id, nama, alamat, latitude, longitude, penanggung_jawab, no_hp, status)
            VALUES (:desa_id, :nama, :alamat, :latitude, :longitude, :penanggung_jawab, :no_hp, :status)
        ");

        return $stmt->execute([
            'desa_id' => $data['desa_id'],
            'nama' => $data['nama'],
            'alamat' => $data['alamat'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'penanggung_jawab' => $data['penanggung_jawab'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'status' => $data['status'] ?? 'AKTIF'
        ]);
    }

    /**
     * Update bank sampah
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE bank_sampah
            SET desa_id = :desa_id,
                nama = :nama,
                alamat = :alamat,
                latitude = :latitude,
                longitude = :longitude,
                penanggung_jawab = :penanggung_jawab,
                no_hp = :no_hp,
                status = :status
            WHERE id = :id
        ");

        $params = [
            'id' => $id,
            'desa_id' => $data['desa_id'],
            'nama' => $data['nama'],
            'alamat' => $data['alamat'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'penanggung_jawab' => $data['penanggung_jawab'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'status' => $data['status'] ?? 'AKTIF'
        ];

        return $stmt->execute($params);
    }

    /**
     * Hapus bank sampah
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM bank_sampah WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Hitung total bank sampah
     *
     * @param string|null $status Filter status
     * @return int
     */
    public function count($status = null): int
    {
        $sql = "SELECT COUNT(*) as total FROM bank_sampah";
        $params = [];

        if ($status !== null) {
            $sql .= " WHERE status = :status";
            $params['status'] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Ambil statistik bank sampah
     *
     * @return array
     */
    public function getStatistik(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'AKTIF' THEN 1 ELSE 0 END) as aktif,
                SUM(CASE WHEN status = 'NONAKTIF' THEN 1 ELSE 0 END) as nonaktif
            FROM bank_sampah
        ");
        return $stmt->fetch();
    }
}
