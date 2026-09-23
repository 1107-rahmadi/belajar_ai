<?php
function formatRupiah($n): string { return 'Rp ' . number_format($n, 0, ',', '.'); }
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - <?= APP_NAME ?></title>
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
        .flash { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .flash-info { background: #d1ecf1; color: #0c5460; }
        .filter-form { display: flex; gap: 1rem; padding: 1rem 1.5rem; background: #f8f9fa; border-radius: 12px 12px 0 0; }
        .filter-form select, .filter-form input, .filter-form button { padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid #ddd; }
        .filter-form button { background: #6a1b9a; color: white; cursor: pointer; border: none; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; }
        .card-header h3 { margin: 0; font-size: 1rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 1.5rem; }
        .stat-box { background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center; }
        .stat-box h4 { margin: 0 0 0.25rem; font-size: 1.25rem; }
        .stat-box.green h4 { color: #2e7d32; }
        .stat-box.orange h4 { color: #f57c00; }
        .stat-box.purple h4 { color: #6a1b9a; }
        .stat-box p { margin: 0; font-size: 0.8rem; color: #888; }
        .summary-table { width: 100%; border-collapse: collapse; margin: 1rem 1.5rem; }
        .summary-table td { padding: 0.5rem 1rem; }
        .summary-table td:first-child { color: #666; }
        .summary-table td:last-child { font-weight: 600; text-align: right; }
        .summary-table tr.total td { border-top: 2px solid #eee; font-size: 1.1rem; }
        .btn { padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; background: #6a1b9a; color: white; }
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
                <a href="<?= base_url('kades/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('kades/monitoring') ?>">📈 Monitoring</a>
                <a href="<?= base_url('kades/laporan') ?>" class="active">💰 Laporan Keuangan</a>
                <a href="<?= base_url('logout') ?>" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if ($flash): ?>
                <div class="flash flash-info"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

            <div class="page-header">
                <h1>💰 Laporan Keuangan</h1>
                <div class="breadcrumb"><a href="<?= base_url('kades/dashboard') ?>">Dashboard</a> / Laporan</div>
            </div>

            <div class="card">
                <form method="GET" class="filter-form">
                    <select name="bank_id">
                        <option value="">Semua Bank Sampah</option>
                        <?php foreach ($banks as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= (isset($_GET['bank_id']) && $_GET['bank_id'] == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="tanggal_from" value="<?= $tanggalFrom ?>">
                    <span>s/d</span>
                    <input type="date" name="tanggal_to" value="<?= $tanggalTo ?>">
                    <button type="submit">🔍 Tampilkan</button>
                </form>

                <div class="stats-grid">
                    <div class="stat-box">
                        <h4><?= number_format($stats['total_transaksi_beli']) ?></h4>
                        <p>Transaksi Beli</p>
                    </div>
                    <div class="stat-box">
                        <h4><?= number_format($stats['total_berat_beli'], 1) ?> kg</h4>
                        <p>Total Berat Beli</p>
                    </div>
                    <div class="stat-box green">
                        <h4><?= formatRupiah($stats['total_nilai_beli']) ?></h4>
                        <p>Total Pembelian</p>
                    </div>
                    <div class="stat-box">
                        <h4><?= number_format($stats['total_transaksi_jual']) ?></h4>
                        <p>Transaksi Jual</p>
                    </div>
                    <div class="stat-box">
                        <h4><?= number_format($stats['total_berat_jual'], 1) ?> kg</h4>
                        <p>Total Berat Jual</p>
                    </div>
                    <div class="stat-box orange">
                        <h4><?= formatRupiah($stats['total_nilai_jual']) ?></h4>
                        <p>Total Penjualan</p>
                    </div>
                </div>

                <div class="card-header"><h3>📋 Ringkasan Keuangan</h3></div>
                <table class="summary-table">
                    <tr>
                        <td>Total Pembelian dari Warga</td>
                        <td><?= formatRupiah($stats['total_nilai_beli']) ?></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;- Via Tabungan Nasabah</td>
                        <td><?= formatRupiah($stats['total_tabungan']) ?></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;- Via Tunai</td>
                        <td><?= formatRupiah($stats['total_tunai']) ?></td>
                    </tr>
                    <tr>
                        <td>Total Penjualan ke Pengepul</td>
                        <td><?= formatRupiah($stats['total_nilai_jual']) ?></td>
                    </tr>
                    <tr>
                        <td>Piutang (Belum Lunas)</td>
                        <td style="color:#f57c00;"><?= formatRupiah($stats['total_piutang']) ?></td>
                    </tr>
                    <tr class="total" style="background:#f8f9fa;">
                        <td style="font-weight:700;">Estimasi Keuntungan</td>
                        <td style="color:<?= $stats['keuntungan'] >= 0 ? '#2e7d32' : '#c62828' ?>; font-size:1.25rem;">
                            <?= formatRupiah($stats['keuntungan']) ?>
                        </td>
                    </tr>
                </table>

                <div style="padding:1rem 1.5rem; border-top:1px solid #eee;">
                    <p style="margin:0 0 1rem; color:#888; font-size:0.9rem;">📝 *Estimasi keuntungan = Total Penjualan - Total Pembelian</p>
                    <div style="display:flex; gap:1rem;">
                        <a href="<?= base_url('kades/export?format=pdf') ?>" class="btn">📄 Export PDF</a>
                        <a href="<?= base_url('kades/export?format=excel') ?>" class="btn">📊 Export Excel</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
