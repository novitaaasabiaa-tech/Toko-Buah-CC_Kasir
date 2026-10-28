<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_pemilik();

$periode = $_GET['periode'] ?? 'mingguan';
$hariIni = date('Y-m-d');
if ($periode === 'harian') {
    $dari = $sampai = $hariIni;
} elseif ($periode === 'bulanan') {
    $dari = date('Y-m-01');
    $sampai = $hariIni;
} elseif ($periode === 'custom') {
    $dari = valid_date($_GET['dari'] ?? '') ?: $hariIni;
    $sampai = valid_date($_GET['sampai'] ?? '') ?: $hariIni;
    if ($dari > $sampai) {
        [$dari, $sampai] = [$sampai, $dari];
    }
} else {
    $periode = 'mingguan';
    $dari = date('Y-m-d', strtotime('-6 days'));
    $sampai = $hariIni;
}

$db = db();
$st = $db->prepare('SELECT COUNT(*) AS jml, COALESCE(SUM(total), 0) AS omzet FROM transaksi WHERE DATE(tanggal) BETWEEN ? AND ?');
$st->execute([$dari, $sampai]);
$ringkas = $st->fetch();
$rata = $ringkas['jml'] > 0 ? $ringkas['omzet'] / $ringkas['jml'] : 0;

$st = $db->prepare('SELECT DATE(tanggal) AS hari, SUM(total) AS omzet FROM transaksi WHERE DATE(tanggal) BETWEEN ? AND ? GROUP BY DATE(tanggal) ORDER BY hari');
$st->execute([$dari, $sampai]);
$perHari = $st->fetchAll();
$maks = 1;
foreach ($perHari as $r) {
    if ($r['omzet'] > $maks) {
        $maks = $r['omzet'];
    }
}

$st = $db->prepare('SELECT p.nama_produk, p.satuan, SUM(d.qty) AS qty, SUM(d.subtotal) AS omzet FROM transaksi_detail d JOIN transaksi t ON t.id_transaksi = d.id_transaksi JOIN produk p ON p.id_produk = d.id_produk WHERE DATE(t.tanggal) BETWEEN ? AND ? GROUP BY p.id_produk, p.nama_produk, p.satuan ORDER BY omzet DESC LIMIT 10');
$st->execute([$dari, $sampai]);
$terlaris = $st->fetchAll();

$pageTitle = 'Laporan Penjualan';
$active = 'laporan';
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">
    <form method="get" class="tools noprint">
        <?php foreach (['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan'] as $k => $label): ?>
            <a class="pill <?= $periode === $k ? 'on' : '' ?>" href="?periode=<?= $k ?>"><?= $label ?></a>
        <?php endforeach; ?>
        <input type="hidden" name="periode" value="custom">
        <input type="date" name="dari" value="<?= e($dari) ?>">
        <input type="date" name="sampai" value="<?= e($sampai) ?>">
        <button class="btn" type="submit">Tampilkan</button>
        <button class="btn o right" type="button" onclick="window.print()">🖨 Cetak Laporan</button>
    </form>
    <p class="muted">Periode: <?= e(date('d/m/Y', strtotime($dari))) ?> – <?= e(date('d/m/Y', strtotime($sampai))) ?></p>
    <div class="row c3">
        <div class="card stat"><div class="ico">💰</div><div><small>Total Penjualan</small><b><?= e(rupiah($ringkas['omzet'])) ?></b></div></div>
        <div class="card stat"><div class="ico">🧾</div><div><small>Jumlah Transaksi</small><b><?= (int) $ringkas['jml'] ?></b></div></div>
        <div class="card stat"><div class="ico">📈</div><div><small>Rata-rata / Transaksi</small><b><?= e(rupiah($rata)) ?></b></div></div>
    </div>
    <div class="row c21">
        <div class="card">
            <h3>Grafik Penjualan per Hari</h3>
            <?php if (!$perHari): ?><p class="muted">Belum ada transaksi pada periode ini.</p><?php endif; ?>
            <div class="bars">
                <?php foreach ($perHari as $r): ?>
                    <div style="height:<?= max(3, round($r['omzet'] / $maks * 100)) ?>%" title="<?= e(rupiah($r['omzet'])) ?>"><span><?= e(date('d/m', strtotime($r['hari']))) ?></span></div>
                <?php endforeach; ?>
            </div>
            <div class="bars-gap"></div>
        </div>
        <div class="card">
            <h3>Produk Terlaris</h3>
            <?php foreach ($terlaris as $i => $t): ?>
                <div class="ln"><span><?= $i + 1 ?>. <?= e($t['nama_produk']) ?></span><b><?= e(format_qty($t['qty'])) ?> <?= e($t['satuan']) ?></b></div>
            <?php endforeach; ?>
            <?php if (!$terlaris): ?><p class="muted">Belum ada data.</p><?php endif; ?>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
