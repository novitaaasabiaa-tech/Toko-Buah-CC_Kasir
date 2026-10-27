<?php
require_once __DIR__ . '/../config/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/functions.php';
require_login();

$db = db();
$produkAktif = (int) $db->query("SELECT COUNT(*) FROM produk WHERE status = 'aktif'")->fetchColumn();
$stokMenipis = (int) $db->query("SELECT COUNT(*) FROM produk WHERE status = 'aktif' AND stok <= stok_minimum")->fetchColumn();
$trxHariIni = (int) $db->query("SELECT COUNT(*) FROM transaksi WHERE DATE(tanggal) = CURDATE()")->fetchColumn();
$omzetHariIni = (float) $db->query("SELECT COALESCE(SUM(total), 0) FROM transaksi WHERE DATE(tanggal) = CURDATE()")->fetchColumn();

$pageTitle = 'Dashboard';
$active = 'dashboard';
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">

    <div class="row c4">
        <div class="card stat"><div class="ico" style="background:#E6E6E6">💰</div><div><small>Penjualan Hari Ini</small><b><?= rupiah($omzetHariIni) ?></b></div></div>
        <div class="card stat"><div class="ico" style="background:#E6E6E6">🧾</div><div><small>Transaksi Hari Ini</small><b><?= $trxHariIni ?></b></div></div>
        <div class="card stat"><div class="ico" style="background:#E6E6E6">🍎</div><div><small>Produk Aktif</small><b><?= $produkAktif ?></b></div></div>
        <div class="card stat"><div class="ico" style="background:#F4D9D6">⚠️</div><div><small>Stok Menipis</small><b><?= $stokMenipis ?> item</b></div></div>
    </div>
    <div class="card"><h3>Selamat datang, <?= e($_SESSION['user']['nama']) ?></h3>
        <p class="muted">Fondasi proyek sudah berjalan: koneksi database, login, dan struktur halaman. Fitur kasir, stok, dan laporan dikembangkan pada tahap berikutnya.</p></div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
