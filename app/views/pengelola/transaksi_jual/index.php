<?php
function formatRupiah($n): string { return 'Rp ' . number_format($n, 0, ',', '.'); }
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Jual - <?= APP_NAME ?></title>
    <style>
        * { box-sizing: border-box; } body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #1565c0, #1976d2); color: white; flex-shrink: 0; }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { margin: 0; font-size: 1.1rem; }
        .sidebar nav a { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.875rem 1.5rem; border-left: 3px solid transparent; }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.15); color: white; border-left-color: #90caf9; }
        .sidebar nav a.logout { margin-top: 2rem; color: #ffcdd2; }
        .main-content { flex: 1; padding: 2rem; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1565c0; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #1565c0; text-decoration: none; }
        .flash { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .flash-success { background: #d4edda; color: #155724; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; }
        .card-header h3 { margin: 0; font-size: 1rem; }
        .stats-row { display: flex; gap: 1rem; padding: 1rem 1.5rem; background: #f8f9fa; }
        .stat-item { text-align: center; }
        .stat-item h4 { margin: 0; font-size: 1.25rem; color: #f57c00; }
        .stat-item p { margin: 0; font-size: 0.8rem; color: #888; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.875rem 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-size: 0.8rem; color: #888; text-transform: uppercase; }
        .btn { padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; background: #1565c0; color: white; }
        .btn:hover { background: #0d47a1; }
        .badge { padding: 0.25rem 0.5rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-warning { background: #fff3e0; color: #f57c00; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><h2>📦 <?= APP_NAME ?></h2></div>
            <nav>
                <a href="<?= base_url('pengelola/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('pengelola/transaksi-beli') ?>">🛒 Transaksi Beli</a>
                <a href="<?= base_url('pengelola/transaksi-jual') ?>" class="active">📤 Transaksi Jual</a>
                <a href="<?= base_url('pengelola/kategori') ?>">📦 Kategori</a>
                <a href="<?= base_url('pengelola/harga') ?>">💰 Harga</a>
                <a href="<?= base_url('pengelola/nasabah') ?>">👥 Nasabah</a>
                <a href="<?= base_url('pengelola/pengepul') ?>">🚛 Pengepul</a>
                <a href="<?= base_url('logout') ?>" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if ($flash): ?><div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div><?php endif; ?>

            <div class="page-header">
                <div>
                    <h1>📤 Transaksi Jual (Penjualan ke Pengepul)</h1>
                    <div class="breadcrumb"><a href="<?= base_url('pengelola/dashboard') ?>">Dashboard</a> / Transaksi Jual</div>
                </div>
                <a href="<?= base_url('pengelola/transaksi-jual/add') ?>" class="btn">➕ Transaksi Baru</a>
            </div>

            <div class="card">
                <div class="stats-row">
                    <div class="stat-item"><h4><?= number_format($stats['total_transaksi'] ?? 0) ?></h4><p>Total Transaksi</p></div>
                    <div class="stat-item"><h4><?= number_format($stats['total_berat'] ?? 0, 1) ?> kg</h4><p>Total Berat</p></div>
                    <div class="stat-item"><h4><?= formatRupiah($stats['total_nilai'] ?? 0) ?></h4><p>Total Nilai</p></div>
                    <div class="stat-item"><h4><?= formatRupiah($stats['total_belum_bayar'] ?? 0) ?></h4><p>Piutang</p></div>
                </div>

                <table>
                    <thead><tr><th>No Transaksi</th><th>Tanggal</th><th>Pengepul</th><th>Berat</th><th>Total</th><th>Status Bayar</th></tr></thead>
                    <tbody>
                        <?php foreach ($transaksis as $t): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['no_transaksi']) ?></strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($t['tanggal'])) ?></td>
                                <td><?= htmlspecialchars($t['pengepul_nama'] ?? '-') ?></td>
                                <td><?= number_format($t['total_berat'], 1) ?> kg</td>
                                <td><strong><?= formatRupiah($t['total_nilai']) ?></strong></td>
                                <td><span class="badge badge-<?= $t['status_bayar'] === 'LUNAS' ? 'success' : 'warning' ?>"><?= $t['status_bayar'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($transaksis)): ?>
                            <tr><td colspan="6" style="text-align:center;color:#888;padding:2rem;">Belum ada transaksi</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
