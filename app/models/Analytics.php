<?php
/**
 * Analytics Model
 *
 * Model untuk statistik dan analytics
 * Compatible dengan PHP 7.3+
 */

class Analytics
{
    /** @var PDO */
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? getDbConnection();
    }

    /**
     * Statistik overview
     *
     * @param int|null $bankSampahId
     * @param string|null $tanggalFrom
     * @param string|null $tanggalTo
     * @return array
     */
    public function getOverview($bankSampahId = null, $tanggalFrom = null, $tanggalTo = null): array
    {
        $where = $this->buildWhere($bankSampahId, $tanggalFrom, $tanggalTo);

        // Total nasabah
        $sqlNasabah = "SELECT COUNT(*) as total FROM nasabah n";
        if ($bankSampahId) {
            $sqlNasabah .= " WHERE n.bank_sampah_id = :bank_id";
        }
        $stmtNasabah = $this->db->prepare($sqlNasabah);
        if ($bankSampahId) {
            $stmtNasabah->execute(['bank_id' => $bankSampahId]);
        } else {
            $stmtNasabah->execute();
        }
        $totalNasabah = $stmtNasabah->fetch()['total'];

        // Total transaksi beli
        $sqlBeli = "SELECT COUNT(*) as total, COALESCE(SUM(total_berat), 0) as berat, COALESCE(SUM(total_nilai), 0) as nilai FROM transaksi_beli WHERE status = 'SAH'" . $where;
        $stmtBeli = $this->db->prepare($sqlBeli);
        $this->bindParams($stmtBeli, $bankSampahId, $tanggalFrom, $tanggalTo);
        $stmtBeli->execute();
        $statBeli = $stmtBeli->fetch();

        // Total transaksi jual
        $sqlJual = "SELECT COUNT(*) as total, COALESCE(SUM(total_berat), 0) as berat, COALESCE(SUM(total_nilai), 0) as nilai FROM transaksi_jual WHERE status = 'SAH'" . $where;
        $stmtJual = $this->db->prepare($sqlJual);
        $this->bindParams($stmtJual, $bankSampahId, $tanggalFrom, $tanggalTo);
        $stmtJual->execute();
        $statJual = $stmtJual->fetch();

        // Total saldo nasabah
        $sqlSaldo = "SELECT COALESCE(SUM(saldo), 0) as total FROM nasabah";
        if ($bankSampahId) {
            $sqlSaldo .= " WHERE bank_sampah_id = :bank_id";
        }
        $stmtSaldo = $this->db->prepare($sqlSaldo);
        if ($bankSampahId) {
            $stmtSaldo->execute(['bank_id' => $bankSampahId]);
        } else {
            $stmtSaldo->execute();
        }
        $totalSaldo = $stmtSaldo->fetch()['total'];

        return [
            'total_nasabah' => $totalNasabah,
            'total_saldo' => $totalSaldo,
            'transaksi_beli' => [
                'jumlah' => $statBeli['total'],
                'berat' => $statBeli['berat'],
                'nilai' => $statBeli['nilai']
            ],
            'transaksi_jual' => [
                'jumlah' => $statJual['total'],
                'berat' => $statJual['berat'],
                'nilai' => $statJual['nilai']
            ],
            'keuntungan' => $statJual['nilai'] - $statBeli['nilai']
        ];
    }

    /**
     * Data grafik per hari (7 hari terakhir)
     *
     * @param int|null $bankSampahId
     * @return array
     */
    public function getChartData($bankSampahId = null): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = date('Y-m-d', strtotime("-$i days"));
            $tanggalDisplay = date('d/m', strtotime("-$i days"));

            $params = ['tanggal' => $tanggal];
            $where = " AND DATE(tanggal) = :tanggal";
            if ($bankSampahId) {
                $where .= " AND bank_sampah_id = :bank_id";
                $params['bank_id'] = $bankSampahId;
            }

            // Beli
            $stmtBeli = $this->db->prepare("
                SELECT COALESCE(SUM(total_nilai), 0) as nilai, COUNT(*) as jumlah
                FROM transaksi_beli WHERE status = 'SAH'" . $where
            );
            $stmtBeli->execute($params);
            $beli = $stmtBeli->fetch();

            // Jual
            $stmtJual = $this->db->prepare("
                SELECT COALESCE(SUM(total_nilai), 0) as nilai, COUNT(*) as jumlah
                FROM transaksi_jual WHERE status = 'SAH'" . $where
            );
            $stmtJual->execute($params);
            $jual = $stmtJual->fetch();

            $data[] = [
                'tanggal' => $tanggalDisplay,
                'beli' => (float) $beli['nilai'],
                'jual' => (float) $jual['nilai'],
                'jumlah_beli' => $beli['jumlah'],
                'jumlah_jual' => $jual['jumlah']
            ];
        }

        return $data;
    }

    /**
     * Kategori terpopuler
     *
     * @param int|null $bankSampahId
     * @param int $limit
     * @return array
     */
    public function getTopKategori($bankSampahId = null, $limit = 5): array
    {
        $sql = "
            SELECT k.nama, k.jenis, SUM(db.berat_kg) as total_berat, COUNT(*) as total_transaksi
            FROM detail_beli db
            JOIN transaksi_beli tb ON db.transaksi_beli_id = tb.id
            JOIN kategori_sampah k ON db.kategori_id = k.id
            WHERE tb.status = 'SAH'
        ";
        $params = [];

        if ($bankSampahId) {
            $sql .= " AND tb.bank_sampah_id = :bank_id";
            $params['bank_id'] = $bankSampahId;
        }

        $sql .= " GROUP BY db.kategori_id ORDER BY total_berat DESC LIMIT :limit";
        $params['limit'] = $limit;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === 'limit') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Helper: Build WHERE clause
     */
    private function buildWhere($bankSampahId, $tanggalFrom, $tanggalTo): string
    {
        $where = "";
        if ($bankSampahId) {
            $where .= " AND bank_sampah_id = :bank_id";
        }
        if ($tanggalFrom) {
            $where .= " AND tanggal >= :tanggal_from";
        }
        if ($tanggalTo) {
            $where .= " AND tanggal <= :tanggal_to";
        }
        return $where;
    }

    /**
     * Helper: Bind parameters
     */
    private function bindParams($stmt, $bankSampahId, $tanggalFrom, $tanggalTo): void
    {
        if ($bankSampahId) {
            $stmt->bindValue(':bank_id', $bankSampahId, PDO::PARAM_INT);
        }
        if ($tanggalFrom) {
            $stmt->bindValue(':tanggal_from', $tanggalFrom . ' 00:00:00');
        }
        if ($tanggalTo) {
            $stmt->bindValue(':tanggal_to', $tanggalTo . ' 23:59:59');
        }
    }
}
