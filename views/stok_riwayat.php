
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$jenis = ($_GET['jenis'] ?? 'masuk') === 'keluar'
    ? 'keluar'
    : 'masuk';

if ($jenis === 'masuk') {
    $kondisi = "
        (l.jenis = 'masuk'
        OR (l.jenis = 'penyesuaian' AND l.jumlah > 0))
    ";
} else {
    $kondisi = "
        (l.jenis = 'keluar'
        OR (l.jenis = 'penyesuaian' AND l.jumlah < 0))
    ";
}

$sql = "
    SELECT
        l.id_log,
        l.tanggal,
        p.nama_produk,
        p.kategori,
        p.satuan,
        l.jenis,
        l.jumlah,
        l.keterangan,
        u.nama AS pengguna
    FROM stok_log l
    JOIN produk p ON p.id_produk = l.id_produk
    JOIN `user` u ON u.id_user = l.id_user
    WHERE $kondisi
    ORDER BY l.tanggal DESC, l.id_log DESC
    LIMIT 100
";

$daftar = db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = $jenis === 'masuk'
    ? 'Stok Masuk'
    : 'Stok Keluar';

$active = 'stok';
$tabAktif = $jenis;

include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>

<div class="view">

    <?php include ROOT_PATH . '/includes/tab_stok.php'; ?>

    <div class="card tw">

        <table>

            <thead>
                <tr>
                    <th>No</th>
                    <th>Waktu</th>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                    <th>Oleh</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($daftar): ?>

                <?php foreach ($daftar as $i => $d): ?>

                    <?php
                    $penyesuaian = $d['jenis'] === 'penyesuaian';

                    $warna = $jenis === 'masuk'
                        ? 'ok'
                        : 'lw';

                    $labelJenis = $penyesuaian
                        ? 'Penyesuaian'
                        : ($jenis === 'masuk' ? 'Stok masuk' : 'Stok keluar');

                    $tanda = $jenis === 'masuk' ? '+' : '-';
                    ?>

                    <tr>

                        <td><?= $i + 1 ?></td>

                        <td>
                            <?= e(date(
                                'd/m/Y H:i',
                                strtotime($d['tanggal'])
                            )) ?>
                        </td>

                        <td class="flex">
                            <img
                                class="thumb"
                                src="<?= e(gambar_produk($d['nama_produk'])) ?>"
                                alt=""
                            >
                            <b><?= e($d['nama_produk']) ?></b>
                        </td>

                        <td><?= e($d['kategori']) ?></td>

                        <td>
                            <span class="bd <?= $warna ?>">
                                <?= e($labelJenis) ?>
                            </span>
                        </td>

                        <td>
                            <b>
                                <?= $tanda ?><?= e(format_qty(abs((float)$d['jumlah']))) ?>
                                <?= e($d['satuan']) ?>
                            </b>
                        </td>

                        <td>
                            <?= e($d['keterangan'] ?: '-') ?>
                        </td>

                        <td>
                            <?= e($d['pengguna']) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>
                    <td colspan="8" class="muted">
                        Belum ada riwayat stok
                        <?= $jenis === 'masuk' ? 'masuk' : 'keluar' ?>.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <p class="muted">
        Menampilkan <?= count($daftar) ?> dari maksimal 100 catatan terakhir.
    </p>

</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
