
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_pemilik();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('views/stok.php');
}

csrf_check();

$idProduk = (int) ($_POST['id_produk'] ?? 0);

$jenisInput = $_POST['jenis'] ?? 'masuk';
$jenis = $jenisInput === 'penyesuaian'
    ? 'penyesuaian'
    : 'masuk';

$jumlah = round(angka_input($_POST['jumlah'] ?? 0), 2);

$ket = mb_substr(
    trim($_POST['keterangan'] ?? ''),
    0,
    150
);

$idUser = (int) ($_SESSION['user']['id_user'] ?? 0);

// Validasi input
if (
    $idProduk <= 0 ||
    $idUser <= 0 ||
    !is_finite($jumlah) ||
    $jumlah == 0 ||
    ($jenis === 'masuk' && $jumlah < 0)
) {
    set_flash('error', 'Data perubahan stok tidak valid.');
    redirect('views/stok.php');
}

$pdo = db();

try {

    $pdo->beginTransaction();

    // Periksa produk dan kunci data
    $cek = $pdo->prepare("
        SELECT stok, status
        FROM produk
        WHERE id_produk = ?
        FOR UPDATE
    ");

    $cek->execute([$idProduk]);

    $produk = $cek->fetch(PDO::FETCH_ASSOC);

    if (!$produk) {
        throw new RuntimeException('Produk tidak ditemukan.');
    }

    if ($produk['status'] !== 'aktif') {
        throw new RuntimeException(
            'Produk nonaktif tidak dapat diubah stoknya.'
        );
    }

    $stokLama = (float) $produk['stok'];

    // Hitung stok baru
    $stokBaru = round($stokLama + $jumlah, 2);

    // Cegah stok minus
    if ($stokBaru < 0) {
        throw new RuntimeException(
            'Stok tidak boleh menjadi minus. Stok tersedia: ' .
            format_qty($stokLama)
        );
    }

    // Perbarui stok
    $ubah = $pdo->prepare("
        UPDATE produk
        SET stok = ?
        WHERE id_produk = ?
    ");

    $ubah->execute([
        $stokBaru,
        $idProduk
    ]);

    // Simpan riwayat stok
    $log = $pdo->prepare("
        INSERT INTO stok_log (
            id_produk,
            id_user,
            jenis,
            jumlah,
            keterangan
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    $log->execute([
        $idProduk,
        $idUser,
        $jenis,
        $jumlah,
        $ket
    ]);

    $pdo->commit();

    set_flash(
        'success',
        'Perubahan stok berhasil dicatat. Stok sekarang: ' .
        format_qty($stokBaru)
    );

} catch (Throwable $ex) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($ex instanceof RuntimeException) {
        set_flash('error', $ex->getMessage());
    } else {
        set_flash(
            'error',
            pesan_db('Gagal mencatat perubahan stok.', $ex)
        );
    }
}

redirect('views/stok.php');
