<?php
/**
 * Nasabah Model
 *
 * Model untuk interaksi dengan tabel nasabah
 * Compatible dengan PHP 7.3+
 */

class Nasabah
{
    /** @var PDO */
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? getDbConnection();
    }

    /**
     * Cari data Nasabah berdasarkan user_id
     *
     * @param int $userId
     * @return array|null
     */
    public function findByUserId(int $userId)
    {
        $stmt = $this->db->prepare("
            SELECT n.*, b.nama as bank_sampah_nama
            FROM nasabah n
            LEFT JOIN bank_sampah b ON n.bank_sampah_id = b.id
            WHERE n.user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Cari data Nasabah berdasarkan ID
     *
     * @param int $nasabahId
     * @return array|null
     */
    public function findById(int $nasabahId)
    {
        $stmt = $this->db->prepare("
            SELECT n.*, b.nama as bank_sampah_nama
            FROM nasabah n
            LEFT JOIN bank_sampah b ON n.bank_sampah_id = b.id
            WHERE n.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $nasabahId]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Ambil saldo tabungan Nasabah
     *
     * @param int $nasabahId
     * @return float
     */
    public function getSaldo(int $nasabahId): float
    {
        $stmt = $this->db->prepare("
            SELECT saldo
            FROM nasabah
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $nasabahId]);
        $result = $stmt->fetch();

        return (float) ($result['saldo'] ?? 0);
    }

    /**
     * Ambil saldo yang bisa dicairkan
     * Yaitu saldo dikurangi total pengajuan pencairan yang statusnya DIAJUKAN atau DISETUJUI
     *
     * @param int $nasabahId
     * @return float
     */
    public function getSaldoTersedia(int $nasabahId): float
    {
        // Ambil saldo saat ini
        $saldo = $this->getSaldo($nasabahId);

        // Hitung total pengajuan yang belum selesai (DIAJUKAN atau DISETUJUI)
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(jumlah), 0) as total_terikat
            FROM pengajuan_pencairan
            WHERE nasabah_id = :nasabah_id
            AND status IN ('DIAJUKAN', 'DISETUJUI')
        ");
        $stmt->execute(['nasabah_id' => $nasabahId]);
        $result = $stmt->fetch();

        $terikat = (float) ($result['total_terikat'] ?? 0);

        // Saldo tersedia = saldo - yang terikat pengajuan
        $tersedia = $saldo - $terikat;

        return max(0, $tersedia);
    }

    /**
     * Update saldo Nasabah
     *
     * @param int $nasabahId
     * @param float $jumlah Jumlah perubahan (positif=tambah, negatif=kurang)
     * @return bool
     */
    public function updateSaldo(int $nasabahId, float $jumlah): bool
    {
        $stmt = $this->db->prepare("
            UPDATE nasabah
            SET saldo = saldo + :jumlah
            WHERE id = :id
            AND (saldo + :jumlah2) >= 0
        ");
        return $stmt->execute([
            'jumlah' => $jumlah,
            'jumlah2' => $jumlah,
            'id' => $nasabahId
        ]);
    }

    /**
     * Set saldo absolute (untuk koreksi)
     *
     * @param int $nasabahId
     * @param float $saldoBaru
     * @return bool
     */
    public function setSaldo(int $nasabahId, float $saldoBaru): bool
    {
        if ($saldoBaru < 0) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE nasabah
            SET saldo = :saldo
            WHERE id = :id
        ");
        return $stmt->execute([
            'saldo' => $saldoBaru,
            'id' => $nasabahId
        ]);
    }

    /**
     * Ambil semua data Nasabah (dengan pagination)
     *
     * @param int|null $limit
     * @param int $offset
     * @param int|null $bankSampahId Filter berdasarkan bank sampah
     * @return array
     */
    public function getAll($limit = null, $offset = 0, $bankSampahId = null): array
    {
        $sql = "
            SELECT n.*, b.nama as bank_sampah_nama, u.no_hp as user_no_hp
            FROM nasabah n
            LEFT JOIN bank_sampah b ON n.bank_sampah_id = b.id
            LEFT JOIN users u ON n.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if ($bankSampahId !== null) {
            $sql .= " AND n.bank_sampah_id = :bank_sampah_id";
            $params['bank_sampah_id'] = $bankSampahId;
        }

        $sql .= " ORDER BY n.tgl_daftar DESC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params['limit'] = $limit;
            $params['offset'] = $offset;
        }

        $stmt = $this->db->prepare($sql);

        if ($limit !== null) {
            $stmt->bindValue('limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue('offset', (int) $offset, PDO::PARAM_INT);
        }

        foreach ($params as $key => $value) {
            if ($key !== 'limit' && $key !== 'offset') {
                $stmt->bindValue(":$key", $value);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hitung total Nasabah aktif
     *
     * @param int|null $bankSampahId
     * @return int
     */
    public function countAktif($bankSampahId = null): int
    {
        $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'NASABAH'";
        $params = [];

        if ($bankSampahId !== null) {
            $sql .= " AND bank_sampah_id = :bank_sampah_id";
            $params['bank_sampah_id'] = $bankSampahId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Buat Nasabah baru
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO nasabah (user_id, bank_sampah_id, no_anggota, nama, no_hp, nik, alamat, rt, rw, tipe, nama_bank, no_rekening, saldo, status, tgl_daftar)
            VALUES (:user_id, :bank_sampah_id, :no_anggota, :nama, :no_hp, :nik, :alamat, :rt, :rw, :tipe, :nama_bank, :no_rekening, :saldo, :status, :tgl_daftar)
        ");

        return $stmt->execute([
            'user_id' => $data['user_id'] ?? null,
            'bank_sampah_id' => $data['bank_sampah_id'],
            'no_anggota' => $data['no_anggota'],
            'nama' => $data['nama'],
            'no_hp' => $data['no_hp'],
            'nik' => $data['nik'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'rt' => $data['rt'] ?? null,
            'rw' => $data['rw'] ?? null,
            'tipe' => $data['tipe'] ?? 'PERORANGAN',
            'nama_bank' => $data['nama_bank'] ?? null,
            'no_rekening' => $data['no_rekening'] ?? null,
            'saldo' => $data['saldo'] ?? 0,
            'status' => $data['status'] ?? 'AKTIF',
            'tgl_daftar' => $data['tgl_daftar'] ?? date('Y-m-d'),
        ]);
    }

    /**
     * Update Nasabah
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE nasabah
            SET bank_sampah_id = :bank_sampah_id,
                no_anggota = :no_anggota,
                nama = :nama,
                no_hp = :no_hp,
                nik = :nik,
                alamat = :alamat,
                rt = :rt,
                rw = :rw,
                tipe = :tipe,
                nama_bank = :nama_bank,
                no_rekening = :no_rekening,
                status = :status
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'bank_sampah_id' => $data['bank_sampah_id'],
            'no_anggota' => $data['no_anggota'],
            'nama' => $data['nama'],
            'no_hp' => $data['no_hp'],
            'nik' => $data['nik'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'rt' => $data['rt'] ?? null,
            'rw' => $data['rw'] ?? null,
            'tipe' => $data['tipe'] ?? 'PERORANGAN',
            'nama_bank' => $data['nama_bank'] ?? null,
            'no_rekening' => $data['no_rekening'] ?? null,
            'status' => $data['status'] ?? 'AKTIF',
        ]);
    }

    /**
     * Hapus Nasabah
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM nasabah WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
