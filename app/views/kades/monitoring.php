<?php
function formatRupiah($n): string { return 'Rp ' . number_format($n, 0, ',', '.'); }
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring - <?= APP_NAME ?></title>
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
        .page-header { margin-bottom: 1.5rem; }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #6a1b9a; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #6a1b9a; text-decoration: none; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; }
        .card-header h3 { margin: 0; font-size: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.875rem 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-size: 0.8rem; color: #888; text-transform: uppercase; }
        .badge { padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-warning { background: #fff3e0; color: #f57c00; }
        .badge-purple { background: #f3e5f5; color: #6a1b9a; }
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
                <a href="<?= base_url('kades/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('kades/monitoring') ?>" class="active">📈 Monitoring</a>
                <a href="<?= base_url('kades/laporan') ?>">💰 Laporan Keuangan</a>
                <a href="<?= base_url('logout') ?>" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>📈 Monitoring Kegiatan</h1>
                <div class="breadcrumb"><a href="<?= base_url('kades/dashboard') ?>">Dashboard</a> / Monitoring</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Aktivitas Terbaru</h3>
                    <span style="color:#888; font-size:0.9rem;">20 transaksi terakhir</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Bank Sampah</th>
                            <th>Keterangan</th>
                            <th style="text-align:right;">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTrans as $t): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($t['tanggal'])) ?></td>
                                <td>
                                    <?php if ($t['jenis'] === 'BELI'): ?>
                                        <span class="badge badge-success">🛒 BELI</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">📤 JUAL</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($t['bank_nama'] ?? '-') ?></td>
                                <td>
                                    <?php if ($t['jenis'] === 'BELI'): ?>
                                        <?= htmlspecialchars($t['nasabah_nama'] ?? $t['nama_penjual'] ?? '-') ?>
                                        <?php if ($t['tipe_penjual'] === 'NASABAH'): ?>
                                            <span class="badge badge-purple">Nasabah</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?= htmlspecialchars($t['pengepul_nama'] ?? '-') ?>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right; font-weight:600;"><?= formatRupiah($t['total_nilai']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentTrans)): ?>
                            <tr><td colspan="5" style="text-align:center; color:#888; padding:2rem;">Belum ada aktivitas</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
