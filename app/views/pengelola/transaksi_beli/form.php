<?php
function formatRupiah($n): string { return 'Rp ' . number_format($n, 0, ',', '.'); }
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Beli Baru - <?= APP_NAME ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #1565c0, #1976d2); color: white; flex-shrink: 0; }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { margin: 0; font-size: 1.1rem; }
        .sidebar nav a { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.875rem 1.5rem; border-left: 3px solid transparent; }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.15); color: white; }
        .main-content { flex: 1; padding: 2rem; }
        .page-header { margin-bottom: 1.5rem; }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1565c0; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #1565c0; text-decoration: none; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; }
        .card-header h3 { margin: 0; font-size: 1rem; }
        .card-body { padding: 1.5rem; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem; }
        .form-group label span { color: #e53935; }
        .form-control { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; }
        .form-control:focus { outline: none; border-color: #1565c0; }
        select.form-control { background: white; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 1rem; font-weight: 600; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { background: #1565c0; color: white; }
        .btn-primary:hover { background: #0d47a1; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .form-actions { display: flex; gap: 1rem; margin-top: 1.5rem; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        .items-table th, .items-table td { padding: 0.5rem; border: 1px solid #eee; }
        .items-table th { background: #f8f9fa; font-size: 0.85rem; }
        .items-table input, .items-table select { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
        .subtotal-cell { font-weight: 600; color: #1565c0; }
        .total-box { background: #e3f2fd; padding: 1rem; border-radius: 8px; margin-top: 1rem; text-align: right; }
        .total-box h3 { margin: 0; color: #1565c0; font-size: 1.5rem; }
        .radio-group { display: flex; gap: 1rem; }
        .radio-option input { display: none; }
        .radio-option label { padding: 0.75rem 1.5rem; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; display: block; text-align: center; font-weight: 600; }
        .radio-option input:checked + label { border-color: #1565c0; background: #e3f2fd; color: #1565c0; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header"><h2>Bank Sampah</h2></div>
            <nav>
                <a href="<?= base_url('pengelola/dashboard') ?>">Dashboard</a>
                <a href="<?= base_url('pengelola/transaksi-beli') ?>">Transaksi Beli</a>
                <a href="<?= base_url('logout') ?>">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if (!empty($errors)): ?>
                <div class="alert">
                    <strong>Kesalahan:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem;">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1>Transaksi Beli Baru</h1>
                <div class="breadcrumb">
                    <a href="<?= base_url('pengelola/dashboard') ?>">Dashboard</a> /
                    <a href="<?= base_url('pengelola/transaksi-beli') ?>">Transaksi Beli</a> / Baru
                </div>
            </div>

            <form method="POST" action="">
                <div class="card">
                    <div class="card-header"><h3>Data Penjual</h3></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Tipe Penjual</label>
                                <div class="radio-group">
                                    <div class="radio-option">
                                        <input type="radio" name="tipe_penjual" id="tipeNasabah" value="NASABAH" checked onchange="togglePenjual()">
                                        <label for="tipeNasabah">Nasabah</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" name="tipe_penjual" id="tipeNon" value="NON_NASABAH" onchange="togglePenjual()">
                                        <label for="tipeNon">Non-Nasabah</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" id="fieldNasabah">
                                <label>Pilih Nasabah</label>
                                <select name="nasabah_id" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($nasabahList as $n): ?>
                                        <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['no_anggota']) ?> - <?= htmlspecialchars($n['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" id="fieldNama" style="display:none;">
                                <label>Nama Penjual</label>
                                <input type="text" name="nama_penjual" class="form-control" placeholder="Nama lengkap">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3>Item Sampah</h3></div>
                    <div class="card-body">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th style="width:40%">Kategori</th>
                                    <th style="width:20%">Berat (kg)</th>
                                    <th style="width:20%">Harga/kg</th>
                                    <th style="width:20%">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr>
                                    <td>
                                        <select name="kategori_id[]" class="kategori-select" onchange="updateHarga(this)">
                                            <option value="">-- Pilih --</option>
                                            <?php foreach ($hargaList as $h): ?>
                                                <option value="<?= $h['kategori_id'] ?>" data-harga="<?= $h['harga_beli'] ?>"><?= htmlspecialchars($h['kategori_nama']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input type="number" name="berat[]" step="0.1" min="0" class="berat-input" oninput="calcSubtotal(this)"></td>
                                    <td class="harga-cell">Rp 0</td>
                                    <td class="subtotal-cell">Rp 0</td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" onclick="addItem()" class="btn btn-secondary" style="margin-bottom:1rem;">+ Tambah Item</button>

                        <div class="total-box">
                            <h3>Total: <span id="totalDisplay">Rp 0</span></h3>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3>Detail Lainnya</h3></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Metode Bayar</label>
                                <select name="metode_bayar" class="form-control">
                                    <option value="TABUNG">Tabung ke Saldo</option>
                                    <option value="TUNAI">Tunai</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Catatan</label>
                                <input type="text" name="catatan" class="form-control" placeholder="Opsional">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                    <a href="<?= base_url('pengelola/transaksi-beli') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </main>
    </div>

    <script>
        function togglePenjual() {
            var isNasabah = document.getElementById('tipeNasabah').checked;
            document.getElementById('fieldNasabah').style.display = isNasabah ? 'block' : 'none';
            document.getElementById('fieldNama').style.display = isNasabah ? 'none' : 'block';
        }

        function addItem() {
            var tbody = document.getElementById('itemsBody');
            var row = tbody.insertRow();
            row.innerHTML = '<td><select name="kategori_id[]" class="kategori-select" onchange="updateHarga(this)"><option value="">-- Pilih --</option><?php foreach ($hargaList as $h): ?><option value="<?= $h['kategori_id'] ?>" data-harga="<?= $h['harga_beli'] ?>"><?= htmlspecialchars($h['kategori_nama']) ?></option><?php endforeach; ?></select></td><td><input type="number" name="berat[]" step="0.1" min="0" class="berat-input" oninput="calcSubtotal(this)"></td><td class="harga-cell">Rp 0</td><td class="subtotal-cell">Rp 0</td>';
        }

        function updateHarga(select) {
            var harga = select.options[select.selectedIndex].dataset.harga || 0;
            var row = select.closest('tr');
            row.cells[2].textContent = 'Rp ' + parseInt(harga).toLocaleString('id-ID');
            calcSubtotal(select);
        }

        function calcSubtotal(input) {
            var row = input.closest('tr');
            var select = row.cells[0].querySelector('select');
            var harga = parseFloat(select.options[select.selectedIndex].dataset.harga) || 0;
            var berat = parseFloat(input.value) || 0;
            var subtotal = harga * berat;
            row.cells[3].textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            hitungTotal();
        }

        function hitungTotal() {
            var total = 0;
            var cells = document.querySelectorAll('.subtotal-cell');
            cells.forEach(function(el) {
                total += parseInt(el.textContent.replace(/[^\d]/g, '')) || 0;
            });
            document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
    </script>
</body>
</html>
