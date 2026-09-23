<?php
/**
 * Form Tambah/Edit Pengguna
 */
$isEdit = isset($editUser) && $editUser;
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Tambah' ?> Pengguna - <?= APP_NAME ?></title>
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
        .flash { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; }
        .card-header h3 { margin: 0; font-size: 1rem; color: #333; }
        .card-body { padding: 1.5rem; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group.full { grid-column: span 2; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: #333; }
        .form-group label span { color: #e53935; }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; }
        .form-control:focus { outline: none; border-color: #2e7d32; box-shadow: 0 0 0 3px rgba(46,125,50,0.1); }
        select.form-control { background: white; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-size: 1rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; border: none; cursor: pointer; }
        .btn-primary { background: #2e7d32; color: white; }
        .btn-primary:hover { background: #1b5e20; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn-secondary:hover { background: #bdbdbd; }
        .form-actions { display: flex; gap: 1rem; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🏛️ <?= APP_NAME ?></h2>
            </div>
            <nav>
                <a href="<?= base_url('admin/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('admin/users') ?>" class="active">👥 Kelola Pengguna</a>
                <a href="<?= base_url('admin/nasabah') ?>">🧑 Kelola Nasabah</a>
                <a href="<?= base_url('admin/bank-sampah') ?>">🏢 Kelola Bank Sampah</a>
                <a href="<?= base_url('admin/desa') ?>">📍 Kelola Desa</a>
                <a href="<?= base_url('admin/pengaturan') ?>">⚙️ Pengaturan</a>
                <a href="<?= base_url('logout') ?>" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if ($flash): ?>
                <div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert">
                    <strong>Terjadi kesalahan:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem;">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1><?= $isEdit ? '✏️ Edit' : '➕ Tambah' ?> Pengguna</h1>
                <div class="breadcrumb"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a> / <a href="<?= base_url('admin/users') ?>">Pengguna</a> / <?= $isEdit ? 'Edit' : 'Tambah' ?></div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Form Data Pengguna</h3></div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Nama Lengkap <span>*</span></label>
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($editUser['nama'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>No HP <span>*</span></label>
                                <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($editUser['no_hp'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editUser['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Role <span>*</span></label>
                                <select name="role" class="form-control" required>
                                    <option value="">-- Pilih Role --</option>
                                    <option value="ADMIN" <?= (isset($editUser['role']) && $editUser['role'] === 'ADMIN') ? 'selected' : '' ?>>ADMIN</option>
                                    <option value="PENGELOLA" <?= (isset($editUser['role']) && $editUser['role'] === 'PENGELOLA') ? 'selected' : '' ?>>PENGELOLA</option>
                                    <option value="KADES" <?= (isset($editUser['role']) && $editUser['role'] === 'KADES') ? 'selected' : '' ?>>KEPALA DESA</option>
                                    <option value="NASABAH" <?= (isset($editUser['role']) && $editUser['role'] === 'NASABAH') ? 'selected' : '' ?>>NASABAH</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="AKTIF" <?= (isset($editUser['status']) && $editUser['status'] === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                                    <option value="NONAKTIF" <?= (isset($editUser['status']) && $editUser['status'] === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Desa</label>
                                <select name="desa_id" class="form-control">
                                    <option value="">-- Tidak Ada --</option>
                                    <?php foreach ($desas as $d): ?>
                                        <option value="<?= $d['id'] ?>" <?= (isset($editUser['desa_id']) && $editUser['desa_id'] == $d['id']) ? 'selected' : '' ?>><?= htmlspecialchars($d['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Bank Sampah</label>
                                <select name="bank_sampah_id" class="form-control">
                                    <option value="">-- Tidak Ada --</option>
                                    <?php foreach ($banks as $b): ?>
                                        <option value="<?= $b['id'] ?>" <?= (isset($editUser['bank_sampah_id']) && $editUser['bank_sampah_id'] == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group full">
                                <label>Password <?= $isEdit ? '(kosongkan jika tidak diubah)' : '<span>*</span>' ?></label>
                                <input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Simpan</button>
                            <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
