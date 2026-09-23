<?php
/**
 * Detail Pengajuan Pencairan
 *
 * Halaman detail untuk melihat dan memproses pengajuan pencairan
 */

// Format uang
function formatRupiah($number): string
{
    return 'Rp ' . number_format((float) $number, 0, ',', '.');
}

// Format tanggal
function formatTanggal($date): string
{
    return date('d M Y H:i', strtotime($date));
}

$user = currentUser();
$flash = getFlashMessage();

$statusBadge = $pengajuan['status'];
if ($statusBadge === 'DIAJUKAN') {
    $statusClass = 'badge-menunggu';
} elseif ($statusBadge === 'DISETUJUI') {
    $statusClass = 'badge-disetujui';
} elseif ($statusBadge === 'DITOLAK') {
    $statusClass = 'badge-ditolak';
} elseif ($statusBadge === 'DICAIRKAN') {
    $statusClass = 'badge-dicairkan';
} else {
    $statusClass = 'badge-dibatalkan';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan <?= htmlspecialchars($pengajuan['no_pengajuan']) ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
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
        .flash-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: #1b5e20; }
        .breadcrumb { color: #888; font-size: 0.9rem; }
        .breadcrumb a { color: #2e7d32; text-decoration: none; }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #eee;
        }
        .card-header h3 { margin: 0; font-size: 1.1rem; }
        .card-body { padding: 1.5rem; }

        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        .badge-menunggu { background: #fff3e0; color: #f57c00; }
        .badge-disetujui { background: #e8f5e9; color: #2e7d32; }
        .badge-ditolak { background: #ffebee; color: #e53935; }
        .badge-dicairkan { background: #e3f2fd; color: #1565c0; }
        .badge-dibatalkan { background: #f5f5f5; color: #757575; }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
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
        .btn-danger { background: #e53935; color: white; }
        .btn-danger:hover { background: #c62828; }
        .btn-success { background: #2e7d32; color: white; }
        .btn-success:hover { background: #1b5e20; }
        .btn-block { width: 100%; justify-content: center; }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        @media (max-width: 768px) {
            .info-grid { grid-template-columns: 1fr; }
        }

        .info-item {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .info-item label {
            display: block;
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }
        .info-item .value {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
        }
        .info-item .value.jumlah {
            font-size: 1.25rem;
            color: #2e7d32;
        }

        .timeline {
            border-left: 2px solid #e0e0e0;
            padding-left: 1.5rem;
            margin-left: 0.5rem;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        .timeline-item:last-child { padding-bottom: 0; }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.7rem;
            top: 0.25rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e0e0e0;
            border: 2px solid white;
        }
        .timeline-item.active::before {
            background: #2e7d32;
        }
        .timeline-item .title {
            font-weight: 600;
            color: #333;
        }
        .timeline-item .date {
            font-size: 0.85rem;
            color: #888;
        }
        .timeline-item .desc {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .actions-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
        }
        .actions-box h4 {
            margin: 0 0 1rem 0;
            color: #333;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .form-group textarea:focus {
            outline: none;
            border-color: #2e7d32;
        }

        .text-danger { color: #e53935; }
        .text-success { color: #2e7d32; }
        .mt-2 { margin-top: 1rem; }
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
                <a href="<?= base_url('pengelola/dashboard') ?>">📊 Dashboard</a>
                <a href="<?= base_url('pengelola/transaksi-beli') ?>">🛒 Transaksi Beli</a>
                <a href="<?= base_url('pengelola/transaksi-jual') ?>">💵 Transaksi Jual</a>
                <a href="<?= base_url('pengelola/nasabah') ?>">👥 Nasabah</a>
                <a href="<?= base_url('pengelola/pencairan') ?>" class="active">💰 Pencairan</a>
                <a href="<?= base_url('pengelola/kategori') ?>">🏷️ Kategori</a>
                <a href="<?= base_url('pengelola/harga') ?>">💲 Harga</a>
                <a href="<?= base_url('pengelola/pengepul') ?>">🚛 Pengepul</a>
                <a href="<?= base_url('pengelola/laporan') ?>">📈 Laporan</a>
                <a href="<?= base_url('logout') ?>" style="margin-top: 1rem; color: #ffcdd2;">🚪 Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php if ($flash): ?>
                <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
                    <?php if ($flash['type'] === 'success'): ?>✓<?php elseif ($flash['type'] === 'error'): ?>✕<?php else: ?>ℹ<?php endif; ?>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <div>
                    <h1>📋 Detail Pengajuan</h1>
                    <div class="breadcrumb">
                        <a href="<?= base_url('pengelola/dashboard') ?>">Dashboard</a> /
                        <a href="<?= base_url('pengelola/pencairan') ?>">Pengajuan Pencairan</a> /
                        <?= htmlspecialchars($pengajuan['no_pengajuan']) ?>
                    </div>
                </div>
                <a href="<?= base_url('pengelola/pencairan') ?>" class="btn btn-secondary">← Kembali</a>
            </div>

            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3><?= htmlspecialchars($pengajuan['no_pengajuan']) ?></h3>
                    <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($pengajuan['status']) ?></span>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <!-- Info农户 -->
                        <div class="info-item">
                            <label>Nasabah</label>
                            <div class="value"><?= htmlspecialchars($pengajuan['nama_nasabah']) ?></div>
                        </div>
                        <div class="info-item">
                            <label>No. HP</label>
                            <div class="value"><?= htmlspecialchars($pengajuan['hp_nasabah'] ?? '-') ?></div>
                        </div>
                        <div class="info-item">
                            <label>Bank Sampah</label>
                            <div class="value"><?= htmlspecialchars($pengajuan['nama_bank_sampah'] ?? '-') ?></div>
                        </div>
                        <div class="info-item">
                            <label>No. Rekening农户</label>
                            <div class="value"><?= htmlspecialchars($pengajuan['no_rekening'] ?? '-') ?></div>
                        </div>
                        <div class="info-item" style="grid-column: 1 / -1;">
                            <label>Jumlah Pencairan</label>
                            <div class="value jumlah"><?= formatRupiah($pengajuan['jumlah']) ?></div>
                        </div>
                        <div class="info-item">
                            <label>Metode Pencairan</label>
                            <div class="value">
                                <?php if ($pengajuan['metode'] === 'TUNAI'): ?>
                                    💵 Tunai
                                <?php else: ?>
                                    🏦 Transfer
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($pengajuan['metode'] === 'TRANSFER' && !empty($pengajuan['tujuan_transfer'])): ?>
                            <div class="info-item">
                                <label>Tujuan Transfer</label>
                                <div class="value"><?= htmlspecialchars($pengajuan['tujuan_transfer']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card">
                <div class="card-header">
                    <h3>📅 Riwayat Proses</h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item active">
                            <div class="title">Pengajuan Dibuat</div>
                            <div class="date"><?= formatTanggal($pengajuan['diajukan_at']) ?></div>
                            <div class="desc">Nasabah mengajukan pencairan</div>
                        </div>

                        <?php if ($pengajuan['diproses_at']): ?>
                            <div class="timeline-item <?= ($pengajuan['status'] !== 'DIAJUKAN') ? 'active' : '' ?>">
                                <div class="title">
                                    <?php if ($pengajuan['status'] === 'DITOLAK'): ?>
                                        Pengajuan Ditolak
                                    <?php elseif ($pengajuan['status'] === 'DISETUJUI' || $pengajuan['status'] === 'DICAIRKAN'): ?>
                                        Pengajuan Disetujui
                                    <?php else: ?>
                                        Sedang Diproses
                                    <?php endif; ?>
                                </div>
                                <div class="date"><?= formatTanggal($pengajuan['diproses_at']) ?></div>
                                <?php if ($pengajuan['diproses_oleh_nama']): ?>
                                    <div class="desc">Oleh: <?= htmlspecialchars($pengajuan['diproses_oleh_nama']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($pengajuan['alasan_tolak'])): ?>
                                    <div class="desc text-danger">Alasan: <?= htmlspecialchars($pengajuan['alasan_tolak']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($pengajuan['dicairkan_at']): ?>
                            <div class="timeline-item active">
                                <div class="title">Saldo Dicairkan</div>
                                <div class="date"><?= formatTanggal($pengajuan['dicairkan_at']) ?></div>
                                <div class="desc text-success">Saldo农户 telah dikurangi</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Aksi -->
            <?php if ($pengajuan['status'] === 'DIAJUKAN'): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>⚡ Aksi</h3>
                    </div>
                    <div class="card-body">
                        <div class="actions-box">
                            <h4>Pilih aksi untuk pengajuan ini:</h4>

                            <div class="action-buttons">
                                <form method="POST" action="<?= base_url('pengelola/pencairan/setuju') ?>" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $pengajuan['id'] ?>">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Setujui pengajuan ini?')">
                                        ✓ Setujui
                                    </button>
                                </form>

                                <button type="button" class="btn btn-danger" onclick="showTolakForm()">
                                    ✕ Tolak
                                </button>
                            </div>

                            <div id="tolakForm" style="display: none; margin-top: 1.5rem;">
                                <form method="POST" action="<?= base_url('pengelola/pencairan/tolak') ?>">
                                    <input type="hidden" name="id" value="<?= $pengajuan['id'] ?>">
                                    <div class="form-group">
                                        <label for="alasan">Alasan Penolakan <span class="text-danger">*</span></label>
                                        <textarea name="alasan" id="alasan" rows="3" required placeholder="Jelaskan alasan penolakan..."></textarea>
                                    </div>
                                    <div style="display: flex; gap: 1rem;">
                                        <button type="submit" class="btn btn-danger">Konfirmasi Tolak</button>
                                        <button type="button" class="btn btn-secondary" onclick="hideTolakForm()">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($pengajuan['status'] === 'DISETUJUI'): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>⚡ Aksi</h3>
                    </div>
                    <div class="card-body">
                        <div class="actions-box">
                            <h4>Pengajuan telah disetujui.</h4>
                            <p class="mt-2">Proses pencairan dan pastikan saldo农户 dikurangi.</p>

                            <form method="POST" action="<?= base_url('pengelola/pencairan/cairkan') ?>" style="margin-top: 1rem;">
                                <input type="hidden" name="id" value="<?= $pengajuan['id'] ?>">
                                <button type="submit" class="btn btn-primary btn-block" onclick="return confirm('Yakin ingin mencairkan saldo农户? Saldo akan dikurangi secara permanen.')">
                                    💰 Tandai Sudah Dicairkan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php elseif ($pengajuan['status'] === 'DITOLAK'): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>ℹ️ Informasi</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-danger"><strong>Pengajuan ini telah ditolak.</strong></p>
                        <?php if (!empty($pengajuan['alasan_tolak'])): ?>
                            <p><strong>Alasan:</strong> <?= htmlspecialchars($pengajuan['alasan_tolak']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($pengajuan['status'] === 'DICAIRKAN'): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>✅ Selesai</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-success"><strong>Pengajuan ini telah dicairkan.</strong></p>
                        <p>Saldo农户 telah dikurangi sebesar <?= formatRupiah($pengajuan['jumlah']) ?>.</p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function showTolakForm() {
            document.getElementById('tolakForm').style.display = 'block';
        }

        function hideTolakForm() {
            document.getElementById('tolakForm').style.display = 'none';
            document.getElementById('alasan').value = '';
        }
    </script>
</body>
</html>
