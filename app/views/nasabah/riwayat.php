<?php
/**
 * Riwayat Transaksi Nasabah
 *
 * Menampilkan semua riwayat mutasi saldo
 * Compatible dengan PHP 7.3+
 */

// Format uang ke Rupiah Indonesia
function formatRupiah($number): string
{
    return 'Rp ' . number_format((float) $number, 0, ',', '.');
}

// Format tanggal Indonesia
function formatTanggalIndonesia($datetime): string
{
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
        4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $date = is_string($datetime) ? strtotime($datetime) : $datetime;
    $day = date('j', $date);
    $month = $bulan[(int) date('n', $date)];
    $year = date('Y', $date);

    return $day . ' ' . $month . ' ' . $year;
}

// Format datetime Indonesia dengan jam
function formatTanggalWaktuIndonesia($datetime): string
{
    $tanggal = formatTanggalIndonesia($datetime);
    $date = is_string($datetime) ? strtotime($datetime) : $datetime;
    $time = date('H:i', $date);

    return $tanggal . ' ' . $time;
}

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
            padding: 0;
            flex-shrink: 0;
        }
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header h2 { margin: 0; font-size: 1.1rem; }
        .sidebar-header small { opacity: 0.8; }
        .sidebar nav { padding: 1rem 0; }
        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 0.875rem 1.5rem;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        .sidebar nav a:hover { background: rgba(255,255,255,0.1); color: white; }
        .sidebar nav a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: #81c784;
        }
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }

        /* Flash Messages */
        .flash {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1b5e20; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #2e7d32; text-decoration: none; }

        /* Card */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header h3 { margin: 0; font-size: 1.1rem; color: #333; }
        .card-body { padding: 0; }

        /* Table */
        .table-responsive { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1rem 1.25rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tr:hover { background: #f8f9fa; }
        td { color: #333; font-size: 0.95rem; }

        /* Badges */
        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-warning { background: #fff3e0; color: #f57c00; }
        .badge-info { background: #e3f2fd; color: #1976d2; }
        .badge-secondary { background: #eceff1; color: #546e7a; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #888;
        }
        .empty-state svg { width: 80px; height: 80px; margin-bottom: 1rem; opacity: 0.4; }
        .empty-state h3 { margin: 0 0 0.5rem 0; color: #666; }
        .empty-state p { margin: 0; }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            padding: 1.5rem;
        }
        .pagination a, .pagination span {
            padding: 0.5rem 0.875rem;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            font-size: 0.9rem;
        }
        .pagination a:hover { background: #e8f5e9; color: #2e7d32; }
        .pagination .active {
            background: #2e7d32;
            color: white;
        }
        .pagination .disabled {
            color: #ccc;
            pointer-events: none;
        }

        /* Info Box */
        .info-box {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .info-box svg { flex-shrink: 0; }
        .info-box p { margin: 0; font-size: 0.9rem; color: #1565c0; }

        /* Saldo Summary */
        .saldo-summary {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .summary-item {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .summary-item label {
            display: block;
            font-size: 0.75rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        .summary-item .value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🌿 <?= APP_NAME ?></h2>
                <small>Bank Sampah Digital</small>
            </div>
            <nav>
                <a href="<?= base_url('nasabah/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('nasabah/setor') ?>">🗑️ Setor Sampah</a>
                <a href="<?= base_url('nasabah/riwayat') ?>" class="active">📜 Riwayat Transaksi</a>
                <a href="<?= base_url('nasabah/cairkan') ?>">💰 Ajukan Pencairan</a>
                <a href="<?= base_url('nasabah/profil') ?>">👤 Profil Saya</a>
                <a href="<?= base_url('logout') ?>" style="margin-top: 1rem; color: #ffcdd2;">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <!-- Flash Message -->
            <?php if ($flash): ?>
                <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
                    <?php if ($flash['type'] === 'success'): ?>✓<?php elseif ($flash['type'] === 'error'): ?>✕<?php else: ?>ℹ<?php endif; ?>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>📜 Riwayat Transaksi</h1>
                    <div class="breadcrumb">
                        <a href="<?= base_url('nasabah/dashboard') ?>">Dashboard</a> / Riwayat Transaksi
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <p>Menampilkan riwayat mutasi saldo Anda. Total <?= number_format($totalData) ?> transaksi.</p>
            </div>

            <!-- Tabel Riwayat -->
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Transaksi</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($riwayat)): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal & Waktu</th>
                                        <th>Tipe</th>
                                        <th>Jumlah</th>
                                        <th>Saldo Akhir</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = $offset + 1;
                                    foreach ($riwayat as $row):
                                        $badgeClass = 'badge-secondary';
                                        if ($row['tipe'] === 'SETOR') {
                                            $badgeClass = 'badge-success';
                                        } elseif ($row['tipe'] === 'PENCAIRAN') {
                                            $badgeClass = 'badge-warning';
                                        } elseif ($row['tipe'] === 'KOREKSI') {
                                            $badgeClass = 'badge-info';
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $no ?></td>
                                            <td><?= formatTanggalWaktuIndonesia($row['created_at']) ?></td>
                                            <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['tipe']) ?></span></td>
                                            <td style="<?= $row['jumlah'] >= 0 ? 'color: #2e7d32; font-weight: 600;' : 'color: #f57c00; font-weight: 600;' ?>">
                                                <?= $row['jumlah'] >= 0 ? '+' : '' ?><?= formatRupiah($row['jumlah']) ?>
                                            </td>
                                            <td><?= formatRupiah($row['saldo_setelah']) ?></td>
                                            <td style="font-size: 0.85rem; color: #666;">
                                                <?= htmlspecialchars($row['keterangan'] ?? '-') ?>
                                            </td>
                                        </tr>
                                    <?php
                                        $no++;
                                    endforeach;
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page - 1 ?>">‹ Prev</a>
                                <?php else: ?>
                                    <span class="disabled">‹ Prev</span>
                                <?php endif; ?>

                                <?php
                                $start = max(1, $page - 2);
                                $end = min($totalPages, $page + 2);

                                if ($start > 1) {
                                    echo '<a href="?page=1">1</a>';
                                    if ($start > 2) {
                                        echo '<span class="disabled">...</span>';
                                    }
                                }

                                for ($i = $start; $i <= $end; $i++):
                                ?>
                                    <?php if ($i == $page): ?>
                                        <span class="active"><?= $i ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?= $i ?>"><?= $i ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($end < $totalPages): ?>
                                    <?php if ($end < $totalPages - 1): ?>
                                        <span class="disabled">...</span>
                                    <?php endif; ?>
                                    <a href="?page=<?= $totalPages ?>"><?= $totalPages ?></a>
                                <?php endif; ?>

                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?= $page + 1 ?>">Next ›</a>
                                <?php else: ?>
                                    <span class="disabled">Next ›</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3>Belum Ada Transaksi</h3>
                            <p>Riwayat transaksi akan muncul setelah Anda melakukan setoran sampah.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
