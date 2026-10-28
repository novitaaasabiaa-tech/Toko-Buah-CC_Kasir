<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_pemilik();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('views/produk.php');
}
csrf_check();

$id = (int) ($_POST['id_produk'] ?? 0);
$nama = trim($_POST['nama_produk'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$satuan = trim($_POST['satuan'] ?? '');
$harga = (int) round(angka_input($_POST['harga'] ?? 0));
$stokMin = angka_input($_POST['stok_minimum'] ?? 0);
$stokAwal = angka_input($_POST['stok'] ?? 0);
$status = ($_POST['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';

if ($nama === '' || $kategori === '' || $satuan === '' || $harga <= 0 || $stokMin < 0 || $stokAwal < 0) {
    set_flash('error', 'Data produk belum lengkap atau tidak valid.');
    redirect($id > 0 ? 'views/produk_form.php?id=' . $id : 'views/produk_form.php');
}

$pdo = db();
try {
    if ($id > 0) {
        $st = $pdo->prepare('UPDATE produk SET nama_produk = ?, kategori = ?, harga = ?, satuan = ?, stok_minimum = ?, status = ? WHERE id_produk = ?');
        $st->execute([$nama, $kategori, $harga, $satuan, $stokMin, $status, $id]);
    } else {
        $st = $pdo->prepare('INSERT INTO produk (nama_produk, kategori, harga, satuan, stok, stok_minimum, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $st->execute([$nama, $kategori, $harga, $satuan, $stokAwal, $stokMin, $status]);
        if ($stokAwal > 0) {
            $baru = (int) $pdo->lastInsertId();
            $log = $pdo->prepare("INSERT INTO stok_log (id_produk, id_user, jenis, jumlah, keterangan) VALUES (?, ?, 'masuk', ?, 'Stok awal')");
            $log->execute([$baru, $_SESSION['user']['id_user'], $stokAwal]);
        }
    }
    set_flash('success', 'Produk berhasil disimpan.');
} catch (PDOException $ex) {
    set_flash('error', pesan_db('Gagal menyimpan produk.', $ex));
}
redirect('views/produk.php');
