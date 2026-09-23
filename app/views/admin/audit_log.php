<?php
/**
 * Audit Log - Admin View
 */
$user = currentUser();
$logs = $logs ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log - <?= APP_NAME ?></title>
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
        .page-header { margin-bottom: 1.5rem; }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1b5e20; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #2e7d32; text-decoration: none; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; }
        .card-header h3 { margin: 0; font-size: 1rem; }
        .filter-form { display: flex; gap: 1rem; padding: 1rem 1.5rem; background: #f8f9fa; border-radius: 12px 12px 0 0; flex-wrap: wrap; }
        .filter-form select, .filter-form input, .filter-form button { padding: 0.5rem; border-radius: 6px; border: 1px solid #ddd; font-size: 0.9rem; }
        .filter-form button { background: #2e7d32; color: white; cursor: pointer; border: none; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.875rem 1rem; text-align: left; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        th { background: #f8f9fa; font-size: 0.8rem; color: #888; text-transform: uppercase; }
        .badge { padding: 0.25rem 0.5rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-block; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-warning { background: #fff3e0; color: #f57c00; }
        .badge-danger { background: #ffebee; color: #c62828; }
        .badge-info { background: #e3f2fd; color: #1976d2; }
        .json-view { background: #f8f9fa; padding: 0.5rem; border-radius: 4px; font-family: monospace; font-size: 0.8rem; max-width: 300px; overflow-x: auto; white-space: pre; }
        .empty-state { text-align: center; padding: 3rem; color: #888; }
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
                <a href="<?= base_url('admin/bank-sampah') ?>">🏢 Kelola Bank Sampah</a>
                <a href="<?= base_url('admin/desa') ?>">📍 Kelola Desa</a>
                <a href="<?= base_url('admin/audit-log') ?>" class="active">📋 Audit Log</a>
                <a href="<?= base_url('logout') ?>" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>📋 Audit Log</h1>
                <div class="breadcrumb"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a> / Audit Log</div>
            </div>

            <div class="card">
                <form method="GET" class="filter-form">
                    <select name="tabel">
                        <option value="">Semua Tabel</option>
                        <option value="users" <?= (isset($_GET['tabel']) && $_GET['tabel'] === 'users') ? 'selected' : '' ?>>Users</option>
                        <option value="nasabah" <?= (isset($_GET['tabel']) && $_GET['tabel'] === 'nasabah') ? 'selected' : '' ?>>Nasabah</option>
                        <option value="transaksi_beli" <?= (isset($_GET['tabel']) && $_GET['tabel'] === 'transaksi_beli') ? 'selected' : '' ?>>Transaksi Beli</option>
                        <option value="transaksi_jual" <?= (isset($_GET['tabel']) && $_GET['tabel'] === 'transaksi_jual') ? 'selected' : '' ?>>Transaksi Jual</option>
                    </select>
                    <select name="aksi">
                        <option value="">Semua Aksi</option>
                        <option value="INSERT" <?= (isset($_GET['aksi']) && $_GET['aksi'] === 'INSERT') ? 'selected' : '' ?>>INSERT</option>
                        <option value="UPDATE" <?= (isset($_GET['aksi']) && $_GET['aksi'] === 'UPDATE') ? 'selected' : '' ?>>UPDATE</option>
                        <option value="DELETE" <?= (isset($_GET['aksi']) && $_GET['aksi'] === 'DELETE') ? 'selected' : '' ?>>DELETE</option>
                    </select>
                    <input type="date" name="tanggal_from" value="<?= $_GET['tanggal_from'] ?? '' ?>">
                    <span>s/d</span>
                    <input type="date" name="tanggal_to" value="<?= $_GET['tanggal_to'] ?? '' ?>">
                    <button type="submit">🔍 Filter</button>
                </form>

                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Aksi</th>
                            <th>Tabel</th>
                            <th>Record ID</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                <td><?= htmlspecialchars($log['user_nama'] ?? 'System') ?></td>
                                <td>
                                    <?php
                                    $badgeClass = 'badge-info';
                                    if ($log['aksi'] === 'INSERT') $badgeClass = 'badge-success';
                                    elseif ($log['aksi'] === 'UPDATE') $badgeClass = 'badge-warning';
                                    elseif ($log['aksi'] === 'DELETE') $badgeClass = 'badge-danger';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $log['aksi'] ?></span>
                                </td>
                                <td><?= htmlspecialchars($log['tabel']) ?></td>
                                <td><?= $log['record_id'] ?></td>
                                <td>
                                    <?php if ($log['sebelum']): ?>
                                        <details>
                                            <summary style="cursor:pointer; color:#1976d2;">Sebelum</summary>
                                            <div class="json-view"><?= htmlspecialchars($log['sebelum']) ?></div>
                                        </details>
                                    <?php endif; ?>
                                    <?php if ($log['sesudah']): ?>
                                        <details>
                                            <summary style="cursor:pointer; color:#2e7d32;">Sesudah</summary>
                                            <div class="json-view"><?= htmlspecialchars($log['sesudah']) ?></div>
                                        </details>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="6" class="empty-state">Tidak ada data log</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
