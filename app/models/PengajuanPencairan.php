<?php
/**
 * PengajuanPencairan Model
 *
 * Model untuk interaksi dengan tabel pengajuan_pencairan
 * Compatible dengan PHP 7.3+
 */

class PengajuanPencairan
{
    /** @var PDO */
    private $db;

    // Konstanta untuk validasi
    const MINIMAL_PENCAIRAN = 10000; // Rp 10.000

    // Konstanta status
    const STATUS_DIAJUKAN = 'DIAJUKAN';
    const STATUS_DISETUJUI = 'DISETUJUI';
    const STATUS_DITOLAK = 'DITOLAK';
    const STATUS_DICAIRKAN = 'DICAIRKAN';
    const STATUS_DIBATALKAN = 'DIBATALKAN';

    public function __construct($db = null)
    {
        $this->db = $db ?? getDbConnection();
    }

    /**
     * Generate nomor pengajuan baru dengan format PC-XXXX
     *
     * @return string
     */
    public function generateNoPengajuan(): string
    {
        // Ambil ID terakhir
        $stmt = $this->db->query("
            SELECT id FROM pengajuan_pencairan ORDER BY id DESC LIMIT 1
        ");
        $last = $stmt->fetch();

        $nextNum = ($last) ? ((int) $last['id']) + 1 : 1;

        // Format: PC-XXXX (4 digit dengan leading zero)
        return 'PC-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Buat pengajuan pencairan baru
     *
     * @param array $data
     * @return int|false Last insert ID jika berhasil, false jika gagal
     */
    public function create(array $data)
    {
        $noPengajuan = $this->generateNoPengajuan();

        $stmt = $this->db->prepare("
            INSERT INTO pengajuan_pencairan
            (no_pengajuan, nasabah_id, jumlah, metode, tujuan_transfer, status, diajukan_at)
            VALUES
            (:no_pengajuan, :nasabah_id, :jumlah, :metode, :tujuan_transfer, :status, :diajukan_at)
        ");

        $params = [
            'no_pengajuan' => $noPengajuan,
            'nasabah_id' => $data['nasabah_id'],
            'jumlah' => $data['jumlah'],
            'metode' => $data['metode'],
            'tujuan_transfer' => $data['tujuan_transfer'] ?? null,
            'status' => self::STATUS_DIAJUKAN,
            'diajukan_at' => date('Y-m-d H:i:s')
        ];

        $result = $stmt->execute($params);

        if ($result) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Ambil pengajuan berdasarkan ID
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT
                p.*,
                n.nama as nama_nasabah,
                n.no_hp as hp_nasabah,
                n.no_rekening,
                n.bank_sampah_id,
                bs.nama as nama_bank_sampah,
                u.nama as diproses_oleh_nama
            FROM pengajuan_pencairan p
            LEFT JOIN nasabah n ON p.nasabah_id = n.id
            LEFT JOIN bank_sampah bs ON n.bank_sampah_id = bs.id
            LEFT JOIN users u ON p.diproses_oleh = u.id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Ambil semua pengajuan oleh seorang Nasabah
     *
     * @param int $nasabahId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByNasabah(int $nasabahId, $limit = 20, $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                no_pengajuan,
                jumlah,
                metode,
                status,
                diajukan_at,
                diproses_at,
                dicairkan_at
            FROM pengajuan_pencairan
            WHERE nasabah_id = :nasabah_id
            ORDER BY diajukan_at DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue('nasabah_id', $nasabahId, PDO::PARAM_INT);
        $stmt->bindValue('limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ambil pengajuan yang masih aktif (DIAJUKAN atau DISETUJUI)
     *
     * @param int $nasabahId
     * @return array
     */
    public function getAktifByNasabah(int $nasabahId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                no_pengajuan,
                jumlah,
                metode,
                status,
                diajukan_at
            FROM pengajuan_pencairan
            WHERE nasabah_id = :nasabah_id
            AND status IN (:diajukan, :disetujui)
            ORDER BY diajukan_at DESC
        ");

        $stmt->bindValue('nasabah_id', $nasabahId, PDO::PARAM_INT);
        $stmt->bindValue('diajukan', self::STATUS_DIAJUKAN, PDO::PARAM_STR);
        $stmt->bindValue('disetujui', self::STATUS_DISETUJUI, PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Update status pengajuan
     *
     * @param int $id
     * @param string $status
     * @param int|null $processedBy User ID yang memproses
     * @param string|null $alasan Alasan penolakan (jika ditolak)
     * @return bool
     */
    public function updateStatus(int $id, string $status, $processedBy = null, $alasan = null): bool
    {
        $now = date('Y-m-d H:i:s');

        $sql = "UPDATE pengajuan_pencairan SET status = :status, diproses_at = :diproses_at";

        $params = [
            'status' => $status,
            'diproses_at' => $now,
            'id' => $id
        ];

        if ($processedBy !== null) {
            $sql .= ", diproses_oleh = :diproses_oleh";
            $params['diproses_oleh'] = $processedBy;
        }

        if ($status === self::STATUS_DITOLAK && $alasan !== null) {
            $sql .= ", alasan_tolak = :alasan_tolak";
            $params['alasan_tolak'] = $alasan;
        }

        if ($status === self::STATUS_DICAIRKAN) {
            $sql .= ", dicairkan_at = :dicairkan_at";
            $params['dicairkan_at'] = $now;
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Hitung total pengajuan aktif (belum selesai) seorang Nasabah
     *
     * @param int $nasabahId
     * @return float
     */
    public function getTotalTerikat(int $nasabahId): float
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(jumlah), 0) as total
            FROM pengajuan_pencairan
            WHERE nasabah_id = :nasabah_id
            AND status IN (:diajukan, :disetujui)
        ");

        $stmt->bindValue('nasabah_id', $nasabahId, PDO::PARAM_INT);
        $stmt->bindValue('diajukan', self::STATUS_DIAJUKAN, PDO::PARAM_STR);
        $stmt->bindValue('disetujui', self::STATUS_DISETUJUI, PDO::PARAM_STR);

        $stmt->execute();
        $result = $stmt->fetch();

        return (float) ($result['total'] ?? 0);
    }

    /**
     * Hitung total pengajuan seorang Nasabah
     *
     * @param int $nasabahId
     * @return int
     */
    public function countByNasabah(int $nasabahId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM pengajuan_pencairan
            WHERE nasabah_id = :nasabah_id
        ");
        $stmt->execute(['nasabah_id' => $nasabahId]);
        $result = $stmt->fetch();

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Ambil semua pengajuan pencairan (untuk admin/pengelola)
     *
     * @param int|null $bankSampahId Filter berdasarkan bank sampah (null = semua)
     * @param string|null $status Filter berdasarkan status
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAll(?int $bankSampahId = null, ?string $status = null, int $limit = 50, int $offset = 0): array
    {
        $sql = "
            SELECT
                p.id,
                p.no_pengajuan,
                p.nasabah_id,
                p.jumlah,
                p.metode,
                p.tujuan_transfer,
                p.status,
                p.alasan_tolak,
                p.diajukan_at,
                p.diproses_at,
                p.diproses_oleh,
                p.dicairkan_at,
                n.nama as nama_nasabah,
                n.no_hp as hp_nasabah,
                n.no_rekening,
                n.bank_sampah_id,
                bs.nama as nama_bank_sampah,
                u.nama as diproses_oleh_nama
            FROM pengajuan_pencairan p
            LEFT JOIN nasabah n ON p.nasabah_id = n.id
            LEFT JOIN bank_sampah bs ON n.bank_sampah_id = bs.id
            LEFT JOIN users u ON p.diproses_oleh = u.id
            WHERE 1=1
        ";

        $params = [];

        if ($bankSampahId !== null) {
            $sql .= " AND n.bank_sampah_id = :bank_sampah_id";
            $params['bank_sampah_id'] = $bankSampahId;
        }

        if ($status !== null) {
            $sql .= " AND p.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY p.diajukan_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Hitung total pengajuan dengan filter
     *
     * @param int|null $bankSampahId
     * @param string|null $status
     * @return int
     */
    public function countAll(?int $bankSampahId = null, ?string $status = null): int
    {
        $sql = "SELECT COUNT(*) as total FROM pengajuan_pencairan p
                LEFT JOIN nasabah n ON p.nasabah_id = n.id WHERE 1=1";
        $params = [];

        if ($bankSampahId !== null) {
            $sql .= " AND n.bank_sampah_id = :bank_sampah_id";
            $params['bank_sampah_id'] = $bankSampahId;
        }

        if ($status !== null) {
            $sql .= " AND p.status = :status";
            $params['status'] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Ambil statistik pengajuan per status
     *
     * @param int|null $bankSampahId
     * @return array
     */
    public function getStatistik(?int $bankSampahId = null): array
    {
        $sql = "SELECT p.status, COUNT(*) as jumlah, COALESCE(SUM(p.jumlah), 0) as total_nilai
                FROM pengajuan_pencairan p
                LEFT JOIN nasabah n ON p.nasabah_id = n.id
                WHERE 1=1";

        $params = [];
        if ($bankSampahId !== null) {
            $sql .= " AND n.bank_sampah_id = :bank_sampah_id";
            $params['bank_sampah_id'] = $bankSampahId;
        }

        $sql .= " GROUP BY p.status";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = [];
        $totalSemua = ['jumlah' => 0, 'total_nilai' => 0];

        while ($row = $stmt->fetch()) {
            $result[$row['status']] = [
                'jumlah' => (int) $row['jumlah'],
                'total_nilai' => (float) $row['total_nilai']
            ];
            $totalSemua['jumlah'] += (int) $row['jumlah'];
            $totalSemua['total_nilai'] += (float) $row['total_nilai'];
        }

        $result['TOTAL'] = $totalSemua;
        return $result;
    }

    /**
     * Validasi data pengajuan
     *
     * @param array $data
     * @param float $saldoTersedia
     * @return array Array of error messages (empty if valid)
     */
    public static function validate(array $data, float $saldoTersedia): array
    {
        $errors = [];

        // Validasi jumlah
        if (!isset($data['jumlah']) || !is_numeric($data['jumlah'])) {
            $errors[] = 'Jumlah pencairan harus diisi dengan angka.';
        } else {
            $jumlah = (float) $data['jumlah'];

            if ($jumlah < self::MINIMAL_PENCAIRAN) {
                $errors[] = 'Jumlah minimal pencairan adalah Rp ' . number_format(self::MINIMAL_PENCAIRAN, 0, ',', '.');
            }

            if ($jumlah > $saldoTersedia) {
                $errors[] = 'Jumlah pencairan melebihi saldo yang tersedia (Rp ' . number_format($saldoTersedia, 0, ',', '.') . ').';
            }
        }

        // Validasi metode
        if (!isset($data['metode']) || !in_array($data['metode'], ['TUNAI', 'TRANSFER'])) {
            $errors[] = 'Metode pencairan harus dipilih.';
        }

        // Validasi tujuan transfer jika metode TRANSFER
        if (isset($data['metode']) && $data['metode'] === 'TRANSFER') {
            $tujuan = trim($data['tujuan_transfer'] ?? '');
            if (empty($tujuan)) {
                $errors[] = 'Tujuan transfer (nomor rekening/e-wallet) wajib diisi.';
            } elseif (strlen($tujuan) < 8) {
                $errors[] = 'Nomor tujuan transfer terlalu pendek.';
            }
        }

        return $errors;
    }
}
