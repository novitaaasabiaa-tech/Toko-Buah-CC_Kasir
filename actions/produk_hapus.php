
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

try {
    $db->beginTransaction();

    // Pastikan produk tersedia
    $cekProduk = $db->prepare("
        SELECT id_produk
        FROM produk
        WHERE id_produk = ?
        FOR UPDATE
    ");
    $cekProduk->execute([$id]);

    if (!$cekProduk->fetch()) {
        $db->rollBack();
        set_flash('error', 'Produk tidak ditemukan.');
        redirect('views/produk.php');
    }

    // Periksa apakah produk pernah digunakan dalam transaksi
    $cekTransaksi = $db->prepare("
        SELECT COUNT(*)
        FROM transaksi_detail
        WHERE id_produk = ?
    ");
    $cekTransaksi->execute([$id]);

    $jumlahTransaksi = (int) $cekTransaksi->fetchColumn();

    if ($jumlahTransaksi > 0) {
        $db->rollBack();

        set_flash(
            'error',
            'Produk tidak dapat dihapus permanen karena sudah memiliki riwayat transaksi. Silakan nonaktifkan produk.'
        );

        redirect('views/produk.php');
    }

    // Hapus produk jika belum pernah digunakan dalam transaksi
    $hapus = $db->prepare("
        DELETE FROM produk
        WHERE id_produk = ?
    ");

    $hapus->execute([$id]);

    $db->commit();

    set_flash('success', 'Produk berhasil dihapus permanen.');

} catch (Throwable $e) {

    if ($db->inTransaction()) {
        $db->rollBack();
    }

    set_flash(
        'error',
        'Produk gagal dihapus. Periksa kembali hubungan data produk.'
    );
}

redirect('views/produk.php');
