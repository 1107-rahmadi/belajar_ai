<?php
/**
 * Pengaturan Sistem - Placeholder
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - <?= APP_NAME ?></title>
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
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; }
        .card-header h3 { margin: 0; font-size: 1rem; color: #333; }
        .card-body { padding: 1.5rem; }
        .placeholder { text-align: center; padding: 3rem; color: #888; }
        .placeholder-icon { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
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
                <a href="<?= base_url('admin/pengaturan') ?>" class="active">⚙️ Pengaturan</a>
                <a href="<?= base_url('logout') ?>" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>⚙️ Pengaturan Sistem</h1>
                <div class="breadcrumb"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a> / Pengaturan</div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Pengaturan Umum</h3></div>
                <div class="placeholder">
                    <div class="placeholder-icon">⚙️</div>
                    <h3>Fitur dalam pengembangan</h3>
                    <p>Pengaturan sistem seperti nama aplikasi, logo, konfigurasi email, dll.</p>
                    <p>Fitur ini akan segera ditambahkan.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Informasi Sistem</h3></div>
                <div class="card-body">
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 0.5rem; width: 200px; color: #666;">Nama Aplikasi</td>
                            <td style="padding: 0.5rem;"><strong><?= APP_NAME ?></strong></td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem; color: #666;">Versi</td>
                            <td style="padding: 0.5rem;">1.0.0</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem; color: #666;">PHP Version</td>
                            <td style="padding: 0.5rem;"><?= phpversion() ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
