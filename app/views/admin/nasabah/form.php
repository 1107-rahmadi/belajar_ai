<?php
/**
 * Form Tambah/Edit Nasabah
 */
$isEdit = isset($editNasabah) && $editNasabah;
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Tambah' ?> Nasabah - <?= APP_NAME ?></title>
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
                <a href="<?= base_url('admin/nasabah') ?>">🧑 Kelola Nasabah</a>
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
                <h1><?= $isEdit ? '✏️ Edit' : '➕ Tambah' ?> Nasabah</h1>
                <div class="breadcrumb"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a> / <a href="<?= base_url('admin/nasabah') ?>">Nasabah</a> / <?= $isEdit ? 'Edit' : 'Tambah' ?></div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Form Data Nasabah</h3></div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>No Anggota <span>*</span></label>
                                <input type="text" name="no_anggota" class="form-control" value="<?= htmlspecialchars($editNasabah['no_anggota'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Bank Sampah <span>*</span></label>
                                <select name="bank_sampah_id" class="form-control" required>
                                    <option value="">-- Pilih Bank Sampah --</option>
                                    <?php foreach ($banks as $b): ?>
                                        <option value="<?= $b['id'] ?>" <?= (isset($editNasabah['bank_sampah_id']) && $editNasabah['bank_sampah_id'] == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nama Lengkap <span>*</span></label>
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($editNasabah['nama'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>No HP <span>*</span></label>
                                <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($editNasabah['no_hp'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>NIK</label>
                                <input type="text" name="nik" class="form-control" value="<?= htmlspecialchars($editNasabah['nik'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Tipe</label>
                                <select name="tipe" class="form-control">
                                    <option value="PERORANGAN" <?= (isset($editNasabah['tipe']) && $editNasabah['tipe'] === 'PERORANGAN') ? 'selected' : '' ?>>PERORANGAN</option>
                                    <option value="INSTANSI" <?= (isset($editNasabah['tipe']) && $editNasabah['tipe'] === 'INSTANSI') ? 'selected' : '' ?>>INSTANSI</option>
                                </select>
                            </div>
                            <div class="form-group full">
                                <label>Alamat</label>
                                <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($editNasabah['alamat'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>RT</label>
                                <input type="text" name="rt" class="form-control" value="<?= htmlspecialchars($editNasabah['rt'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>RW</label>
                                <input type="text" name="rw" class="form-control" value="<?= htmlspecialchars($editNasabah['rw'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Nama Bank</label>
                                <input type="text" name="nama_bank" class="form-control" value="<?= htmlspecialchars($editNasabah['nama_bank'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>No Rekening</label>
                                <input type="text" name="no_rekening" class="form-control" value="<?= htmlspecialchars($editNasabah['no_rekening'] ?? '') ?>">
                            </div>
                            <?php if ($isEdit): ?>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="AKTIF" <?= (isset($editNasabah['status']) && $editNasabah['status'] === 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                                    <option value="NONAKTIF" <?= (isset($editNasabah['status']) && $editNasabah['status'] === 'NONAKTIF') ? 'selected' : '' ?>>NONAKTIF</option>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Simpan</button>
                            <a href="<?= base_url('admin/nasabah') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
