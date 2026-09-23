<?php
$isEdit = isset($editPengepul) && $editPengepul;
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Tambah' ?> Pengepul - <?= APP_NAME ?></title>
    <style>
        * { box-sizing: border-box; } body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #1565c0, #1976d2); color: white; flex-shrink: 0; }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { margin: 0; }
        .sidebar nav a { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.875rem 1.5rem; }
        .main-content { flex: 1; padding: 2rem; }
        .page-header h1 { margin: 0 0 1rem; font-size: 1.5rem; color: #1565c0; }
        .breadcrumb { color: #888; font-size: 0.9rem; margin-bottom: 1.5rem; }
        .breadcrumb a { color: #1565c0; text-decoration: none; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; }
        .card-header h3 { margin: 0; }
        .card-body { padding: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
        .form-group label span { color: #e53935; }
        .form-control { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; }
        .form-control:focus { outline: none; border-color: #1565c0; }
        select.form-control { background: white; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 1rem; font-weight: 600; border: none; cursor: pointer; }
        .btn-primary { background: #1565c0; color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .form-actions { display: flex; gap: 1rem; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><h2>📦 <?= APP_NAME ?></h2></div>
            <nav>
                <a href="<?= base_url('pengelola/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('pengelola/pengepul') ?>">🚛 Pengepul</a>
                <a href="<?= base_url('logout') ?>">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if (!empty($errors)): ?>
                <div class="alert"><strong>Kesalahan:</strong><ul style="margin:0.5rem 0 0 1.5rem;"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>

            <div class="page-header">
                <h1><?= $isEdit ? '✏️ Edit' : '➕ Tambah' ?> Pengepul</h1>
                <div class="breadcrumb"><a href="<?= base_url('pengelola/dashboard') ?>">Dashboard</a> / <a href="<?= base_url('pengelola/pengepul') ?>">Pengepul</a> / <?= $isEdit ? 'Edit' : 'Tambah' ?></div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Form Pengepul</h3></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label>Nama Pengepul <span>*</span></label>
                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($editPengepul['nama'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>No HP</label>
                            <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($editPengepul['no_hp'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($editPengepul['alamat'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="AKTIF" <?= (isset($editPengepul['status']) && $editPengepul['status'] === 'AKTIF') || !isset($editPengepul) ? 'selected' : '' ?>>AKTIF</option>
                                <option value="NONAKTIF" <?= isset($editPengepul['status']) && $editPengepul['status'] === 'NONAKTIF' ? 'selected' : '' ?>>NONAKTIF</option>
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Simpan</button>
                            <a href="<?= base_url('pengelola/pengepul') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
