<?php
function formatRupiah($n): string { return 'Rp ' . number_format($n, 0, ',', '.'); }
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - <?= APP_NAME ?></title>
    <style>
        * { box-sizing: border-box; } body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #1565c0, #1976d2); color: white; flex-shrink: 0; }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { margin: 0; }
        .sidebar nav a { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.875rem 1.5rem; border-left: 3px solid transparent; }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.15); color: white; }
        .sidebar nav a.logout { margin-top: 2rem; color: #ffcdd2; }
        .main-content { flex: 1; padding: 2rem; }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1565c0; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #1565c0; text-decoration: none; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; }
        .card-header h3 { margin: 0; }
        .card-body { padding: 1.5rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        .stat-box { background: #f8f9fa; padding: 1.25rem; border-radius: 8px; text-align: center; }
        .stat-box h4 { margin: 0 0 0.5rem; font-size: 1.5rem; color: #333; }
        .stat-box.green h4 { color: #2e7d32; }
        .stat-box.orange h4 { color: #f57c00; }
        .stat-box p { margin: 0; font-size: 0.85rem; color: #888; }
        .filter-form { display: flex; gap: 1rem; padding: 1rem 1.5rem; background: #f8f9fa; }
        .filter-form input, .filter-form button { padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid #ddd; }
        .filter-form button { background: #1565c0; color: white; cursor: pointer; border: none; }
        .btn { padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { background: #1565c0; color: white; }
        .btn-success { background: #2e7d32; color: white; }
        .btn-success:hover { background: #1b5e20; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><h2>📦 <?= APP_NAME ?></h2></div>
            <nav>
                <a href="<?= base_url('pengelola/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('pengelola/transaksi-beli') ?>">🛒 Transaksi Beli</a>
                <a href="<?= base_url('pengelola/transaksi-jual') ?>">📤 Transaksi Jual</a>
                <a href="<?= base_url('pengelola/kategori') ?>">📦 Kategori</a>
                <a href="<?= base_url('pengelola/harga') ?>">💰 Harga</a>
                <a href="<?= base_url('pengelola/nasabah') ?>">👥 Nasabah</a>
                <a href="<?= base_url('pengelola/pengepul') ?>">🚛 Pengepul</a>
                <a href="<?= base_url('pengelola/laporan') ?>" class="active">📊 Laporan</a>
                <a href="<?= base_url('logout') ?>" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>📊 Laporan Operasional</h1>
                <div class="breadcrumb">
                    <a href="<?= base_url('pengelola/dashboard') ?>">Dashboard</a> / Laporan
                </div>
            </div>

            <div style="margin-bottom: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <form method="GET" class="filter-form" style="margin-bottom:0; flex: 1; min-width: 300px;">
                    <input type="date" name="tanggal_from" value="<?= $tanggalFrom ?>">
                    <span>s/d</span>
                    <input type="date" name="tanggal_to" value="<?= $tanggalTo ?>">
                    <button type="submit">🔍 Tampilkan</button>
                </form>
                <a href="<?= base_url('pengelola/laporan/export-pdf?tanggal_from=' . $tanggalFrom . '&tanggal_to=' . $tanggalTo) ?>" class="btn btn-success" target="_blank">
                    📄 Export PDF
                </a>
            </div>

            <div class="card">
                <div class="card-header"><h3>🛒 Transaksi Beli</h3></div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-box">
                            <h4><?= number_format($statsBeli['total_transaksi'] ?? 0) ?></h4>
                            <p>Total Transaksi</p>
                        </div>
                        <div class="stat-box">
                            <h4><?= number_format($statsBeli['total_berat'] ?? 0, 1) ?> kg</h4>
                            <p>Total Berat</p>
                        </div>
                        <div class="stat-box green">
                            <h4><?= formatRupiah($statsBeli['total_nilai'] ?? 0) ?></h4>
                            <p>Total Nilai Beli</p>
                        </div>
                        <div class="stat-box">
                            <h4><?= formatRupiah($statsBeli['total_tabungan'] ?? 0) ?></h4>
                            <p>Ke Tabungan</p>
                        </div>
                        <div class="stat-box orange">
                            <h4><?= formatRupiah($statsBeli['total_tunai'] ?? 0) ?></h4>
                            <p>Ke Tunai</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>📤 Transaksi Jual</h3></div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-box">
                            <h4><?= number_format($statsJual['total_transaksi'] ?? 0) ?></h4>
                            <p>Total Transaksi</p>
                        </div>
                        <div class="stat-box">
                            <h4><?= number_format($statsJual['total_berat'] ?? 0, 1) ?> kg</h4>
                            <p>Total Berat</p>
                        </div>
                        <div class="stat-box green">
                            <h4><?= formatRupiah($statsJual['total_nilai'] ?? 0) ?></h4>
                            <p>Total Penjualan</p>
                        </div>
                        <div class="stat-box orange">
                            <h4><?= formatRupiah($statsJual['total_belum_bayar'] ?? 0) ?></h4>
                            <p>Piutang</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
