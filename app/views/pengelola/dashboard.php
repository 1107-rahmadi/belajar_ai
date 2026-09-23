<?php
/**
 * Dashboard Pengelola
 */
function formatRupiah($n): string { return 'Rp ' . number_format($n, 0, ',', '.'); }

$user = currentUser();
$bank = $bankInfo ?? null;

// Ambil statistik pencairan jika ada
$statsPencairan = $statsPencairan ?? null;
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
        .sidebar { width: 260px; background: linear-gradient(180deg, #1565c0, #1976d2); color: white; flex-shrink: 0; }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { margin: 0; font-size: 1.1rem; }
        .sidebar nav a { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.875rem 1.5rem; border-left: 3px solid transparent; }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.15); color: white; border-left-color: #90caf9; }
        .sidebar nav a.logout { margin-top: 2rem; color: #ffcdd2; }
        .main-content { flex: 1; padding: 2rem; }
        .page-header { margin-bottom: 1.5rem; }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1565c0; }
        .page-header p { margin: 0.5rem 0 0; color: #666; }
        .flash { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: white; border-radius: 12px; padding: 1.25rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .stat-card h3 { margin: 0; font-size: 1.5rem; font-weight: 700; color: #333; }
        .stat-card p { margin: 0.25rem 0 0; font-size: 0.85rem; color: #888; }
        .stat-card.blue h3 { color: #1565c0; }
        .stat-card.green h3 { color: #2e7d32; }
        .stat-card.orange h3 { color: #f57c00; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; }
        .card-header h3 { margin: 0; font-size: 1rem; }
        .card-body { padding: 1rem 1.5rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; }
        th { font-size: 0.8rem; color: #888; text-transform: uppercase; }
        .badge { padding: 0.25rem 0.5rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-warning { background: #fff3e0; color: #f57c00; }
        .btn { padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; background: #1565c0; color: white; }
        .btn:hover { background: #0d47a1; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>📦 <?= APP_NAME ?></h2>
                <small><?= htmlspecialchars($bank['nama'] ?? 'Bank Sampah') ?></small>
            </div>
            <nav>
                <a href="<?= base_url('pengelola/dashboard') ?>" class="active">📊 Dashboard</a>
                <a href="<?= base_url('pengelola/transaksi-beli') ?>">🛒 Transaksi Beli</a>
                <a href="<?= base_url('pengelola/transaksi-jual') ?>">📤 Transaksi Jual</a>
                <a href="<?= base_url('pengelola/kategori') ?>">📦 Kategori Sampah</a>
                <a href="<?= base_url('pengelola/harga') ?>">💰 Harga Sampah</a>
                <a href="<?= base_url('pengelola/nasabah') ?>">👥 Data Nasabah</a>
                <a href="<?= base_url('pengelola/pencairan') ?>">💵 Pencairan</a>
                <a href="<?= base_url('pengelola/pengepul') ?>">🚛 Pengepul</a>
                <a href="<?= base_url('pengelola/laporan') ?>">📊 Laporan</a>
                <a href="<?= base_url('logout') ?>" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if ($flash): ?>
                <div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

            <div class="page-header">
                <h1>Selamat Datang, <?= htmlspecialchars($user['nama']) ?>!</h1>
                <p>Kelola operasional bank sampah <?= htmlspecialchars($bank['nama'] ?? '') ?></p>
            </div>

            <div class="stats-grid">
                <div class="stat-card blue">
                    <h3><?= number_format($statsBeli['total_transaksi'] ?? 0) ?></h3>
                    <p>Total Transaksi Beli</p>
                </div>
                <div class="stat-card green">
                    <h3><?= formatRupiah($statsBeli['total_nilai'] ?? 0) ?></h3>
                    <p>Total Nilai Beli</p>
                </div>
                <div class="stat-card orange">
                    <h3><?= number_format($statsJual['total_transaksi'] ?? 0) ?></h3>
                    <p>Total Transaksi Jual</p>
                </div>
                <div class="stat-card">
                    <h3><?= number_format(array_sum(array_column($stokList, 'berat_kg')), 1) ?> kg</h3>
                    <p>Total Stok</p>
                </div>
                <?php if ($statsPencairan): ?>
                <div class="stat-card" style="border-left: 4px solid #f57c00;">
                    <h3 style="color: #f57c00;"><?= ($statsPencairan['DIAJUKAN']['jumlah'] ?? 0) + ($statsPencairan['DISETUJUI']['jumlah'] ?? 0) ?></h3>
                    <p>Pengajuan Pencairan</p>
                </div>
                <?php endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="card">
                    <div class="card-header">
                        <h3>🛒 Transaksi Beli Terakhir</h3>
                        <a href="<?= base_url('pengelola/transaksi-beli') ?>" style="color: #1565c0; font-size: 0.85rem;">Lihat Semua</a>
                    </div>
                    <table>
                        <thead><tr><th>Tanggal</th><th>Penjual</th><th>Total</th></tr></thead>
                        <tbody>
                            <?php foreach ($recentBeli as $t): ?>
                                <tr>
                                    <td><?= date('d/m H:i', strtotime($t['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($t['nasabah_nama'] ?? $t['nama_penjual'] ?? '-') ?></td>
                                    <td><?= formatRupiah($t['total_nilai']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentBeli)): ?>
                                <tr><td colspan="3" style="text-align:center;color:#888">Belum ada transaksi</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>📦 Stok Sampah</h3>
                    </div>
                    <table>
                        <thead><tr><th>Kategori</th><th>Stok (kg)</th></tr></thead>
                        <tbody>
                            <?php foreach (array_slice($stokList, 0, 5) as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['kategori_nama']) ?></td>
                                    <td><?= number_format($s['berat_kg'], 1) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($stokList)): ?>
                                <tr><td colspan="2" style="text-align:center;color:#888">Stok kosong</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="margin-top: 1.5rem;">
                <a href="<?= base_url('pengelola/transaksi-beli/add') ?>" class="btn" style="margin-right: 0.5rem;">➕ Transaksi Beli Baru</a>
                <a href="<?= base_url('pengelola/transaksi-jual/add') ?>" class="btn">📤 Transaksi Jual Baru</a>
            </div>
        </main>
    </div>
</body>
</html>
