
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

$status = ($_POST['status'] ?? 'aktif') === 'nonaktif'
    ? 'nonaktif'
    : 'aktif';

$slug = slug_produk($nama);

// Validasi data
if (
    $nama === '' ||
    $slug === '' ||
    $kategori === '' ||
    $satuan === '' ||
    $harga <= 0 ||
    $stokMin < 0 ||
    $stokAwal < 0
) {
    set_flash('error', 'Data produk belum lengkap atau tidak valid.');
    redirect(
        $id > 0
            ? 'views/produk_form.php?id=' . $id
            : 'views/produk_form.php'
    );
}

$pdo = db();

try {

    $pdo->beginTransaction();

    // Periksa apakah nama produk sudah digunakan
    $cekNama = $pdo->prepare("
        SELECT id_produk
        FROM produk
        WHERE LOWER(TRIM(nama_produk)) = LOWER(TRIM(?))
        AND id_produk != ?
        LIMIT 1
    ");

    $cekNama->execute([$nama, $id]);

    if ($cekNama->fetch()) {

        $pdo->rollBack();

        set_flash(
            'error',
            'Nama produk sudah tersedia. Gunakan nama produk yang berbeda.'
        );

        redirect(
            $id > 0
                ? 'views/produk_form.php?id=' . $id
                : 'views/produk_form.php'
        );
    }

    if ($id > 0) {

        // Ambil produk lama
        $cek = $pdo->prepare("
            SELECT nama_produk
            FROM produk
            WHERE id_produk = ?
            FOR UPDATE
        ");

        $cek->execute([$id]);

        $namaLama = $cek->fetchColumn();

        if ($namaLama === false) {
            $pdo->rollBack();

            set_flash('error', 'Produk tidak ditemukan.');

            redirect('views/produk.php');
        }

        // Update tanpa mengubah stok
        $st = $pdo->prepare("
            UPDATE produk
            SET
                nama_produk = ?,
                kategori = ?,
                harga = ?,
                satuan = ?,
                stok_minimum = ?,
                status = ?
            WHERE id_produk = ?
        ");

        $st->execute([
            $nama,
            $kategori,
            $harga,
            $satuan,
            $stokMin,
            $status,
            $id
        ]);

        $namaLama = (string) $namaLama;

    } else {

        // Tambah produk baru
        $st = $pdo->prepare("
            INSERT INTO produk (
                nama_produk,
                kategori,
                harga,
                satuan,
                stok,
                stok_minimum,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $st->execute([
            $nama,
            $kategori,
            $harga,
            $satuan,
            $stokAwal,
            $stokMin,
            $status
        ]);

        $baru = (int) $pdo->lastInsertId();

        // Catat stok awal
        if ($stokAwal > 0) {

            $idUser = (int) ($_SESSION['user']['id_user'] ?? 0);

            $log = $pdo->prepare("
                INSERT INTO stok_log (
                    id_produk,
                    id_user,
                    jenis,
                    jumlah,
                    keterangan
                )
                VALUES (?, ?, 'masuk', ?, 'Stok awal')
            ");

            $log->execute([
                $baru,
                $idUser,
                $stokAwal
            ]);
        }
    }

    $pdo->commit();

    // Pindahkan foto lama jika nama produk berubah
    if ($id > 0 && $namaLama !== $nama) {
        pindah_foto(slug_produk($namaLama), $slug);
    }

} catch (PDOException $ex) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    set_flash(
        'error',
        pesan_db('Gagal menyimpan produk.', $ex)
    );

    redirect(
        $id > 0
            ? 'views/produk_form.php?id=' . $id
            : 'views/produk_form.php'
    );

} catch (Throwable $ex) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    set_flash('error', 'Terjadi kesalahan saat menyimpan produk.');

    redirect('views/produk.php');
}

// Simpan foto setelah data berhasil disimpan
$galatFoto = simpan_foto_produk(
    $_FILES['foto'] ?? ['error' => UPLOAD_ERR_NO_FILE],
    $slug
);

if ($galatFoto !== null) {

    set_flash(
        'error',
        'Produk tersimpan, tetapi foto belum berhasil: ' . $galatFoto
    );

} else {

    set_flash('success', 'Produk berhasil disimpan.');
}

redirect('views/produk.php');
