<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_pemilik();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('views/produk.php');
}
csrf_check();

$id = (int) ($_POST['id_produk'] ?? 0);
$st = db()->prepare("UPDATE produk SET status = IF(status = 'aktif', 'nonaktif', 'aktif') WHERE id_produk = ?");
$st->execute([$id]);
set_flash('success', 'Status produk diperbarui.');
redirect('views/produk.php');
