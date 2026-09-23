<?php
require_once __DIR__ . '/../config/app.php';
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>Test URLs</title></head>
<body>
<h1>Test URLs</h1>
<p>base_url('pengelola/transaksi-beli'): <a href="<?= base_url('pengelola/transaksi-beli') ?>"><?= base_url('pengelola/transaksi-beli') ?></a></p>
<p>base_url('pengelola/transaksi-beli/add'): <a href="<?= base_url('pengelola/transaksi-beli/add') ?>"><?= base_url('pengelola/transaksi-beli/add') ?></a></p>
<h2>Click link to test routing</h2>
</body>
</html>
