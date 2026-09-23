<?php
/**
 * Kelola Bank Sampah - List
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Bank Sampah - <?= APP_NAME ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #1b5e20, #2e7d32); color: white; flex-shrink: 0; }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { margin: 0; font-size: 1.1rem; }
        .sidebar nav a { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.875rem 1.5rem; border-left: 3px solid transparent; }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.15); color: white; border-left-color: #a5d6a7; }
        .sidebar nav a.logout { margin-top: 2rem; color: #ffcdd2; }
        .main-content { flex: 1; padding: 2rem; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1b5e20; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #2e7d32; text-decoration: none; }
        .flash { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .card-header h3 { margin: 0; font-size: 1rem; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.875rem 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #555; font-size: 0.8rem; text-transform: uppercase; }
        tr:hover { background: #f8f9fa; }
        .btn { padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { background: #2e7d32; color: white; }
        .btn-primary:hover { background: #1b5e20; }
        .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
        .btn-warning { background: #fff3e0; color: #f57c00; }
        .btn-danger { background: #ffebee; color: #c62828; }
        .badge { padding: 0.3rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-secondary { background: #eceff1; color: #546e7a; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><h2>🏛️ <?= APP_NAME ?></h2></div>
            <nav>
                <a href="<?= base_url('admin/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('admin/users') ?>">👥 Kelola Pengguna</a>
                <a href="<?= base_url('admin/nasabah') ?>">🧑 Kelola Nasabah</a>
                <a href="<?= base_url('admin/bank-sampah') ?>" class="active">🏢 Kelola Bank Sampah</a>
                <a href="<?= base_url('admin/desa') ?>">📍 Kelola Desa</a>
                <a href="<?= base_url('admin/pengaturan') ?>">⚙️ Pengaturan</a>
                <a href="<?= base_url('logout') ?>" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if ($flash): ?>
                <div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

            <div class="page-header">
                <div>
                    <h1>🏢 Kelola Bank Sampah</h1>
                    <div class="breadcrumb"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a> / Kelola Bank Sampah</div>
                </div>
                <a href="<?= base_url('admin/bank-sampah/add') ?>" class="btn btn-primary">➕ Tambah Bank Sampah</a>
            </div>

            <div class="card">
                <div class="card-header"><h3>Daftar Bank Sampah</h3></div>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Desa</th>
                            <th>Penanggung Jawab</th>
                            <th>No HP</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($banks as $b): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($b['nama']) ?></strong><br><small style="color: #888;"><?= htmlspecialchars($b['alamat'] ?? '-') ?></small></td>
                                <td><?= htmlspecialchars($b['desa_nama'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($b['penanggung_jawab'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($b['no_hp'] ?? '-') ?></td>
                                <td><span class="badge badge-<?= $b['status'] === 'AKTIF' ? 'success' : 'secondary' ?>"><?= $b['status'] ?></span></td>
                                <td>
                                    <a href="<?= base_url('admin/bank-sampah/edit?id=' . $b['id']) ?>" class="btn btn-sm btn-warning">✏️</a>
                                    <a href="<?= base_url('admin/bank-sampah/delete?id=' . $b['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">🗑️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($banks)): ?>
                            <tr><td colspan="7" style="text-align: center; color: #888; padding: 2rem;">Belum ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
