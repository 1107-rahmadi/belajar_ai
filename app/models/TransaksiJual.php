<?php
/**
 * TransaksiJual Model
 *
 * Model untuk interaksi dengan tabel transaksi_jual
 * Compatible dengan PHP 7.3+
 */

class TransaksiJual
{
    /** @var PDO */
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? getDbConnection();
    }

    /**
     * Generate nomor transaksi
     *
     * @return string
     */
    public function generateNoTrans(): string
    {
        $prefix = 'TJ-' . date('ymd');
        $stmt = $this->db->prepare("
            SELECT no_transaksi FROM transaksi_jual
            WHERE no_transaksi LIKE :prefix
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetch();

        if ($last) {
            $num = (int) substr($last['no_transaksi'], -4) + 1;
        } else {
            $num = 1;
        }

        return $prefix . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Buat transaksi jual baru
     *
     * @param array $data
     * @param array $items
     * @return int|false
     */
    public function create(array $data, array $items = [])
    {
        $this->db->beginTransaction();

        try {
            $noTrans = $this->generateNoTrans();

            $stmt = $this->db->prepare("
                INSERT INTO transaksi_jual
                (no_transaksi, bank_sampah_id, pengepul_id, petugas_id, tanggal, total_berat, total_nilai, status_bayar, status, catatan)
                VALUES
                (:no_trans, :bank_id, :pengepul_id, :petugas_id, :tanggal, :total_berat, :total_nilai, :status_bayar, :status, :catatan)
            ");

            $stmt->execute([
                'no_trans' => $noTrans,
                'bank_id' => $data['bank_sampah_id'],
                'pengepul_id' => $data['pengepul_id'],
                'petugas_id' => $data['petugas_id'],
                'tanggal' => $data['tanggal'] ?? date('Y-m-d H:i:s'),
                'total_berat' => $data['total_berat'],
                'total_nilai' => $data['total_nilai'],
                'status_bayar' => $data['status_bayar'] ?? 'BELUM',
                'status' => $data['status'] ?? 'SAH',
                'catatan' => $data['catatan'] ?? null
            ]);

            $transaksiId = $this->db->lastInsertId();

            // Insert detail items
            if (!empty($items)) {
                $stmtDetail = $this->db->prepare("
                    INSERT INTO detail_jual (transaksi_jual_id, kategori_id, berat_kg, harga_per_kg, subtotal)
                    VALUES (:trans_id, :kategori_id, :berat, :harga, :subtotal)
                ");

                foreach ($items as $item) {
                    $stmtDetail->execute([
                        'trans_id' => $transaksiId,
                        'kategori_id' => $item['kategori_id'],
                        'berat' => $item['berat_kg'],
                        'harga' => $item['harga_per_kg'],
                        'subtotal' => $item['subtotal']
                    ]);
                }

                // Kurangi stok
                $stmtStok = $this->db->prepare("
                    UPDATE stok_sampah
                    SET berat_kg = berat_kg - :berat
                    WHERE bank_sampah_id = :bank_id AND kategori_id = :kategori_id
                ");

                foreach ($items as $item) {
                    $stmtStok->execute([
                        'berat' => $item['berat_kg'],
                        'bank_id' => $data['bank_sampah_id'],
                        'kategori_id' => $item['kategori_id']
                    ]);
                }
            }

            $this->db->commit();
            return $transaksiId;

        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Ambil transaksi dengan detail
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT tj.*, b.nama as bank_nama, u.nama as petugas_nama,
                   p.nama as pengepul_nama, p.no_hp as pengepul_hp
            FROM transaksi_jual tj
            LEFT JOIN bank_sampah b ON tj.bank_sampah_id = b.id
            LEFT JOIN users u ON tj.petugas_id = u.id
            LEFT JOIN pengepul p ON tj.pengepul_id = p.id
            WHERE tj.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        if ($result) {
            $stmtDetail = $this->db->prepare("
                SELECT dj.*, k.nama as kategori_nama, k.jenis
                FROM detail_jual dj
                JOIN kategori_sampah k ON dj.kategori_id = k.id
                WHERE dj.transaksi_jual_id = :id
            ");
            $stmtDetail->execute(['id' => $id]);
            $result['items'] = $stmtDetail->fetchAll();
        }

        return $result ?: null;
    }

    /**
     * Ambil transaksi berdasarkan bank sampah
     *
     * @param int $bankSampahId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByBankSampah($bankSampahId, $limit = 50, $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT tj.*, b.nama as bank_nama, u.nama as petugas_nama,
                   p.nama as pengepul_nama
            FROM transaksi_jual tj
            LEFT JOIN bank_sampah b ON tj.bank_sampah_id = b.id
            LEFT JOIN users u ON tj.petugas_id = u.id
            LEFT JOIN pengepul p ON tj.pengepul_id = p.id
            WHERE tj.bank_sampah_id = :bank_id
            ORDER BY tj.tanggal DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue('bank_id', $bankSampahId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Statistik transaksi jual
     *
     * @param int $bankSampahId
     * @return array
     */
    public function getStatistik($bankSampahId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total_transaksi,
                COALESCE(SUM(total_berat), 0) as total_berat,
                COALESCE(SUM(total_nilai), 0) as total_nilai,
                COALESCE(SUM(CASE WHEN status_bayar = 'LUNAS' THEN total_nilai ELSE 0 END), 0) as total_lunas,
                COALESCE(SUM(CASE WHEN status_bayar = 'BELUM' THEN total_nilai ELSE 0 END), 0) as total_belum_bayar
            FROM transaksi_jual
            WHERE bank_sampah_id = :bank_id AND status = 'SAH'
        ");
        $stmt->execute(['bank_id' => $bankSampahId]);
        return $stmt->fetch();
    }
}
