<?php
/**
 * MutasiSaldo Model
 *
 * Model untuk interaksi dengan tabel mutasi_saldo
 * Compatible dengan PHP 7.3+
 */

class MutasiSaldo
{
    /** @var PDO */
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? getDbConnection();
    }

    /**
     * Ambil riwayat mutasi saldo Nasabah
     *
     * @param int $nasabahId
     * @param int $limit Batas jumlah data (default 20)
     * @param int $offset Offset untuk pagination (default 0)
     * @return array
     */
    public function getRiwayat(int $nasabahId, $limit = 20, $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                tipe,
                jumlah,
                saldo_setelah,
                referensi_tipe,
                referensi_id,
                keterangan,
                created_at
            FROM mutasi_saldo
            WHERE nasabah_id = :nasabah_id
            ORDER BY created_at DESC, id DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue('nasabah_id', $nasabahId, PDO::PARAM_INT);
        $stmt->bindValue('limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ambil riwayat mutasi saldo dengan informasi referensi
     *
     * @param int $nasabahId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getRiwayatLengkap(int $nasabahId, $limit = 20, $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT
                m.id,
                m.tipe,
                m.jumlah,
                m.saldo_setelah,
                m.referensi_tipe,
                m.referensi_id,
                m.keterangan,
                m.created_at,
                CASE
                    WHEN m.referensi_tipe = 'transaksi_beli' THEN CONCAT('Transaksi Beli - ', tb.no_transaksi)
                    WHEN m.referensi_tipe = 'pengajuan_pencairan' THEN CONCAT('Pencairan - ', pp.no_pengajuan)
                    WHEN m.referensi_tipe = 'koreksi' THEN 'Koreksi Saldo'
                    ELSE m.keterangan
                END as keterangan_lengkap
            FROM mutasi_saldo m
            LEFT JOIN transaksi_beli tb ON m.referensi_tipe = 'transaksi_beli' AND m.referensi_id = tb.id
            LEFT JOIN pengajuan_pencairan pp ON m.referensi_tipe = 'pengajuan_pencairan' AND m.referensi_id = pp.id
            WHERE m.nasabah_id = :nasabah_id
            ORDER BY m.created_at DESC, m.id DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue('nasabah_id', $nasabahId, PDO::PARAM_INT);
        $stmt->bindValue('limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hitung total mutasi berdasarkan tipe
     *
     * @param int $nasabahId
     * @param string $tipe SETOR, PENCAIRAN, KOREKSI
     * @return float
     */
    public function getTotalByTipe(int $nasabahId, string $tipe): float
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(jumlah), 0) as total
            FROM mutasi_saldo
            WHERE nasabah_id = :nasabah_id
            AND tipe = :tipe
        ");
        $stmt->execute([
            'nasabah_id' => $nasabahId,
            'tipe' => $tipe
        ]);
        $result = $stmt->fetch();

        return (float) ($result['total'] ?? 0);
    }

    /**
     * Buat record mutasi saldo baru
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        // Validasi field yang wajib ada
        $requiredFields = ['nasabah_id', 'tipe', 'jumlah', 'saldo_setelah'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new InvalidArgumentException("Field '$field' wajib diisi untuk MutasiSaldo::create()");
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO mutasi_saldo
            (nasabah_id, tipe, jumlah, saldo_setelah, referensi_tipe, referensi_id, keterangan)
            VALUES
            (:nasabah_id, :tipe, :jumlah, :saldo_setelah, :referensi_tipe, :referensi_id, :keterangan)
        ");

        $params = [
            'nasabah_id' => (int) $data['nasabah_id'],
            'tipe' => trim($data['tipe']),
            'jumlah' => (float) $data['jumlah'],
            'saldo_setelah' => (float) $data['saldo_setelah'],
            'referensi_tipe' => $data['referensi_tipe'] ?? null,
            'referensi_id' => isset($data['referensi_id']) ? (int) $data['referensi_id'] : null,
            'keterangan' => $data['keterangan'] ?? null
        ];

        return $stmt->execute($params);
    }

    /**
     * Ambil mutasi berdasarkan ID
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM mutasi_saldo WHERE id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Hitung total record mutasi untuk pagination
     *
     * @param int $nasabahId
     * @return int
     */
    public function countRiwayat(int $nasabahId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM mutasi_saldo
            WHERE nasabah_id = :nasabah_id
        ");
        $stmt->execute(['nasabah_id' => $nasabahId]);
        $result = $stmt->fetch();

        return (int) ($result['total'] ?? 0);
    }
}
