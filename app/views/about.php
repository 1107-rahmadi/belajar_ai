<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
    <?php include APP_PATH . '/views/partials/header.php'; ?>

    <main class="app-main">
        <div class="container">
            <section class="about-section">
                <h1>Tentang <?= APP_NAME ?></h1>
                <p class="lead">
                    Sistem informasi digital untuk mengelola Bank Sampah di tingkat desa,
                    membantu masyarakat dalam mengelola sampah bernilai ekonomi.
                </p>
            </section>

            <section class="features">
                <h2>Visi & Misi</h2>
                <div class="card">
                    <h3>Visi</h3>
                    <p>Menjadi solusi pengelolaan sampah berbasis komunitas yang transparan,
                    efisien, dan memberdayakan masyarakat desa.</p>
                </div>
                <div class="card">
                    <h3>Misi</h3>
                    <ul>
                        <li>Menyediakan sistem pencatatan transaksi yang transparan</li>
                        <li>Memudahkan masyarakat dalam menyetorkan sampah</li>
                        <li>Menyediakan laporan keuangan yang akurat</li>
                        <li>Meningkatkan nilai ekonomi dari sampah</li>
                    </ul>
                </div>
            </section>

            <section>
                <h2>Fitur Utama</h2>
                <ul>
                    <li><strong>Manajemen Nasabah:</strong> Pendaftaran dan pengelolaan data anggota</li>
                    <li><strong>Transaksi Setoran:</strong> Pencatatan setoran sampah dengan timbangan</li>
                    <li><strong>Manajemen Harga:</strong> Pengaturan harga beli per kategori sampah</li>
                    <li><strong>Transaksi Penjualan:</strong> Penjualan ke pengepul</li>
                    <li><strong>Tabungan Nasabah:</strong> Saldo otomatis dari setoran sampah</li>
                    <li><strong>Pengajuan Pencairan:</strong> Proses pencairan saldo</li>
                    <li><strong>Laporan:</strong> Laporan keuangan dan operasional</li>
                </ul>
            </section>

            <div style="margin-top: 2rem;">
                <a href="<?= base_url('login') ?>" class="btn btn-primary">Masuk ke Sistem</a>
                <a href="<?= base_url() ?>" class="btn btn-outline">Kembali</a>
            </div>
        </div>
    </main>

    <?php include APP_PATH . '/views/partials/footer.php'; ?>

    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
