<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('views/kasir.php');
}
csrf_check();

$items = json_decode($_POST['items'] ?? '[]', true);
$bayar = (int) round(angka_input($_POST['bayar'] ?? 0));
$metode = in_array($_POST['metode'] ?? '', ['tunai', 'transfer'], true) ? $_POST['metode'] : 'tunai';

if (!is_array($items) || count($items) === 0) {
    set_flash('error', 'Keranjang masih kosong.');
    redirect('views/kasir.php');
}

$pdo = db();
try {
    $pdo->beginTransaction();

    // 1) Validasi tiap item terhadap data di database (harga & stok tidak dipercaya dari browser).
    $cek = $pdo->prepare("SELECT id_produk, nama_produk, harga, stok FROM produk WHERE id_produk = ? AND status = 'aktif' FOR UPDATE");
    $total = 0;
    $baris = [];
    foreach ($items as $item) {
        $idProduk = (int) ($item['id'] ?? 0);
        $qty = round((float) ($item['qty'] ?? 0), 2);
        if ($idProduk <= 0 || $qty <= 0) {
            throw new RuntimeException('Data keranjang tidak valid.');
        }
        $cek->execute([$idProduk]);
        $produk = $cek->fetch();
        if (!$produk) {
            throw new RuntimeException('Produk tidak ditemukan atau sudah nonaktif.');
        }
        if ($qty > (float) $produk['stok']) {
            throw new RuntimeException('Stok ' . $produk['nama_produk'] . ' tidak cukup (tersedia ' . format_qty($produk['stok']) . ').');
        }
        $subtotal = (int) round($qty * (int) $produk['harga']);
        $total += $subtotal;
        $baris[] = ['id' => $idProduk, 'harga' => (int) $produk['harga'], 'qty' => $qty, 'subtotal' => $subtotal];
    }
    if ($bayar < $total) {
        throw new RuntimeException('Nominal bayar kurang dari total belanja.');
    }

    // 2) Simpan transaksi.
    $urut = (int) $pdo->query('SELECT COUNT(*) FROM transaksi WHERE DATE(tanggal) = CURDATE()')->fetchColumn() + 1;
    $kode = sprintf('TRX-%s-%03d', date('Ymd'), $urut);
    $simpan = $pdo->prepare('INSERT INTO transaksi (kode_transaksi, tanggal, id_user, total, bayar, kembalian, metode_bayar) VALUES (?, NOW(), ?, ?, ?, ?, ?)');
    $simpan->execute([$kode, $_SESSION['user']['id_user'], $total, $bayar, $bayar - $total, $metode]);
    $idTransaksi = (int) $pdo->lastInsertId();

    // 3) Simpan detail, kurangi stok, catat riwayat stok.
    $detail = $pdo->prepare('INSERT INTO transaksi_detail (id_transaksi, id_produk, harga_satuan, qty, subtotal) VALUES (?, ?, ?, ?, ?)');
    $kurangi = $pdo->prepare('UPDATE produk SET stok = stok - ? WHERE id_produk = ?');
    $catat = $pdo->prepare("INSERT INTO stok_log (id_produk, id_user, jenis, jumlah, keterangan) VALUES (?, ?, 'keluar', ?, ?)");
    foreach ($baris as $b) {
        $detail->execute([$idTransaksi, $b['id'], $b['harga'], $b['qty'], $b['subtotal']]);
        $kurangi->execute([$b['qty'], $b['id']]);
        $catat->execute([$b['id'], $_SESSION['user']['id_user'], $b['qty'], 'Penjualan ' . $kode]);
    }

    $pdo->commit();
    set_flash('success', 'Transaksi ' . $kode . ' berhasil disimpan.');
    redirect('views/struk.php?id=' . $idTransaksi);
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $pesan = ($ex instanceof RuntimeException) ? $ex->getMessage() : pesan_db('Transaksi gagal disimpan.', $ex);
    set_flash('error', $pesan);
    redirect('views/kasir.php');
}
