<?php
/**
 * Dashboard Nasabah
 *
 * Menampilkan saldo tabungan dan ringkasan aktivitas terbaru
 * Compatible dengan PHP 7.3+
 */

// Format uang ke Rupiah Indonesia
function formatRupiah($number): string
{
    return 'Rp ' . number_format((float) $number, 0, ',', '.');
}

// Format tanggal Indonesia
function formatTanggalIndonesia($datetime): string
{
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
        4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $date = is_string($datetime) ? strtotime($datetime) : $datetime;
    $day = date('j', $date);
    $month = $bulan[(int) date('n', $date)];
    $year = date('Y', $date);
    $time = date('H:i', $date);

    return $day . ' ' . $month . ' ' . $year . ' ' . $time;
}

// Get badge class for status
function getStatusBadgeClass($tipe): string
{
    switch ($tipe) {
        case 'SETOR':
            return 'badge-success';
        case 'PENCAIRAN':
            return 'badge-warning';
        case 'KOREKSI':
            return 'badge-info';
        default:
            return 'badge-secondary';
    }
}

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
        .flash-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .flash-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

        /* Page Header */
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { margin: 0 0 0.5rem 0; font-size: 1.75rem; color: #1b5e20; }
        .page-header p { margin: 0; color: #666; }

        /* Cards */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header h3 { margin: 0; font-size: 1.1rem; color: #333; }
        .card-body { padding: 1.5rem; }

        /* Saldo Cards */
        .saldo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .saldo-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .saldo-card.saldo-utama {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
        }
        .saldo-card.saldo-tersedia {
            background: linear-gradient(135deg, #1976d2 0%, #0d47a1 100%);
            color: white;
        }
        .saldo-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .saldo-amount {
            font-size: 1.75rem;
            font-weight: 700;
        }
        .saldo-card.saldo-terikat {
            background: white;
            border: 1px solid #e0e0e0;
        }
        .saldo-card.saldo-terikat .saldo-amount {
            color: #f57c00;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
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
            font-size: 0.9rem;
        }
        .btn-primary { background: #2e7d32; color: white; }
        .btn-primary:hover { background: #1b5e20; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn-secondary:hover { background: #bdbdbd; }
        .btn-warning { background: #f57c00; color: white; }
        .btn-warning:hover { background: #e65100; }

        /* Table */
        .table-responsive { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tr:hover { background: #f8f9fa; }
        td { color: #333; }

        /* Badges */
        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-warning { background: #fff3e0; color: #f57c00; }
        .badge-info { background: #e3f2fd; color: #1976d2; }
        .badge-secondary { background: #eceff1; color: #546e7a; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #888;
        }
        .empty-state svg { width: 64px; height: 64px; margin-bottom: 1rem; opacity: 0.5; }

        /* Pengajuan Aktif */
        .pengajuan-item {
            background: #fff;
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
        .pengajuan-status .badge { font-size: 0.8rem; }

        /* Quick Links */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
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
        .quick-link:hover {
            border-color: #2e7d32;
            background: #f1f8e9;
        }
        .quick-link-icon {
            width: 40px;
            height: 40px;
            background: #e8f5e9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .quick-link-text h4 { margin: 0; font-size: 0.95rem; }
        .quick-link-text p { margin: 0; font-size: 0.8rem; color: #888; }
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
                <a href="<?= base_url('nasabah/dashboard') ?>" class="active">📊 Dashboard</a>
                <a href="<?= base_url('nasabah/setor') ?>">🗑️ Setor Sampah</a>
                <a href="<?= base_url('nasabah/riwayat') ?>">📜 Riwayat Transaksi</a>
                <a href="<?= base_url('nasabah/cairkan') ?>">💰 Ajukan Pencairan</a>
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
                <h1>Selamat Datang, <?= htmlspecialchars($user['nama'] ?? 'Nasabah') ?>!</h1>
                <p>Bank Sampah <?= htmlspecialchars($nasabah['bank_sampah_nama'] ?? 'Desa') ?></p>
            </div>

            <!-- Saldo Cards -->
            <div class="saldo-grid">
                <div class="saldo-card saldo-utama">
                    <div class="saldo-label">💰 Total Saldo Tabungan</div>
                    <div class="saldo-amount"><?= formatRupiah($saldo) ?></div>
                </div>
                <div class="saldo-card saldo-tersedia">
                    <div class="saldo-label">✨ Saldo Bisa Dicairkan</div>
                    <div class="saldo-amount"><?= formatRupiah($saldoTersedia) ?></div>
                </div>
                <div class="saldo-card saldo-terikat">
                    <div class="saldo-label">⏳ Sedang Dalam Pengajuan</div>
                    <div class="saldo-amount"><?= formatRupiah($totalTerikat) ?></div>
                </div>
            </div>

            <!-- Content Grid -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                <!-- Riwayat Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <h3>📜 Riwayat Transaksi Terakhir</h3>
                        <a href="<?= base_url('nasabah/riwayat') ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Lihat Semua</a>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <?php if (!empty($riwayatTerbaru)): ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Tipe</th>
                                            <th>Jumlah</th>
                                            <th>Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($riwayatTerbaru as $row): ?>
                                            <tr>
                                                <td><?= formatTanggalIndonesia($row['created_at']) ?></td>
                                                <td><span class="badge badge-<?= strtolower($row['tipe']) === 'setor' ? 'success' : ($row['tipe'] === 'PENCAIRAN' ? 'warning' : 'info') ?>"><?= htmlspecialchars($row['tipe']) ?></span></td>
                                                <td style="<?= $row['jumlah'] >= 0 ? 'color: #2e7d32;' : 'color: #f57c00;' ?>">
                                                    <?= $row['jumlah'] >= 0 ? '+' : '' ?><?= formatRupiah($row['jumlah']) ?>
                                                </td>
                                                <td><?= formatRupiah($row['saldo_setelah']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>Belum ada riwayat transaksi.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar Panel -->
                <div>
                    <!-- Pengajuan Aktif -->
                    <div class="card" style="margin-bottom: 1.5rem;">
                        <div class="card-header">
                            <h3>📋 Pengajuan Aktif</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($pengajuanAktif)): ?>
                                <?php foreach ($pengajuanAktif as $p): ?>
                                    <div class="pengajuan-item">
                                        <div class="pengajuan-info">
                                            <h4><?= htmlspecialchars($p['no_pengajuan']) ?></h4>
                                            <p><?= formatRupiah($p['jumlah']) ?> • <?= htmlspecialchars($p['metode']) ?></p>
                                        </div>
                                        <div class="pengajuan-status">
                                            <span class="badge badge-<?= $p['status'] === 'DISETUJUI' ? 'success' : 'warning' ?>">
                                                <?= htmlspecialchars($p['status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #888; text-align: center; padding: 1rem;">Tidak ada pengajuan aktif</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h3>⚡ Aksi Cepat</h3>
                        </div>
                        <div class="card-body">
                            <div class="quick-links" style="grid-template-columns: 1fr; margin: 0;">
                                <a href="<?= base_url('nasabah/cairkan') ?>" class="quick-link">
                                    <div class="quick-link-icon">💸</div>
                                    <div class="quick-link-text">
                                        <h4>Ajukan Pencairan</h4>
                                        <p>Cairkan saldo tabungan</p>
                                    </div>
                                </a>
                                <a href="<?= base_url('nasabah/riwayat') ?>" class="quick-link">
                                    <div class="quick-link-icon">📊</div>
                                    <div class="quick-link-text">
                                        <h4>Lihat Riwayat</h4>
                                        <p>Semua transaksi Anda</p>
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
