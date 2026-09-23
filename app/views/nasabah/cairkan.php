<?php
/**
 * Form Pengajuan Pencairan
 *
 * Form untuk mengajukan pencairan saldo tabungan
 * Compatible dengan PHP 7.3+
 */

// Format uang ke Rupiah Indonesia
function formatRupiah($number): string
{
    return 'Rp ' . number_format((float) $number, 0, ',', '.');
}

// Minimal pencairan
$MINIMAL_PENCAIRAN = PengajuanPencairan::MINIMAL_PENCAIRAN;

$user = currentUser();
$errors = isset($errors) ? $errors : [];
$oldInput = isset($oldInput) ? $oldInput : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Pencairan - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
            padding: 0;
            flex-shrink: 0;
        }
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header h2 { margin: 0; font-size: 1.1rem; }
        .sidebar-header small { opacity: 0.8; }
        .sidebar nav { padding: 1rem 0; }
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
        .sidebar nav a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: #81c784;
        }
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }

        /* Flash Messages */
        .flash {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1b5e20; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #2e7d32; text-decoration: none; }

        /* Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #eee;
        }
        .card-header h3 { margin: 0; font-size: 1.1rem; color: #333; }
        .card-body { padding: 1.5rem; }

        /* Form */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .form-group label span { color: #e53935; }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
        }
        .form-control.error { border-color: #e53935; }
        .form-hint {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.25rem;
        }
        .form-error {
            color: #e53935;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        /* Radio Group */
        .radio-group {
            display: flex;
            gap: 1rem;
        }
        .radio-option {
            flex: 1;
        }
        .radio-option input { display: none; }
        .radio-option label {
            display: block;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }
        .radio-option label .icon { font-size: 1.5rem; display: block; margin-bottom: 0.25rem; }
        .radio-option label .text { font-weight: 600; }
        .radio-option input:checked + label {
            border-color: #2e7d32;
            background: #e8f5e9;
            color: #1b5e20;
        }
        .radio-option input:disabled + label {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Buttons */
        .btn {
            padding: 0.875rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-primary { background: #2e7d32; color: white; }
        .btn-primary:hover { background: #1b5e20; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn-secondary:hover { background: #bdbdbd; }
        .btn-block { width: 100%; }

        /* Info Box */
        .info-box {
            background: #fff3e0;
            border: 1px solid #ffe0b2;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
        .info-box.warning {
            background: #ffebee;
            border-color: #ffcdd2;
        }
        .info-box.success {
            background: #e8f5e9;
            border-color: #c8e6c9;
        }
        .info-box p { margin: 0; font-size: 0.9rem; color: #333; }
        .info-box strong { display: block; margin-bottom: 0.25rem; }

        /* Saldo Info */
        .saldo-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .saldo-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .saldo-row:last-child { border-bottom: none; }
        .saldo-row.total { font-weight: 700; font-size: 1.1rem; }
        .saldo-row .label { color: #666; }
        .saldo-row .value { color: #333; }
        .saldo-row .value.positive { color: #2e7d32; }
        .saldo-row .value.warning { color: #f57c00; }

        /* Pengajuan Aktif */
        .pengajuan-list { margin-top: 1rem; }
        .pengajuan-item {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pengajuan-info h4 { margin: 0 0 0.25rem 0; font-size: 0.95rem; }
        .pengajuan-info p { margin: 0; font-size: 0.85rem; color: #666; }
        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-warning { background: #fff3e0; color: #f57c00; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }

        /* Help Text */
        .help-section {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 1.25rem;
        }
        .help-section h4 { margin: 0 0 0.75rem 0; font-size: 1rem; color: #1565c0; }
        .help-section ul { margin: 0; padding-left: 1.25rem; }
        .help-section li { margin-bottom: 0.5rem; font-size: 0.9rem; color: #333; }
        .help-section li:last-child { margin-bottom: 0; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🌿 <?= APP_NAME ?></h2>
                <small>Bank Sampah Digital</small>
            </div>
            <nav>
                <a href="<?= base_url('nasabah/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('nasabah/setor') ?>">🗑️ Setor Sampah</a>
                <a href="<?= base_url('nasabah/riwayat') ?>">📜 Riwayat Transaksi</a>
                <a href="<?= base_url('nasabah/cairkan') ?>" class="active">💰 Ajukan Pencairan</a>
                <a href="<?= base_url('nasabah/profil') ?>">👤 Profil Saya</a>
                <a href="<?= base_url('logout') ?>" style="margin-top: 1rem; color: #ffcdd2;">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <!-- Flash Message -->
            <?php if ($flash): ?>
                <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
                    <?php if ($flash['type'] === 'success'): ?>✓<?php elseif ($flash['type'] === 'error'): ?>✕<?php else: ?>ℹ<?php endif; ?>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>💰 Ajukan Pencairan</h1>
                    <div class="breadcrumb">
                        <a href="<?= base_url('nasabah/dashboard') ?>">Dashboard</a> / Ajukan Pencairan
                    </div>
                </div>
            </div>

            <div class="content-grid">
                <!-- Form Pencairan -->
                <div class="card">
                    <div class="card-header">
                        <h3>Form Pengajuan Pencairan Saldo</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($saldoTersedia <= 0): ?>
                            <div class="info-box warning">
                                <p><strong>Saldo Tidak Cukup</strong>Saldo Anda tidak mencukupi untuk pencairan minimum Rp <?= number_format($MINIMAL_PENCAIRAN, 0, ',', '.') ?>.</p>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($errors)): ?>
                                <div class="info-box warning" style="background: #ffebee; border-color: #ffcdd2;">
                                    <p><strong>Terjadi Kesalahan:</strong></p>
                                    <ul style="margin: 0.5rem 0 0 1.25rem; padding: 0;">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" id="formCairkan" onsubmit="return validateForm();">
                                <!-- Saldo Info -->
                                <div class="saldo-info">
                                    <div class="saldo-row">
                                        <span class="label">Saldo Tabungan</span>
                                        <span class="value"><?= formatRupiah($saldo) ?></span>
                                    </div>
                                    <div class="saldo-row">
                                        <span class="label">Terikat Pengajuan</span>
                                        <span class="value warning">-<?= formatRupiah($totalTerikat) ?></span>
                                    </div>
                                    <div class="saldo-row total">
                                        <span class="label">Bisa Dicairkan</span>
                                        <span class="value positive"><?= formatRupiah($saldoTersedia) ?></span>
                                    </div>
                                </div>

                                <!-- Jumlah Pencairan -->
                                <div class="form-group">
                                    <label for="jumlah">Jumlah Pencairan <span>*</span></label>
                                    <input
                                        type="text"
                                        id="jumlah"
                                        name="jumlah"
                                        class="form-control"
                                        placeholder="Masukkan jumlah pencairan"
                                        value="<?= htmlspecialchars($oldInput['jumlah'] ?? '') ?>"
                                        onkeyup="formatNumberInput(this)"
                                        onblur="validateJumlah()"
                                    >
                                    <div class="form-hint">Minimal: <?= formatRupiah($MINIMAL_PENCAIRAN) ?></div>
                                    <div class="form-error" id="jumlahError"></div>
                                </div>

                                <!-- Metode Pencairan -->
                                <div class="form-group">
                                    <label>Metode Pencairan <span>*</span></label>
                                    <div class="radio-group">
                                        <div class="radio-option">
                                            <input
                                                type="radio"
                                                name="metode"
                                                id="metodeTunai"
                                                value="TUNAI"
                                                <?= (isset($oldInput['metode']) && $oldInput['metode'] === 'TUNAI') ? 'checked' : '' ?>
                                                onclick="toggleTransfer()"
                                            >
                                            <label for="metodeTunai">
                                                <span class="icon">💵</span>
                                                <span class="text">Tunai</span>
                                            </label>
                                        </div>
                                        <div class="radio-option">
                                            <input
                                                type="radio"
                                                name="metode"
                                                id="metodeTransfer"
                                                value="TRANSFER"
                                                <?= (isset($oldInput['metode']) && $oldInput['metode'] === 'TRANSFER') ? 'checked' : '' ?>
                                                onclick="toggleTransfer()"
                                            >
                                            <label for="metodeTransfer">
                                                <span class="icon">🏦</span>
                                                <span class="text">Transfer</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tujuan Transfer -->
                                <div class="form-group" id="transferGroup" style="display: none;">
                                    <label for="tujuan_transfer">Tujuan Transfer <span>*</span></label>
                                    <input
                                        type="text"
                                        id="tujuan_transfer"
                                        name="tujuan_transfer"
                                        class="form-control"
                                        placeholder="Nomor rekening atau e-wallet"
                                        value="<?= htmlspecialchars($oldInput['tujuan_transfer'] ?? '') ?>"
                                    >
                                    <div class="form-hint">Contoh: 1234567890 (BCA) atau 081234567890 (GoPay/OVO)</div>
                                    <div class="form-error" id="transferError"></div>
                                </div>

                                <!-- Submit -->
                                <button type="submit" name="submit" class="btn btn-primary btn-block">
                                    💸 Ajukan Pencairan
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div>
                    <!-- Pengajuan Aktif -->
                    <div class="card" style="margin-bottom: 1.5rem;">
                        <div class="card-header">
                            <h3>📋 Pengajuan Aktif</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($pengajuanAktif)): ?>
                                <div class="pengajuan-list">
                                    <?php foreach ($pengajuanAktif as $p): ?>
                                        <div class="pengajuan-item">
                                            <div class="pengajuan-info">
                                                <h4><?= htmlspecialchars($p['no_pengajuan']) ?></h4>
                                                <p><?= formatRupiah($p['jumlah']) ?> • <?= htmlspecialchars($p['metode']) ?></p>
                                            </div>
                                            <span class="badge badge-<?= $p['status'] === 'DISETUJUI' ? 'success' : 'warning' ?>">
                                                <?= htmlspecialchars($p['status']) ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p style="color: #888; text-align: center; padding: 1rem; margin: 0;">Tidak ada pengajuan aktif</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Help -->
                    <div class="card">
                        <div class="card-header">
                            <h3>❓ Informasi</h3>
                        </div>
                        <div class="card-body">
                            <div class="help-section">
                                <h4>Ketentuan Pencairan</h4>
                                <ul>
                                    <li>Minimal pencairan: <strong><?= formatRupiah($MINIMAL_PENCAIRAN) ?></strong></li>
                                    <li>Saldo yang dapat dicairkan sudah dikurangi pengajuan aktif</li>
                                    <li>Pengajuan akan diproses dalam 1-2 hari kerja</li>
                                    <li>Pencairan via transfer dikenakan biaya admin sesuai bank tujuan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Constants from PHP
        var MINIMAL_PENCAIRAN = <?= $MINIMAL_PENCAIRAN ?>;
        var SALDO_TERSEDIA = <?= $saldoTersedia ?>;

        // Format number input as currency
        function formatNumberInput(input) {
            var value = input.value.replace(/[^\d]/g, '');
            if (value) {
                value = parseInt(value, 10).toLocaleString('id-ID');
            }
            input.value = value;
        }

        // Toggle transfer fields
        function toggleTransfer() {
            var metodeTransfer = document.getElementById('metodeTransfer').checked;
            var transferGroup = document.getElementById('transferGroup');

            if (metodeTransfer) {
                transferGroup.style.display = 'block';
            } else {
                transferGroup.style.display = 'none';
                document.getElementById('tujuan_transfer').value = '';
                document.getElementById('transferError').textContent = '';
            }
        }

        // Validate jumlah
        function validateJumlah() {
            var input = document.getElementById('jumlah');
            var errorEl = document.getElementById('jumlahError');
            var value = parseInt(input.value.replace(/[^\d]/g, ''), 10);

            if (isNaN(value) || value <= 0) {
                errorEl.textContent = 'Jumlah harus diisi dengan angka.';
                input.classList.add('error');
                return false;
            }

            if (value < MINIMAL_PENCAIRAN) {
                errorEl.textContent = 'Minimal pencairan adalah Rp ' + MINIMAL_PENCAIRAN.toLocaleString('id-ID') + '.';
                input.classList.add('error');
                return false;
            }

            if (value > SALDO_TERSEDIA) {
                errorEl.textContent = 'Jumlah tidak boleh melebihi saldo yang tersedia (Rp ' + SALDO_TERSEDIA.toLocaleString('id-ID') + ').';
                input.classList.add('error');
                return false;
            }

            errorEl.textContent = '';
            input.classList.remove('error');
            return true;
        }

        // Validate transfer destination
        function validateTransfer() {
            var metodeTransfer = document.getElementById('metodeTransfer').checked;

            if (metodeTransfer) {
                var input = document.getElementById('tujuan_transfer');
                var errorEl = document.getElementById('transferError');
                var value = input.value.trim();

                if (value.length < 8) {
                    errorEl.textContent = 'Nomor tujuan transfer minimal 8 digit.';
                    input.classList.add('error');
                    return false;
                }

                errorEl.textContent = '';
                input.classList.remove('error');
            }

            return true;
        }

        // Validate form
        function validateForm() {
            var isValid = true;

            // Validate jumlah
            if (!validateJumlah()) {
                isValid = false;
            }

            // Validate metode
            var metodeTunai = document.getElementById('metodeTunai').checked;
            var metodeTransfer = document.getElementById('metodeTransfer').checked;

            if (!metodeTunai && !metodeTransfer) {
                alert('Silakan pilih metode pencairan.');
                isValid = false;
            }

            // Validate transfer
            if (!validateTransfer()) {
                isValid = false;
            }

            if (isValid) {
                // Confirm submission
                var jumlah = document.getElementById('jumlah').value;
                return confirm('Anda yakin ingin mengajukan pencairan sebesar Rp ' + jumlah + '?');
            }

            return false;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            toggleTransfer();
        });
    </script>
</body>
</html>
