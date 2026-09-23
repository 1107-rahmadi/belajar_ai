<?php
require 'config/database.php';
$db = getDbConnection();
$stmt = $db->query("SELECT u.id, u.nama, u.no_hp, u.role, n.id as nasabah_id, n.saldo FROM users u JOIN nasabah n ON u.id = n.user_id WHERE u.role = 'NASABAH'");
print_r($stmt->fetchAll());
