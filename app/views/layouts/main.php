<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?><?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
    <header class="app-header">
        <div class="container">
            <h1 class="app-logo"><?= APP_NAME ?></h1>
            <nav class="app-nav">
                <a href="<?= base_url() ?>">Beranda</a>
                <a href="<?= base_url('about') ?>">Tentang</a>
                <!-- Menu lain bisa ditambahkan di sini -->
            </nav>
        </div>
    </header>

    <main class="app-main">
        <div class="container">
            <?php if (isset($content)): ?>
                <?= $content ?>
            <?php else: ?>
                <h2>Selamat Datang</h2>
                <p>Sistem Bank Sampah Digital Desa sedang dalam pengembangan.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer class="app-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
