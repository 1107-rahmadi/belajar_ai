<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
    <?php include APP_PATH . '/views/partials/header.php'; ?>

    <main class="app-main">
        <div class="container">
            <section class="hero">
                <h1>Selamat Datang di <?= APP_NAME ?></h1>
                <p class="lead">
                    Solusi digital untuk mengelola sampah dan membantu masyarakat desa
                    dalam mengelola sampah bernilai ekonomi.
                </p>
            </section>

            <section class="features">
                <h2>Fitur Utama</h2>
                <div class="feature-grid">
                    <div class="feature-card">
                        <h3>🗑️ Setor Sampah</h3>
                        <p>Nasabah dapat menyetorkan sampah kering untuk ditimbang dan dikonversi menjadi saldo.</p>
                    </div>
                    <div class="feature-card">
                        <h3>💰 Tarik Saldo</h3>
                        <p>Saldo dari hasil setoran sampah dapat ditarik sewaktu-waktu.</p>
                    </div>
                    <div class="feature-card">
                        <h3>📊 Laporan</h3>
                        <p>Akses laporan keuangan dan operasional secara real-time.</p>
                    </div>
                    <div class="feature-card">
                        <h3>📱 Mudah Digunakan</h3>
                        <p>Antarmuka yang sederhana dan mudah dipahami oleh semua kalangan.</p>
                    </div>
                </div>
            </section>

            <section class="cta">
                <h2>Mulai Sekarang</h2>
                <p>Daftar sebagai Nasabah dan mulai berkontribusi untuk lingkungan yang lebih bersih.</p>
                <div class="cta-buttons">
                    <a href="#" class="btn btn-primary">Daftar Sekarang</a>
                    <a href="#" class="btn btn-outline">Pelajari Lebih Lanjut</a>
                </div>
            </section>
        </div>
    </main>

    <?php include APP_PATH . '/views/partials/footer.php'; ?>

    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
