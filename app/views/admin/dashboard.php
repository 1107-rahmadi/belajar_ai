<?php
/**
 * Dashboard Admin
 *
 * Statistik dan overview sistem
 */

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f7fa; }

        .dashboard-wrapper { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1b5e20 0%, #2e7d32 100%);
            color: white;
            flex-shrink: 0;
        }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { margin: 0; font-size: 1.1rem; }
        .sidebar-header small { opacity: 0.7; }
        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 0.875rem 1.5rem;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        .sidebar nav a:hover { background: rgba(255,255,255,0.1); color: white; }
        .sidebar nav a.active { background: rgba(255,255,255,0.15); color: white; border-left-color: #a5d6a7; }
        .sidebar nav a.logout { margin-top: 2rem; color: #ffcdd2; }

        /* Main Content */
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }

        /* Page Header */
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { margin: 0 0 0.5rem 0; font-size: 1.75rem; color: #1b5e20; }
        .page-header p { margin: 0; color: #666; }

        /* Flash Messages */
        .flash { padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
        .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; }
        .stat-icon.blue { background: #e3f2fd; color: #1976d2; }
        .stat-icon.green { background: #e8f5e9; color: #2e7d32; }
        .stat-icon.orange { background: #fff3e0; color: #f57c00; }
        .stat-icon.purple { background: #f3e5f5; color: #7b1fa2; }
        .stat-info h3 { margin: 0; font-size: 1.75rem; font-weight: 700; color: #333; }
        .stat-info p { margin: 0.25rem 0 0 0; color: #888; font-size: 0.9rem; }

        /* Card */
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 1.5rem; }
        .card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .card-header h3 { margin: 0; font-size: 1rem; color: #333; }
        .card-body { padding: 1.5rem; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.875rem 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #555; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:hover { background: #f8f9fa; }
        td { color: #333; }

        /* Badges */
        .badge { padding: 0.3rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-block; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-warning { background: #fff3e0; color: #f57c00; }
        .badge-danger { background: #ffebee; color: #c62828; }
        .badge-info { background: #e3f2fd; color: #1976d2; }
        .badge-secondary { background: #eceff1; color: #546e7a; }

        /* Quick Links */
        .quick-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        .quick-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.2s;
        }
        .quick-link:hover { border-color: #2e7d32; background: #f1f8e9; }
        .quick-link-icon { width: 40px; height: 40px; background: #e8f5e9; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .quick-link h4 { margin: 0; font-size: 0.9rem; }
        .quick-link p { margin: 0; font-size: 0.8rem; color: #888; }

        /* Welcome Card */
        .welcome-card { background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%); color: white; border-radius: 12px; padding: 2rem; margin-bottom: 2rem; }
        .welcome-card h1 { margin: 0 0 0.5rem 0; font-size: 1.5rem; }
        .welcome-card p { margin: 0; opacity: 0.9; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🏛️ <?= APP_NAME ?></h2>
                <small>Panel Administrator</small>
            </div>
            <nav>
                <a href="<?= base_url('admin/dashboard') ?>" class="active">📊 Dashboard</a>
                <a href="<?= base_url('admin/users') ?>">👥 Kelola Pengguna</a>
                <a href="<?= base_url('admin/nasabah') ?>">🧑 Kelola Nasabah</a>
                <a href="<?= base_url('admin/bank-sampah') ?>">🏢 Kelola Bank Sampah</a>
                <a href="<?= base_url('admin/desa') ?>">📍 Kelola Desa</a>
                <a href="<?= base_url('admin/pengaturan') ?>">⚙️ Pengaturan</a>
                <a href="<?= base_url('logout') ?>" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if ($flash): ?>
                <div class="flash flash-<?= $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="welcome-card">
                <h1>Selamat Datang, <?= htmlspecialchars($user['nama']) ?>!</h1>
                <p>Kelola sistem Bank Sampah Digital Desa dari panel ini.</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">👥</div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['total_users']) ?></h3>
                        <p>Total Pengguna</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">🧑</div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['total_users']) ?></h3>
                        <p>Total Nasabah</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">🏢</div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['total_bank']) ?></h3>
                        <p>Bank Sampah (<?= $stats['bank_aktif'] ?> Aktif)</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">📍</div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['total_desa']) ?></h3>
                        <p>Desa</p>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                <!-- Recent Users -->
                <div class="card">
                    <div class="card-header">
                        <h3>Pengguna Terbaru</h3>
                        <a href="<?= base_url('admin/users') ?>" style="color: #2e7d32; text-decoration: none; font-size: 0.9rem;">Lihat Semua →</a>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $u): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($u['nama']) ?></strong><br>
                                            <small style="color: #888;"><?= htmlspecialchars($u['no_hp']) ?></small>
                                        </td>
                                        <td><span class="badge badge-<?= $u['role'] === 'ADMIN' ? 'danger' : ($u['role'] === 'PENGELOLA' ? 'warning' : 'info') ?>"><?= $u['role'] ?></span></td>
                                        <td><span class="badge badge-<?= $u['status'] === 'AKTIF' ? 'success' : 'secondary' ?>"><?= $u['status'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentUsers)): ?>
                                    <tr><td colspan="3" style="text-align: center; color: #888;">Belum ada data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h3>Aksi Cepat</h3>
                        </div>
                        <div class="card-body">
                            <div class="quick-links" style="grid-template-columns: 1fr;">
                                <a href="<?= base_url('admin/users?action=add') ?>" class="quick-link">
                                    <div class="quick-link-icon">➕</div>
                                    <div>
                                        <h4>Tambah Pengguna</h4>
                                        <p>Buat akun baru</p>
                                    </div>
                                </a>
                                <a href="<?= base_url('admin/nasabah?action=add') ?>" class="quick-link">
                                    <div class="quick-link-icon">👤</div>
                                    <div>
                                        <h4>Tambah Nasabah</h4>
                                        <p>Daftarkan anggota baru</p>
                                    </div>
                                </a>
                                <a href="<?= base_url('admin/bank-sampah?action=add') ?>" class="quick-link">
                                    <div class="quick-link-icon">🏢</div>
                                    <div>
                                        <h4>Tambah Bank Sampah</h4>
                                        <p>Buat unit baru</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
