
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('views/kasir.php');
}

csrf_check();

$items = json_decode($_POST['items'] ?? '[]', true);
$bayar = (int) round(angka_input($_POST['bayar'] ?? 0));

$metode = in_array(
    $_POST['metode'] ?? '',
    ['tunai', 'transfer'],
    true
) ? $_POST['metode'] : 'tunai';

if (!is_array($items) || count($items) === 0) {
    set_flash('error', 'Keranjang masih kosong.');
    redirect('views/kasir.php');
}

$pdo = db();

try {
    $pdo->beginTransaction();

    // 1. Gabungkan produk yang sama jika dikirim lebih dari sekali.
    $daftarItem = [];

    foreach ($items as $item) {
        $idProduk = (int) ($item['id'] ?? 0);
        $qtyInput = $item['qty'] ?? null;

        if (
            $idProduk <= 0 ||
            !is_numeric($qtyInput) ||
            !is_finite((float) $qtyInput)
        ) {
            throw new RuntimeException('Data keranjang tidak valid.');
        }

        $qty = (float) $qtyInput;

        if ($qty <= 0 || abs($qty - round($qty, 2)) > 0.000001) {
            throw new RuntimeException('Jumlah pembelian maksimal 2 angka di belakang koma.');
        }

        if (!isset($daftarItem[$idProduk])) {
            $daftarItem[$idProduk] = 0;
        }

        $daftarItem[$idProduk] += round($qty, 2);
    }

    // 2. Validasi produk, harga, dan stok dari database.
    $cek = $pdo->prepare("
        SELECT id_produk, nama_produk, harga, stok
        FROM produk
        WHERE id_produk = ?
          AND status = 'aktif'
        FOR UPDATE
    ");

    $total = 0;
    $baris = [];

    foreach ($daftarItem as $idProduk => $qty) {
        $qty = round($qty, 2);

        $cek->execute([$idProduk]);
        $produk = $cek->fetch(PDO::FETCH_ASSOC);

        if (!$produk) {
            throw new RuntimeException(
                'Produk tidak ditemukan atau sudah nonaktif.'
            );
        }

        $stok = (float) $produk['stok'];

        if ($qty > $stok) {
            throw new RuntimeException(
                'Stok ' . $produk['nama_produk'] .
                ' tidak cukup (tersedia ' . format_qty($stok) . ').'
            );
        }

        $harga = (int) $produk['harga'];
        $subtotal = (int) round($qty * $harga);

        $total += $subtotal;

        $baris[] = [
            'id' => (int) $idProduk,
            'harga' => $harga,
            'qty' => $qty,
            'subtotal' => $subtotal
        ];
    }

    if ($bayar < $total) {
        throw new RuntimeException(
            'Nominal bayar kurang dari total belanja.'
        );
    }

    // 3. Buat kode transaksi.
    $urut = (int) $pdo->query("
        SELECT COUNT(*)
        FROM transaksi
        WHERE DATE(tanggal) = CURDATE()
    ")->fetchColumn() + 1;

    $kode = sprintf('TRX-%s-%03d', date('Ymd'), $urut);

    // 4. Simpan transaksi.
    $simpan = $pdo->prepare("
        INSERT INTO transaksi
        (
            kode_transaksi,
            tanggal,
            id_user,
            total,
            bayar,
            kembalian,
            metode_bayar
        )
        VALUES (?, NOW(), ?, ?, ?, ?, ?)
    ");

    $simpan->execute([
        $kode,
        $_SESSION['user']['id_user'],
        $total,
        $bayar,
        $bayar - $total,
        $metode
    ]);

    $idTransaksi = (int) $pdo->lastInsertId();

    // 5. Siapkan penyimpanan detail dan pengurangan stok.
    $detail = $pdo->prepare("
        INSERT INTO transaksi_detail
        (id_transaksi, id_produk, harga_satuan, qty, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");

    $kurangi = $pdo->prepare("
        UPDATE produk
        SET stok = stok - ?
        WHERE id_produk = ?
    ");

    $catat = $pdo->prepare("
        INSERT INTO stok_log
        (id_produk, id_user, jenis, jumlah, keterangan)
        VALUES (?, ?, 'keluar', ?, ?)
    ");

    // 6. Simpan detail, kurangi stok, dan catat riwayat stok.
    foreach ($baris as $b) {

        $detail->execute([
            $idTransaksi,
            $b['id'],
            $b['harga'],
            $b['qty'],
            $b['subtotal']
        ]);

        $kurangi->execute([
            $b['qty'],
            $b['id']
        ]);

        $catat->execute([
            $b['id'],
            $_SESSION['user']['id_user'],
            $b['qty'],
            'Penjualan ' . $kode
        ]);
    }

    // 7. Selesaikan transaksi.
    $pdo->commit();

    set_flash(
        'success',
        'Transaksi ' . $kode . ' berhasil disimpan.'
    );

    redirect('views/struk.php?id=' . $idTransaksi);

} catch (Throwable $ex) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $pesan = ($ex instanceof RuntimeException)
        ? $ex->getMessage()
        : pesan_db('Transaksi gagal disimpan.', $ex);

    set_flash('error', $pesan);
    redirect('views/kasir.php');
}
