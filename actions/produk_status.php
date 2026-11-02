
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_pemilik();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('views/produk.php');
}

csrf_check();

$id = (int) ($_POST['id_produk'] ?? 0);

if ($id <= 0) {
    set_flash('error', 'ID produk tidak valid.');
    redirect('views/produk.php');
}

$db = db();

$st = $db->prepare("
    UPDATE produk
    SET status = IF(status = 'aktif', 'nonaktif', 'aktif')
    WHERE id_produk = ?
");

$st->execute([$id]);

if ($st->rowCount() > 0) {
    set_flash('success', 'Status produk berhasil diperbarui.');
} else {
    set_flash('error', 'Produk tidak ditemukan atau status tidak berubah.');
}

redirect('views/produk.php');
