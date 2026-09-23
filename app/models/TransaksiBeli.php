<?php
/**
 * TransaksiBeli Model
 *
 * Model untuk interaksi dengan tabel transaksi_beli
 * Compatible dengan PHP 7.3+
 */

class TransaksiBeli
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
        $prefix = 'TB-' . date('ymd');
        $stmt = $this->db->prepare("
            SELECT no_transaksi FROM transaksi_beli
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
     * Buat transaksi beli baru
     *
     * @param array $data
     * @param array $items Array of item details
     * @return int|false
     */
    public function create(array $data, array $items = [])
    {
        $this->db->beginTransaction();

        try {
            $noTrans = $this->generateNoTrans();

            $stmt = $this->db->prepare("
                INSERT INTO transaksi_beli
                (no_transaksi, bank_sampah_id, tipe_penjual, nasabah_id, nama_penjual, metode_bayar, tanggal, total_berat, total_nilai, petugas_id, status, catatan)
                VALUES
                (:no_trans, :bank_id, :tipe, :nasabah_id, :nama_penjual, :metode, :tanggal, :total_berat, :total_nilai, :petugas_id, :status, :catatan)
            ");

            $stmt->execute([
                'no_trans' => $noTrans,
                'bank_id' => $data['bank_sampah_id'],
                'tipe' => $data['tipe_penjual'],
                'nasabah_id' => $data['nasabah_id'] ?? null,
                'nama_penjual' => $data['nama_penjual'] ?? null,
                'metode' => $data['metode_bayar'],
                'tanggal' => $data['tanggal'] ?? date('Y-m-d H:i:s'),
                'total_berat' => $data['total_berat'],
                'total_nilai' => $data['total_nilai'],
                'petugas_id' => $data['petugas_id'],
                'status' => $data['status'] ?? 'SAH',
                'catatan' => $data['catatan'] ?? null
            ]);

            $transaksiId = $this->db->lastInsertId();

            // Insert detail items
            if (!empty($items)) {
                $stmtDetail = $this->db->prepare("
                    INSERT INTO detail_beli (transaksi_beli_id, kategori_id, berat_kg, harga_per_kg, subtotal)
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
            }

            // Update saldo nasabah jika metode TABUNG
            if ($data['tipe_penjual'] === 'NASABAH' && $data['metode_bayar'] === 'TABUNG' && !empty($data['nasabah_id'])) {
                $stmtSaldo = $this->db->prepare("
                    UPDATE nasabah SET saldo = saldo + :jumlah WHERE id = :id
                ");
                $stmtSaldo->execute([
                    'jumlah' => $data['total_nilai'],
                    'id' => $data['nasabah_id']
                ]);

                // Catat mutasi saldo
                $stmtMutasi = $this->db->prepare("
                    INSERT INTO mutasi_saldo (nasabah_id, tipe, jumlah, saldo_setelah, referensi_tipe, referensi_id, keterangan)
                    VALUES (:nasabah_id, 'SETOR', :jumlah, :saldo_setelah, 'transaksi_beli', :ref_id, :keterangan)
                ");

                // Ambil saldo baru
                $stmtGetSaldo = $this->db->prepare("SELECT saldo FROM nasabah WHERE id = :id");
                $stmtGetSaldo->execute(['id' => $data['nasabah_id']]);
                $newSaldo = $stmtGetSaldo->fetch()['saldo'];

                $stmtMutasi->execute([
                    'nasabah_id' => $data['nasabah_id'],
                    'jumlah' => $data['total_nilai'],
                    'saldo_setelah' => $newSaldo,
                    'ref_id' => $transaksiId,
                    'keterangan' => $noTrans
                ]);
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
            SELECT tb.*, b.nama as bank_nama, u.nama as petugas_nama,
                   n.nama as nasabah_nama, n.no_hp as nasabah_hp
            FROM transaksi_beli tb
            LEFT JOIN bank_sampah b ON tb.bank_sampah_id = b.id
            LEFT JOIN users u ON tb.petugas_id = u.id
            LEFT JOIN nasabah n ON tb.nasabah_id = n.id
            WHERE tb.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        if ($result) {
            // Ambil detail items
            $stmtDetail = $this->db->prepare("
                SELECT db.*, k.nama as kategori_nama, k.jenis
                FROM detail_beli db
                JOIN kategori_sampah k ON db.kategori_id = k.id
                WHERE db.transaksi_beli_id = :id
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
     * @param string|null $tanggalFrom
     * @param string|null $tanggalTo
     * @return array
     */
    public function getByBankSampah($bankSampahId, $limit = 50, $offset = 0, $tanggalFrom = null, $tanggalTo = null): array
    {
        $sql = "
            SELECT tb.*, b.nama as bank_nama, u.nama as petugas_nama,
                   n.nama as nasabah_nama, n.no_anggota
            FROM transaksi_beli tb
            LEFT JOIN bank_sampah b ON tb.bank_sampah_id = b.id
            LEFT JOIN users u ON tb.petugas_id = u.id
            LEFT JOIN nasabah n ON tb.nasabah_id = n.id
            WHERE tb.bank_sampah_id = :bank_id
        ";
        $params = ['bank_id' => $bankSampahId];

        if ($tanggalFrom) {
            $sql .= " AND tb.tanggal >= :tanggal_from";
            $params['tanggal_from'] = $tanggalFrom . ' 00:00:00';
        }

        if ($tanggalTo) {
            $sql .= " AND tb.tanggal <= :tanggal_to";
            $params['tanggal_to'] = $tanggalTo . ' 23:59:59';
        }

        $sql .= " ORDER BY tb.tanggal DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === 'limit' || $key === 'offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Statistik transaksi beli
     *
     * @param int $bankSampahId
     * @param string|null $tanggalFrom
     * @param string|null $tanggalTo
     * @return array
     */
    public function getStatistik($bankSampahId, $tanggalFrom = null, $tanggalTo = null): array
    {
        $sql = "
            SELECT
                COUNT(*) as total_transaksi,
                COALESCE(SUM(total_berat), 0) as total_berat,
                COALESCE(SUM(total_nilai), 0) as total_nilai,
                COALESCE(SUM(CASE WHEN metode_bayar = 'TABUNG' THEN total_nilai ELSE 0 END), 0) as total_tabungan,
                COALESCE(SUM(CASE WHEN metode_bayar = 'TUNAI' THEN total_nilai ELSE 0 END), 0) as total_tunai
            FROM transaksi_beli
            WHERE bank_sampah_id = :bank_id AND status = 'SAH'
        ";
        $params = ['bank_id' => $bankSampahId];

        if ($tanggalFrom) {
            $sql .= " AND tanggal >= :tanggal_from";
            $params['tanggal_from'] = $tanggalFrom . ' 00:00:00';
        }

        if ($tanggalTo) {
            $sql .= " AND tanggal <= :tanggal_to";
            $params['tanggal_to'] = $tanggalTo . ' 23:59:59';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}
