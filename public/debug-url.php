<?php
require_once __DIR__ . '/../config/app.php';
?>
<!DOCTYPE html>
<html>
<head><title>Test URLs</title></head>
<body>
<h1>Test URLs</h1>
<p><strong>APP_URL:</strong> <?= APP_URL ?></p>
<p><strong>base_url('pengelola/transaksi-beli/add'): <?= base_url('pengelola/transaksi-beli/add') ?></p>
<p>&nbsp;</p>
<h2>Direct Link (copy-paste ke browser):</h2>
<pre><?= htmlspecialchars(base_url('pengelola/transaksi-beli/add')) ?></pre>
<h2>HTML Link:</h2>
<a href="<?= htmlspecialchars(base_url('pengelola/transaksi-beli/add')) ?>"><?= htmlspecialchars(base_url('pengelola/transaksi-beli/add')) ?></a>
</body>
</html>
