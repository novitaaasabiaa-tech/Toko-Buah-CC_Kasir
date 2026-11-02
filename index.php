<?php
require_once __DIR__ . '/config/config.php';

// Pintu masuk: arahkan ke dashboard bila sudah login, jika belum ke halaman login.
$tujuan = empty($_SESSION['user']) ? 'views/login.php' : 'views/dashboard.php';
header('Location: ' . BASE_URL . $tujuan);
exit;
