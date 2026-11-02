
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$db = db();

$q = trim($_GET['q'] ?? '');
$kat = trim($_GET['kategori'] ?? '');

// Mengambil data produk dan mendeteksi nama yang duplikat
$sql = "
    SELECT
        p.id_produk,
        p.nama_produk,
        p.kategori,
        p.harga,
        p.satuan,
        p.stok,
        p.stok_minimum,
        p.status,
        (
            SELECT COUNT(*)
            FROM produk p2
            WHERE LOWER(TRIM(p2.nama_produk)) =
                  LOWER(TRIM(p.nama_produk))
        ) AS jumlah_nama
    FROM produk p
    WHERE 1 = 1
";

$param = [];

if ($q !== '') {
    $sql .= " AND p.nama_produk LIKE ?";
    $param[] = '%' . $q . '%';
}

if ($kat !== '') {
    $sql .= " AND p.kategori = ?";
    $param[] = $kat;
}

$sql .= " ORDER BY p.status, p.nama_produk, p.id_produk";

$st = $db->prepare($sql);
$st->execute($param);
$daftar = $st->fetchAll(PDO::FETCH_ASSOC);

$semuaKategori = $db->query(
    "SELECT DISTINCT kategori
     FROM produk
     WHERE kategori IS NOT NULL AND kategori <> ''
     ORDER BY kategori"
)->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Data Produk';
$active = 'produk';

$pageActions = is_pemilik()
    ? '<a class="btn" href="' . BASE_URL .
      'views/produk_form.php">' . ikon('plus') .
      ' Tambah Produk</a>'
    : '';

include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>

<div class="view">

    <form method="get" class="tools">

        <label class="searchbar grow">
            <?= ikon('search') ?>
            <input
                type="text"
                name="q"
                value="<?= e($q) ?>"
                placeholder="Cari nama produk..."
            >
        </label>

        <select
            name="kategori"
            class="pilih"
            onchange="this.form.submit()"
        >
            <option value="">Semua Kategori</option>

            <?php foreach ($semuaKategori as $k): ?>
                <option
                    value="<?= e($k) ?>"
                    <?= $kat === $k ? 'selected' : '' ?>
                >
                    <?= e($k) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn">
            Cari
        </button>

        <?php if ($q !== '' || $kat !== ''): ?>
            <a href="<?= BASE_URL ?>views/produk.php"
               class="btn o">
                Reset
            </a>
        <?php endif; ?>

    </form>

    <div class="card tw">

        <table>

            <thead>
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>

                    <?php if (is_pemilik()): ?>
                        <th>Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody>

            <?php if ($daftar): ?>

                <?php foreach ($daftar as $i => $p): ?>

                    <tr class="<?= $p['status'] === 'aktif' ? '' : 'mati' ?>">

                        <td><?= $i + 1 ?></td>

                        <td>
                            <img
                                class="thumb"
                                src="<?= e(gambar_produk($p['nama_produk'])) ?>"
                                alt="<?= e($p['nama_produk']) ?>"
                            >
                        </td>

                        <td>
                            <b><?= e($p['nama_produk']) ?></b>

                            <?php if ((int)$p['jumlah_nama'] > 1): ?>
                                <br>
                                <span class="bd no">
                                    Nama duplikat
                                </span>
                            <?php endif; ?>

                            <?php if ($p['status'] !== 'aktif'): ?>
                                <br>
                                <span class="bd no">
                                    Nonaktif
                                </span>
                            <?php endif; ?>
                        </td>

                        <td><?= e($p['kategori']) ?></td>

                        <td><?= e(rupiah($p['harga'])) ?></td>

                        <td>
                            <?= e(format_qty($p['stok'])) ?>
                            <?= e($p['satuan']) ?>
                        </td>

                        <?php if (is_pemilik()): ?>

                            <td class="flex">

                                <a
                                    class="btn s"
                                    href="<?= BASE_URL ?>views/produk_form.php?id=<?= (int)$p['id_produk'] ?>"
                                >
                                    Edit
                                </a>

                                <form
                                    method="post"
                                    action="<?= BASE_URL ?>actions/produk_status.php"
                                    class="inline"
                                >
                                    <?= csrf_field() ?>

                                    <input
                                        type="hidden"
                                        name="id_produk"
                                        value="<?= (int)$p['id_produk'] ?>"
                                    >

                                    <button
                                        class="btn s <?= $p['status'] === 'aktif' ? 'danger' : 'o' ?>"
                                        type="submit"
                                    >
                                        <?= $p['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                    </button>

                                </form>

                                <form
                                    method="post"
                                    action="<?= BASE_URL ?>actions/produk_hapus.php"
                                    class="inline"
                                    onsubmit="return confirm('Yakin ingin menghapus produk ini secara permanen? Tindakan ini tidak dapat dibatalkan.');"
                                >
                                    <?= csrf_field() ?>

                                    <input
                                        type="hidden"
                                        name="id_produk"
                                        value="<?= (int)$p['id_produk'] ?>"
                                    >

                                    <button
                                        class="btn s danger"
                                        type="submit"
                                    >
                                        Hapus
                                    </button>

                                </form>

                            </td>

                        <?php endif; ?>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>
                    <td
                        colspan="<?= is_pemilik() ? 7 : 6 ?>"
                        class="muted"
                    >
                        Produk tidak ditemukan.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <p class="muted">
        Menampilkan <?= count($daftar) ?> data produk.
    </p>

</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
