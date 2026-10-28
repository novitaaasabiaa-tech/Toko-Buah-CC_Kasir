<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_pemilik();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('views/stok.php');
}
csrf_check();

$idProduk = (int) ($_POST['id_produk'] ?? 0);
$jenis = ($_POST['jenis'] ?? 'masuk') === 'penyesuaian' ? 'penyesuaian' : 'masuk';
$jumlah = round(angka_input($_POST['jumlah'] ?? 0), 2);
$ket = mb_substr(trim($_POST['keterangan'] ?? ''), 0, 150);

if ($idProduk <= 0 || $jumlah == 0 || ($jenis === 'masuk' && $jumlah < 0)) {
    set_flash('error', 'Jumlah stok tidak valid.');
    redirect('views/stok.php');
}

$pdo = db();
try {
    $pdo->beginTransaction();
    $cek = $pdo->prepare('SELECT stok FROM produk WHERE id_produk = ? FOR UPDATE');
    $cek->execute([$idProduk]);
    $stokLama = $cek->fetchColumn();
    if ($stokLama === false) {
        throw new RuntimeException('Produk tidak ditemukan.');
    }
    if ((float) $stokLama + $jumlah < 0) {
        throw new RuntimeException('Stok tidak boleh menjadi minus.');
    }
    $ubah = $pdo->prepare('UPDATE produk SET stok = stok + ? WHERE id_produk = ?');
    $ubah->execute([$jumlah, $idProduk]);
    $log = $pdo->prepare('INSERT INTO stok_log (id_produk, id_user, jenis, jumlah, keterangan) VALUES (?, ?, ?, ?, ?)');
    $log->execute([$idProduk, $_SESSION['user']['id_user'], $jenis, $jumlah, $ket]);
    $pdo->commit();
    set_flash('success', 'Perubahan stok berhasil dicatat.');
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', ($ex instanceof RuntimeException) ? $ex->getMessage() : pesan_db('Gagal mencatat stok.', $ex));
}
redirect('views/stok.php');
