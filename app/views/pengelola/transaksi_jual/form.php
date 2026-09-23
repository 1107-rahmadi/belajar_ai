<?php
function formatRupiah($n): string { return 'Rp ' . number_format($n, 0, ',', '.'); }
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Jual - <?= APP_NAME ?></title>
    <style>
        * { box-sizing: border-box; } body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #1565c0, #1976d2); color: white; flex-shrink: 0; }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { margin: 0; }
        .sidebar nav a { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.875rem 1.5rem; }
        .main-content { flex: 1; padding: 2rem; }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1565c0; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #1565c0; text-decoration: none; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; }
        .card-header h3 { margin: 0; }
        .card-body { padding: 1.5rem; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group.full { grid-column: span 2; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
        .form-control { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; }
        .form-control:focus { outline: none; border-color: #1565c0; }
        select.form-control { background: white; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 1rem; font-weight: 600; border: none; cursor: pointer; }
        .btn-primary { background: #1565c0; color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .form-actions { display: flex; gap: 1rem; margin-top: 1.5rem; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        .items-table th, .items-table td { padding: 0.5rem; border: 1px solid #eee; }
        .items-table th { background: #f8f9fa; }
        .items-table input, .items-table select { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
        .subtotal-cell { font-weight: 600; color: #f57c00; }
        .total-box { background: #fff3e0; padding: 1rem; border-radius: 8px; margin-top: 1rem; text-align: right; }
        .total-box h3 { margin: 0; color: #f57c00; font-size: 1.5rem; }
        .stok-info { background: #e3f2fd; padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.85rem; color: #1565c0; margin-top: 0.25rem; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><h2>📦 <?= APP_NAME ?></h2></div>
            <nav>
                <a href="<?= base_url('pengelola/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('pengelola/transaksi-jual') ?>">📤 Transaksi Jual</a>
                <a href="<?= base_url('logout') ?>">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if (!empty($errors)): ?>
                <div class="alert"><strong>Kesalahan:</strong><ul style="margin:0.5rem 0 0 1.5rem;"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>

            <div class="page-header" style="margin-bottom:1.5rem;">
                <h1>📤 Transaksi Jual Baru</h1>
                <div class="breadcrumb"><a href="<?= base_url('pengelola/dashboard') ?>">Dashboard</a> / <a href="<?= base_url('pengelola/transaksi-jual') ?>">Transaksi Jual</a> / Baru</div>
            </div>

            <form method="POST">
                <div class="card">
                    <div class="card-header"><h3>Data Pengepul</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Pilih Pengepul <span>*</span></label>
                            <select name="pengepul_id" class="form-control" required>
                                <option value="">-- Pilih Pengepul --</option>
                                <?php foreach ($pengepulList as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?> - <?= htmlspecialchars($p['no_hp'] ?? '-') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3>Item yang Dijual (Stok Tersedia)</h3></div>
                    <div class="card-body">
                        <table class="items-table">
                            <thead><tr><th>Kategori</th><th>Stok</th><th>Berat (kg)</th><th>Harga Jual/kg</th><th>Subtotal</th></tr></thead>
                            <tbody id="itemsBody">
                                <?php foreach ($stokList as $s): if ($s['berat_kg'] > 0): ?>
                                    <?php
                                    $h = null;
                                    foreach ($hargaMap as $hid => $hv) { if ($hid == $s['kategori_id']) { $h = $hv; break; } }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($s['kategori_nama']) ?><div class="stok-info">Stok: <?= number_format($s['berat_kg'], 1) ?> kg</div></td>
                                        <td><?= number_format($s['berat_kg'], 1) ?> kg</td>
                                        <td>
                                            <input type="number" name="berat[<?= $s['kategori_id'] ?>]" step="0.1" min="0" max="<?= $s['berat_kg'] ?>" class="berat-input" data-harga="<?= $h ? $h['harga_jual'] : 0 ?>" oninput="calcSubtotal(this)">
                                            <input type="hidden" name="kategori_id[]" value="<?= $s['kategori_id'] ?>">
                                        </td>
                                        <td><?= formatRupiah($h ? $h['harga_jual'] : 0) ?></td>
                                        <td class="subtotal-cell">Rp 0</td>
                                    </tr>
                                <?php endif; endforeach; ?>
                            </tbody>
                        </table>

                        <div class="total-box">
                            <span>Total Penjualan:</span>
                            <h3 id="totalDisplay">Rp 0</h3>
                        </div>

                        <div class="form-group" style="margin-top:1rem;">
                            <label><input type="checkbox" name="status_bayar" value="1"> Sudah Lunas</label>
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <input type="text" name="catatan" class="form-control" placeholder="Opsional">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                    <a href="<?= base_url('pengelola/transaksi-jual') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </main>
    </div>

    <script>
        function calcSubtotal(input) {
            var harga = parseFloat(input.dataset.harga) || 0;
            var berat = parseFloat(input.value) || 0;
            var row = input.closest('tr');
            row.querySelector('.subtotal-cell').textContent = 'Rp ' + Math.round(harga * berat).toLocaleString('id-ID');
            hitungTotal();
        }
        function hitungTotal() {
            var total = 0;
            document.querySelectorAll('.subtotal-cell').forEach(function(el) {
                total += parseInt(el.textContent.replace(/[^\d]/g, '')) || 0;
            });
            document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
    </script>
</body>
</html>
