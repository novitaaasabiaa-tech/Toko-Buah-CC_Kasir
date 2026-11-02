
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$db = db();

// =====================================
// DATA PENJUALAN HARI INI DAN KEMARIN
// =====================================

$omzetHariIni = (float) $db->query("
    SELECT COALESCE(SUM(total), 0)
    FROM transaksi
    WHERE DATE(tanggal) = CURDATE()
")->fetchColumn();

$omzetKemarin = (float) $db->query("
    SELECT COALESCE(SUM(total), 0)
    FROM transaksi
    WHERE DATE(tanggal) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
")->fetchColumn();

$trxHariIni = (int) $db->query("
    SELECT COUNT(*)
    FROM transaksi
    WHERE DATE(tanggal) = CURDATE()
")->fetchColumn();

$trxKemarin = (int) $db->query("
    SELECT COUNT(*)
    FROM transaksi
    WHERE DATE(tanggal) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
")->fetchColumn();

// =====================================
// DATA PRODUK DAN STOK
// =====================================

$produkAktif = (int) $db->query("
    SELECT COUNT(*)
    FROM produk
    WHERE status = 'aktif'
")->fetchColumn();

$stokMenipis = (int) $db->query("
    SELECT COUNT(*)
    FROM produk
    WHERE status = 'aktif'
    AND stok <= stok_minimum
")->fetchColumn();

// =====================================
// PERUBAHAN PENJUALAN
// =====================================

[$teksOmzet, $kelasOmzet] = perubahan_persen(
    $omzetHariIni,
    $omzetKemarin,
    'dari kemarin'
);

[$teksTrx, $kelasTrx] = perubahan_persen(
    (float) $trxHariIni,
    (float) $trxKemarin,
    'dari kemarin'
);

// =====================================
// GRAFIK PENJUALAN 7 HARI TERAKHIR
// =====================================

$mulai = date('Y-m-d', strtotime('-6 days'));

$st = $db->prepare("
    SELECT
        DATE(tanggal) AS hari,
        SUM(total) AS omzet
    FROM transaksi
    WHERE DATE(tanggal) >= ?
    GROUP BY DATE(tanggal)
");

$st->execute([$mulai]);

$peta = [];

foreach ($st->fetchAll() as $r) {
    $peta[$r['hari']] = (float) $r['omzet'];
}

$tujuhHari = [];
$maks = 0;

for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    $nilai = $peta[$tgl] ?? 0;

    $tujuhHari[] = [
        date('d/m', strtotime($tgl)),
        $nilai
    ];

    $maks = max($maks, $nilai);
}

$langkah = nice_langkah($maks);
$sumbu = $langkah * 3;

// =====================================
// PRODUK TERLARIS 7 HARI TERAKHIR
// =====================================

$st = $db->prepare("
    SELECT
        p.nama_produk,
        p.satuan,
        SUM(d.qty) AS qty
    FROM transaksi_detail d
    JOIN transaksi t
        ON t.id_transaksi = d.id_transaksi
    JOIN produk p
        ON p.id_produk = d.id_produk
    WHERE DATE(t.tanggal) >= ?
    GROUP BY
        p.id_produk,
        p.nama_produk,
        p.satuan
    ORDER BY qty DESC
    LIMIT 5
");

$st->execute([$mulai]);
$terlaris = $st->fetchAll();

// =====================================
// PENGATURAN HALAMAN
// =====================================

$pageTitle = 'Selamat Datang, ' . ($_SESSION['user']['nama'] ?? '');
$pageSub = 'Semoga harimu menyenangkan!';
$active = 'dashboard';

include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>

<div class="view">

   <!-- Banner Dashboard CC KASIR -->
<div style="
    width: 100%;
    margin: 0 0 28px 0;
    overflow: hidden;
    border-radius: 18px;
">
    <img
        src="<?= BASE_URL ?>assets/images/banner-buah.png"
        alt="Banner CC KASIR"
        style="
            display: block;
            width: 100%;
            height: auto;
            max-width: none;
            margin: 0;
        "
    >
</div>


    <!-- =================================
         KARTU STATISTIK DASHBOARD
    ================================== -->

    <div class="stats">

        <!-- Total Penjualan -->
        <div class="card stat">
            <div class="si">
                <?= ikon('dollar') ?>
            </div>

            <div>
                <small>Total Penjualan Hari Ini</small>

                <b><?= e(rupiah($omzetHariIni)) ?></b>

                <span class="tr <?= $kelasOmzet ?>">
                    <?= e($teksOmzet) ?>
                </span>
            </div>
        </div>


        <!-- Jumlah Transaksi -->
        <div class="card stat">
            <div class="si">
                <?= ikon('file') ?>
            </div>

            <div>
                <small>Jumlah Transaksi</small>

                <b><?= $trxHariIni ?></b>

                <span class="tr <?= $kelasTrx ?>">
                    <?= e($teksTrx) ?>
                </span>
            </div>
        </div>


        <!-- Total Produk -->
        <div class="card stat">
            <div class="si">
                <?= ikon('package') ?>
            </div>

            <div>
                <small>Total Produk</small>

                <b><?= $produkAktif ?></b>

                <span class="tr flat">
                    produk aktif dijual
                </span>
            </div>
        </div>


        <!-- Stok Menipis -->
        <div class="card stat">
            <div class="si warn">
                <?= ikon('alert') ?>
            </div>

            <div>
                <small>Stok Menipis</small>

                <b class="<?= $stokMenipis > 0 ? 'bad' : '' ?>">
                    <?= $stokMenipis ?>
                </b>

                <span class="tr <?= $stokMenipis > 0 ? 'down' : 'up' ?>">
                    <?= $stokMenipis > 0
                        ? 'Perlu perhatian'
                        : 'Semua stok aman' ?>
                </span>
            </div>
        </div>

    </div>


    <!-- =================================
         GRAFIK DAN PRODUK TERLARIS
    ================================== -->

    <div class="row c21">

        <!-- GRAFIK PENJUALAN -->
        <div class="card">

            <h3>Grafik Penjualan (7 Hari Terakhir)</h3>

            <div class="chart">

                <div class="yaxis">
                    <?php for ($k = 3; $k >= 0; $k--): ?>

                        <span>
                            <?= number_format(
                                $langkah * $k,
                                0,
                                ',',
                                '.'
                            ) ?>
                        </span>

                    <?php endfor; ?>
                </div>


                <div class="plot">

                    <div class="bars-inner">

                        <?php foreach ($tujuhHari as [$label, $nilai]): ?>

                            <div
                                class="bar"
                                style="height:<?= max(
                                    2,
                                    round($nilai / $sumbu * 100)
                                ) ?>%"
                                title="<?= e(rupiah($nilai)) ?>"
                            ></div>

                        <?php endforeach; ?>

                    </div>


                    <div class="xlabels">

                        <?php foreach ($tujuhHari as [$label, $nilai]): ?>

                            <span><?= e($label) ?></span>

                        <?php endforeach; ?>

                    </div>

                </div>

            </div>

        </div>


        <!-- PRODUK TERLARIS -->
        <div class="card">

            <h3>Produk Terlaris</h3>

            <?php if (!$terlaris): ?>

                <p class="muted">
                    Belum ada penjualan 7 hari terakhir.
                </p>

            <?php endif; ?>


            <?php foreach ($terlaris as $t): ?>

                <div class="top-item">

                    <img
                        src="<?= e(gambar_produk($t['nama_produk'])) ?>"
                        alt="<?= e($t['nama_produk']) ?>"
                    >

                    <span>
                        <?= e($t['nama_produk']) ?>
                    </span>

                    <b>
                        <?= e(format_qty($t['qty'])) ?>
                    </b>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
