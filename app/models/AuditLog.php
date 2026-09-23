<?php
/**
 * AuditLog Model
 *
 * Model untuk interaksi dengan tabel audit_log
 * Compatible dengan PHP 7.3+
 */

class AuditLog
{
    /** @var PDO */
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? getDbConnection();
    }

    /**
     * Buat log audit
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO audit_log (user_id, aksi, tabel, record_id, sebelum, sesudah)
            VALUES (:user_id, :aksi, :tabel, :record_id, :sebelum, :sesudah)
        ");

        return $stmt->execute([
            'user_id' => $data['user_id'] ?? null,
            'aksi' => $data['aksi'],
            'tabel' => $data['tabel'],
            'record_id' => $data['record_id'],
            'sebelum' => isset($data['sebelum']) ? json_encode($data['sebelum']) : null,
            'sesudah' => isset($data['sesudah']) ? json_encode($data['sesudah']) : null
        ]);
    }

    /**
     * Ambil log dengan filter
     *
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLogs($filters = [], $limit = 50, $offset = 0): array
    {
        $sql = "
            SELECT a.*, u.nama as user_nama
            FROM audit_log a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND a.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['tabel'])) {
            $sql .= " AND a.tabel = :tabel";
            $params['tabel'] = $filters['tabel'];
        }

        if (!empty($filters['aksi'])) {
            $sql .= " AND a.aksi = :aksi";
            $params['aksi'] = $filters['aksi'];
        }

        if (!empty($filters['tanggal_from'])) {
            $sql .= " AND a.created_at >= :tanggal_from";
            $params['tanggal_from'] = $filters['tanggal_from'] . ' 00:00:00';
        }

        if (!empty($filters['tanggal_to'])) {
            $sql .= " AND a.created_at <= :tanggal_to";
            $params['tanggal_to'] = $filters['tanggal_to'] . ' 23:59:59';
        }

        $sql .= " ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset";
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
     * Log aktivitas INSERT
     */
    public function logInsert($tabel, $recordId, $data, $userId = null): bool
    {
        return $this->create([
            'user_id' => $userId,
            'aksi' => 'INSERT',
            'tabel' => $tabel,
            'record_id' => $recordId,
            'sesudah' => $data
        ]);
    }

    /**
     * Log aktivitas UPDATE
     */
    public function logUpdate($tabel, $recordId, $sebelum, $sesudah, $userId = null): bool
    {
        return $this->create([
            'user_id' => $userId,
            'aksi' => 'UPDATE',
            'tabel' => $tabel,
            'record_id' => $recordId,
            'sebelum' => $sebelum,
            'sesudah' => $sesudah
        ]);
    }

    /**
     * Log aktivitas DELETE
     */
    public function logDelete($tabel, $recordId, $data, $userId = null): bool
    {
        return $this->create([
            'user_id' => $userId,
            'aksi' => 'DELETE',
            'tabel' => $tabel,
            'record_id' => $recordId,
            'sebelum' => $data
        ]);
    }
}

/**
 * Helper: Catat audit log
 */
function audit_log($aksi, $tabel, $recordId, $sebelum = null, $sesudah = null): void
{
    $audit = new AuditLog();
    $userId = currentUserId();
    $audit->create([
        'user_id' => $userId,
        'aksi' => $aksi,
        'tabel' => $tabel,
        'record_id' => $recordId,
        'sebelum' => $sebelum,
        'sesudah' => $sesudah
    ]);
}

/**
 * Helper: Kirim notifikasi
 */
function notifikasi($userId, $judul, $isi, $tipe = 'INFO'): bool
{
    return Notifikasi::send($userId, $judul, $isi, $tipe);
}
