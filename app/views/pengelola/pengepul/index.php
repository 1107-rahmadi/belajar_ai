<?php
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengepul - <?= APP_NAME ?></title>
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
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1565c0; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #1565c0; text-decoration: none; }
        .flash { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .flash-success { background: #d4edda; color: #155724; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; }
        .card-header h3 { margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.875rem 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-size: 0.8rem; color: #888; text-transform: uppercase; }
        .btn { padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; }
        .btn-primary { background: #1565c0; color: white; }
        .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
        .btn-warning { background: #fff3e0; color: #f57c00; }
        .btn-danger { background: #ffebee; color: #c62828; }
        .badge { padding: 0.25rem 0.5rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-secondary { background: #eceff1; color: #546e7a; }
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
                <a href="<?= base_url('pengelola/pengepul') ?>" class="active">🚛 Pengepul</a>
                <a href="<?= base_url('logout') ?>" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if ($flash): ?><div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div><?php endif; ?>

            <div class="page-header">
                <div>
                    <h1>🚛 Data Pengepul</h1>
                    <div class="breadcrumb"><a href="<?= base_url('pengelola/dashboard') ?>">Dashboard</a> / Pengepul</div>
                </div>
                <a href="<?= base_url('pengelola/pengepul/add') ?>" class="btn btn-primary">➕ Tambah Pengepul</a>
            </div>

            <div class="card">
                <table>
                    <thead><tr><th>No</th><th>Nama</th><th>No HP</th><th>Alamat</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php $no = 1; foreach ($pengepulList as $p): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                                <td><?= htmlspecialchars($p['no_hp'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($p['alamat'] ?? '-') ?></td>
                                <td><span class="badge badge-<?= $p['status'] === 'AKTIF' ? 'success' : 'secondary' ?>"><?= $p['status'] ?></span></td>
                                <td>
                                    <a href="<?= base_url('pengelola/pengepul/edit?id=' . $p['id']) ?>" class="btn btn-sm btn-warning">✏️</a>
                                    <a href="<?= base_url('pengelola/pengepul/delete?id=' . $p['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">🗑️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pengepulList)): ?>
                            <tr><td colspan="6" style="text-align:center;color:#888;padding:2rem;">Belum ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
