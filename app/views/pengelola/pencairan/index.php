<?php
/**
 * Daftar Pengajuan Pencairan
 *
 * Halaman untuk mengelola pengajuan pencairan tabungan农户
 */

// Format uang
function formatRupiah($number) {
    return 'Rp ' . number_format((float) $number, 0, ',', '.');
}

// Format tanggal
function formatTanggal($date) {
    return date('d M Y H:i', strtotime($date));
}

$user = currentUser();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Pencairan - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
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
        .flash-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1b5e20; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #2e7d32; text-decoration: none; }

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
        .card-header h3 { margin: 0; font-size: 1.1rem; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .stat-card h4 { margin: 0 0 0.5rem 0; font-size: 0.85rem; color: #666; text-transform: uppercase; }
        .stat-card .value { font-size: 1.5rem; font-weight: 700; color: #333; }
        .stat-card .value.menunggu { color: #f57c00; }
        .stat-card .value.disetujui { color: #2e7d32; }
        .stat-card .value.ditolak { color: #e53935; }
        .stat-card .value.dicairkan { color: #1565c0; }

        .filter-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .filter-bar select, .filter-bar input {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        .filter-bar button {
            padding: 0.5rem 1.5rem;
            background: #2e7d32;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .filter-bar button:hover { background: #1b5e20; }

        .table-wrapper { overflow-x: auto; }
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
            font-size: 0.85rem;
            color: #666;
            text-transform: uppercase;
        }
        tr:hover { background: #f8f9fa; }

        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .badge-menunggu { background: #fff3e0; color: #f57c00; }
        .badge-disetujui { background: #e8f5e9; color: #2e7d32; }
        .badge-ditolak { background: #ffebee; color: #e53935; }
        .badge-dicairkan { background: #e3f2fd; color: #1565c0; }
        .badge-dibatalkan { background: #f5f5f5; color: #757575; }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
        .btn-primary { background: #2e7d32; color: white; }
        .btn-primary:hover { background: #1b5e20; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn-secondary:hover { background: #bdbdbd; }
        .btn-danger { background: #e53935; color: white; }
        .btn-danger:hover { background: #c62828; }
        .btn-info { background: #1565c0; color: white; }
        .btn-info:hover { background: #0d47a1; }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            padding: 1.5rem;
        }
        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
        }
        .pagination a:hover { background: #e0e0e0; }
        .pagination .active { background: #2e7d32; color: white; }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #888;
        }
        .empty-state .icon { font-size: 3rem; margin-bottom: 1rem; }
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
                <a href="<?= base_url('pengelola/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('pengelola/transaksi-beli') ?>">🛒 Transaksi Beli</a>
                <a href="<?= base_url('pengelola/transaksi-jual') ?>">💵 Transaksi Jual</a>
                <a href="<?= base_url('pengelola/nasabah') ?>">👥 Nasabah</a>
                <a href="<?= base_url('pengelola/pencairan') ?>" class="active">💰 Pencairan</a>
                <a href="<?= base_url('pengelola/kategori') ?>">🏷️ Kategori</a>
                <a href="<?= base_url('pengelola/harga') ?>">💲 Harga</a>
                <a href="<?= base_url('pengelola/pengepul') ?>">🚛 Pengepul</a>
                <a href="<?= base_url('pengelola/laporan') ?>">📈 Laporan</a>
                <a href="<?= base_url('logout') ?>" style="margin-top: 1rem; color: #ffcdd2;">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if ($flash): ?>
                <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
                    <?php if ($flash['type'] === 'success'): ?>✓<?php elseif ($flash['type'] === 'error'): ?>✕<?php else: ?>ℹ<?php endif; ?>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <div>
                    <h1>💰 Pengajuan Pencairan</h1>
                    <div class="breadcrumb">
                        <a href="<?= base_url('pengelola/dashboard') ?>">Dashboard</a> / Pengajuan Pencairan
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Menunggu</h4>
                    <div class="value menunggu"><?= isset($statistik['DIAJUKAN']) ? $statistik['DIAJUKAN']['jumlah'] : 0 ?></div>
                </div>
                <div class="stat-card">
                    <h4>Disetujui</h4>
                    <div class="value disetujui"><?= isset($statistik['DISETUJUI']) ? $statistik['DISETUJUI']['jumlah'] : 0 ?></div>
                </div>
                <div class="stat-card">
                    <h4>Ditolak</h4>
                    <div class="value ditolak"><?= isset($statistik['DITOLAK']) ? $statistik['DITOLAK']['jumlah'] : 0 ?></div>
                </div>
                <div class="stat-card">
                    <h4>Dicairkan</h4>
                    <div class="value dicairkan"><?= isset($statistik['DICAIRKAN']) ? $statistik['DICAIRKAN']['jumlah'] : 0 ?></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Daftar Pengajuan</h3>
                    <a href="<?= base_url('pengelola/pencairan/export-pdf') ?>" class="btn btn-sm btn-success" target="_blank">
                        📄 Export PDF
                    </a>
                </div>

                <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #eee;">
                    <form method="GET" action="" class="filter-bar" style="margin-bottom: 0;">
                        <select name="status">
                            <option value="">Semua Status</option>
                            <option value="DIAJUKAN" <?= ($statusFilter === 'DIAJUKAN') ? 'selected' : '' ?>>Menunggu</option>
                            <option value="DISETUJUI" <?= ($statusFilter === 'DISETUJUI') ? 'selected' : '' ?>>Disetujui</option>
                            <option value="DITOLAK" <?= ($statusFilter === 'DITOLAK') ? 'selected' : '' ?>>Ditolak</option>
                            <option value="DICAIRKAN" <?= ($statusFilter === 'DICAIRKAN') ? 'selected' : '' ?>>Dicairkan</option>
                            <option value="DIBATALKAN" <?= ($statusFilter === 'DIBATALKAN') ? 'selected' : '' ?>>Dibatalkan</option>
                        </select>
                        <button type="submit">🔍 Filter</button>
                        <?php if ($statusFilter): ?>
                            <a href="<?= base_url('pengelola/pencairan') ?>" class="btn btn-secondary">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="table-wrapper">
                    <?php if (empty($pengajuans)): ?>
                        <div class="empty-state">
                            <div class="icon">📋</div>
                            <p>Tidak ada pengajuan pencairan</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>No. Pengajuan</th>
                                    <th>Nasabah</th>
                                    <th>Jumlah</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pengajuans as $p): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($p['no_pengajuan']) ?></strong></td>
                                        <td>
                                            <?= htmlspecialchars($p['nama_nasabah']) ?>
                                            <br><small style="color: #888;"><?= htmlspecialchars($p['hp_nasabah']) ?></small>
                                        </td>
                                        <td><?= formatRupiah($p['jumlah']) ?></td>
                                        <td>
                                            <?php if ($p['metode'] === 'TUNAI'): ?>
                                                💵 Tunai
                                            <?php else: ?>
                                                🏦 Transfer
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status = $p['status'];
                                            if ($status === 'DIAJUKAN') {
                                                echo '<span class="badge badge-menunggu">DIAJUKAN</span>';
                                            } elseif ($status === 'DISETUJUI') {
                                                echo '<span class="badge badge-disetujui">DISETUJUI</span>';
                                            } elseif ($status === 'DITOLAK') {
                                                echo '<span class="badge badge-ditolak">DITOLAK</span>';
                                            } elseif ($status === 'DICAIRKAN') {
                                                echo '<span class="badge badge-dicairkan">DICAIRKAN</span>';
                                            } else {
                                                echo '<span class="badge badge-dibatalkan">' . htmlspecialchars($status) . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?= formatTanggal($p['diajukan_at']) ?></td>
                                        <td>
                                            <a href="<?= base_url('pengelola/pencairan/detail?id=' . $p['id']) ?>" class="btn btn-sm btn-secondary">
                                                👁️ Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($statusFilter ?? '') ?>">← Prev</a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>&status=<?= urlencode($statusFilter ?? '') ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($statusFilter ?? '') ?>">Next →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
