<?php
function formatRupiah($n): string { return 'Rp ' . number_format($n, 0, ',', '.'); }
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= APP_NAME ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #6a1b9a, #7b1fa2); color: white; flex-shrink: 0; }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { margin: 0; font-size: 1.1rem; }
        .sidebar nav a { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.875rem 1.5rem; border-left: 3px solid transparent; }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.15); color: white; border-left-color: #ce93d8; }
        .sidebar nav a.logout { margin-top: 2rem; color: #ffcdd2; }
        .main-content { flex: 1; padding: 2rem; }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #6a1b9a; }
        .page-header p { margin: 0.5rem 0 0; color: #666; }
        .flash { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .flash-success { background: #d4edda; color: #155724; }
        .flash-info { background: #d1ecf1; color: #0c5460; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .stat-card.purple h3 { color: #6a1b9a; }
        .stat-card.green h3 { color: #2e7d32; }
        .stat-card.orange h3 { color: #f57c00; }
        .stat-card.blue h3 { color: #1976d2; }
        .stat-card h3 { margin: 0; font-size: 1.75rem; font-weight: 700; }
        .stat-card p { margin: 0.25rem 0 0; font-size: 0.85rem; color: #888; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; }
        .card-header h3 { margin: 0; font-size: 1rem; }
        .card-body { padding: 1rem 1.5rem; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; background: #6a1b9a; color: white; }
        .btn:hover { background: #4a148c; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🏛️ <?= APP_NAME ?></h2>
                <small>Panel Kepala Desa</small>
            </div>
            <nav>
                <a href="<?= base_url('kades/dashboard') ?>" class="active">📊 Dashboard</a>
                <a href="<?= base_url('kades/monitoring') ?>">📈 Monitoring</a>
                <a href="<?= base_url('kades/laporan') ?>">💰 Laporan Keuangan</a>
                <a href="<?= base_url('logout') ?>" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if ($flash): ?>
                <div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

            <div class="page-header" style="margin-bottom:2rem;">
                <h1>Selamat Datang, <?= htmlspecialchars($user['nama']) ?>!</h1>
                <p>Monitoring kegiatan Bank Sampah di desa Anda</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card purple">
                    <h3><?= number_format($stats['total_bank']) ?></h3>
                    <p>Total Bank Sampah</p>
                </div>
                <div class="stat-card green">
                    <h3><?= number_format($stats['total_nilai_beli']) ?></h3>
                    <p>Total Pembelian (Rp)</p>
                </div>
                <div class="stat-card blue">
                    <h3><?= number_format($stats['total_nilai_jual']) ?></h3>
                    <p>Total Penjualan (Rp)</p>
                </div>
                <div class="stat-card orange">
                    <h3><?= number_format($stats['total_transaksi_beli'] + $stats['total_transaksi_jual']) ?></h3>
                    <p>Total Transaksi</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>📊 Rekapitulasi per Bank Sampah</h3></div>
                <div class="card-body">
                    <?php if (!empty($bankStats)): ?>
                        <table style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f8f9fa;">
                                    <th style="padding:0.75rem; text-align:left;">Bank Sampah</th>
                                    <th style="padding:0.75rem; text-align:right;">Transaksi Beli</th>
                                    <th style="padding:0.75rem; text-align:right;">Nilai Beli</th>
                                    <th style="padding:0.75rem; text-align:right;">Transaksi Jual</th>
                                    <th style="padding:0.75rem; text-align:right;">Nilai Jual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bankStats as $bs): ?>
                                    <tr style="border-bottom:1px solid #eee;">
                                        <td style="padding:0.75rem;"><strong><?= htmlspecialchars($bs['bank']['nama']) ?></strong></td>
                                        <td style="padding:0.75rem; text-align:right;"><?= number_format($bs['transaksi_beli']) ?></td>
                                        <td style="padding:0.75rem; text-align:right;"><?= formatRupiah($bs['nilai_beli']) ?></td>
                                        <td style="padding:0.75rem; text-align:right;"><?= number_format($bs['transaksi_jual']) ?></td>
                                        <td style="padding:0.75rem; text-align:right;"><?= formatRupiah($bs['nilai_jual']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background:#f8f9fa; font-weight:600;">
                                    <td style="padding:0.75rem;">TOTAL</td>
                                    <td style="padding:0.75rem; text-align:right;"><?= number_format($stats['total_transaksi_beli']) ?></td>
                                    <td style="padding:0.75rem; text-align:right;"><?= formatRupiah($stats['total_nilai_beli']) ?></td>
                                    <td style="padding:0.75rem; text-align:right;"><?= number_format($stats['total_transaksi_jual']) ?></td>
                                    <td style="padding:0.75rem; text-align:right;"><?= formatRupiah($stats['total_nilai_jual']) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    <?php else: ?>
                        <p style="text-align:center; color:#888; padding:2rem;">Belum ada data bank sampah di desa ini</p>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display:flex; gap:1rem;">
                <a href="<?= base_url('kades/monitoring') ?>" class="btn">📈 Monitoring Kegiatan</a>
                <a href="<?= base_url('kades/laporan') ?>" class="btn">💰 Laporan Keuangan</a>
            </div>
        </main>
    </div>
</body>
</html>
