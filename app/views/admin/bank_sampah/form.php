<?php
/**
 * Form Tambah/Edit Bank Sampah
 */
$isEdit = isset($editBank) && $editBank;
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Tambah' ?> Bank Sampah - <?= APP_NAME ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #1b5e20, #2e7d32); color: white; flex-shrink: 0; }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { margin: 0; font-size: 1.1rem; }
        .sidebar nav a { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.875rem 1.5rem; border-left: 3px solid transparent; }
        .sidebar nav a.logout { margin-top: 2rem; color: #ffcdd2; }
        .main-content { flex: 1; padding: 2rem; }
        .page-header { margin-bottom: 1.5rem; }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1b5e20; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #2e7d32; text-decoration: none; }
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
        .form-actions { display: flex; gap: 1rem; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><h2>🏛️ <?= APP_NAME ?></h2></div>
            <nav>
                <a href="<?= base_url('admin/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('admin/bank-sampah') ?>">🏢 Kelola Bank Sampah</a>
                <a href="<?= base_url('logout') ?>" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
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
                <h1><?= $isEdit ? '✏️ Edit' : '➕ Tambah' ?> Bank Sampah</h1>
                <div class="breadcrumb"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a> / <a href="<?= base_url('admin/bank-sampah') ?>">Bank Sampah</a> / <?= $isEdit ? 'Edit' : 'Tambah' ?></div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Form Data Bank Sampah</h3></div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Nama Bank Sampah <span>*</span></label>
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($editBank['nama'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Desa <span>*</span></label>
                                <select name="desa_id" class="form-control" required>
                                    <option value="">-- Pilih Desa --</option>
                                    <?php foreach ($desas as $d): ?>
                                        <option value="<?= $d['id'] ?>" <?= (isset($editBank['desa_id']) && $editBank['desa_id'] == $d['id']) ? 'selected' : '' ?>><?= htmlspecialchars($d['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group full">
                                <label>Alamat</label>
                                <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($editBank['alamat'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Penanggung Jawab</label>
                                <input type="text" name="penanggung_jawab" class="form-control" value="<?= htmlspecialchars($editBank['penanggung_jawab'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>No HP</label>
                                <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($editBank['no_hp'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Latitude</label>
                                <input type="text" name="latitude" class="form-control" value="<?= htmlspecialchars($editBank['latitude'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Longitude</label>
                                <input type="text" name="longitude" class="form-control" value="<?= htmlspecialchars($editBank['longitude'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="AKTIF" <?= (isset($editBank['status']) && $editBank['status'] === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                                    <option value="NONAKTIF" <?= (isset($editBank['status']) && $editBank['status'] === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Simpan</button>
                            <a href="<?= base_url('admin/bank-sampah') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
