<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_pemilik();

function ringkasan_periode(PDO $db, string $dari, string $sampai): array
{
    $st = $db->prepare('SELECT COUNT(*) AS jml, COALESCE(SUM(total), 0) AS omzet FROM transaksi WHERE DATE(tanggal) BETWEEN ? AND ?');
    $st->execute([$dari, $sampai]);
    return $st->fetch();
}

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
$hari = (int) round((strtotime($sampai) - strtotime($dari)) / 86400) + 1;
$dariLalu = date('Y-m-d', strtotime($dari . ' -' . $hari . ' days'));
$sampaiLalu = date('Y-m-d', strtotime($dari . ' -1 day'));
$kini = ringkasan_periode($db, $dari, $sampai);
$lalu = ringkasan_periode($db, $dariLalu, $sampaiLalu);
[$teksOmzet, $kelasOmzet] = perubahan_persen((float) $kini['omzet'], (float) $lalu['omzet'], 'dari periode sebelumnya');
[$teksTrx, $kelasTrx] = perubahan_persen((float) $kini['jml'], (float) $lalu['jml'], 'dari periode sebelumnya');

// Omzet per hari (hari tanpa transaksi dihitung 0)
$st = $db->prepare('SELECT DATE(tanggal) AS hari, SUM(total) AS omzet FROM transaksi WHERE DATE(tanggal) BETWEEN ? AND ? GROUP BY DATE(tanggal)');
$st->execute([$dari, $sampai]);
$peta = [];
foreach ($st->fetchAll() as $r) {
    $peta[$r['hari']] = (float) $r['omzet'];
}
$label = [];
$nilai = [];
$maks = 0;
for ($i = 0; $i < $hari && $i < 92; $i++) {
    $tgl = date('Y-m-d', strtotime($dari . ' +' . $i . ' days'));
    $label[] = date('d/m', strtotime($tgl));
    $v = $peta[$tgl] ?? 0;
    $nilai[] = $v;
    $maks = max($maks, $v);
}

// Ukuran grafik garis
$lebar = 520;
$tinggi = 210;
$kiri = 62;
$kanan = 14;
$atas = 14;
$bawah = 30;
$pw = $lebar - $kiri - $kanan;
$ph = $tinggi - $atas - $bawah;
$langkah = nice_langkah($maks);
$sumbu = $langkah * 3;
$n = count($nilai);
$titik = [];
for ($i = 0; $i < $n; $i++) {
    $x = $kiri + ($n > 1 ? $pw * $i / ($n - 1) : $pw / 2);
    $y = $atas + $ph * (1 - $nilai[$i] / $sumbu);
    $titik[] = [round($x, 1), round($y, 1)];
}
$poligon = implode(' ', array_map(function ($t) {
    return $t[0] . ',' . $t[1];
}, $titik));
$selang = max(1, (int) ceil($n / 8));

// Penjualan per kategori (donat)
$st = $db->prepare('SELECT p.kategori, SUM(d.subtotal) AS omzet FROM transaksi_detail d JOIN transaksi t ON t.id_transaksi = d.id_transaksi JOIN produk p ON p.id_produk = d.id_produk WHERE DATE(t.tanggal) BETWEEN ? AND ? GROUP BY p.kategori ORDER BY omzet DESC');
$st->execute([$dari, $sampai]);
$perKategori = $st->fetchAll();
$totalKat = 0;
foreach ($perKategori as $k) {
    $totalKat += (float) $k['omzet'];
}
$warna = ['#1C8F57', '#F5B335', '#7A4FD3', '#E5484D', '#2D9CDB', '#8A9A91'];

// Produk terlaris
$st = $db->prepare('SELECT p.nama_produk, p.satuan, SUM(d.qty) AS qty, SUM(d.subtotal) AS omzet FROM transaksi_detail d JOIN transaksi t ON t.id_transaksi = d.id_transaksi JOIN produk p ON p.id_produk = d.id_produk WHERE DATE(t.tanggal) BETWEEN ? AND ? GROUP BY p.id_produk, p.nama_produk, p.satuan ORDER BY omzet DESC LIMIT 10');
$st->execute([$dari, $sampai]);
$terlaris = $st->fetchAll();

$pageTitle = 'Laporan Penjualan';
$active = 'laporan';
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">
    <form method="get" class="filters noprint">
        <span class="datebox"><?= ikon('calendar') ?><input type="date" name="dari" value="<?= e($dari) ?>"><i>–</i><input type="date" name="sampai" value="<?= e($sampai) ?>"></span>
        <select name="periode" id="periode" class="pilih">
            <?php foreach (['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan', 'custom' => 'Rentang tanggal'] as $k => $labelPeriode): ?>
                <option value="<?= $k ?>" <?= $periode === $k ? 'selected' : '' ?>><?= $labelPeriode ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Tampilkan</button>
    </form>
    <div class="row c2">
        <div class="card stat"><div class="si"><?= ikon('dollar') ?></div><div><small>Total Penjualan</small><b><?= e(rupiah($kini['omzet'])) ?></b><span class="tr <?= $kelasOmzet ?>"><?= e($teksOmzet) ?></span></div></div>
        <div class="card stat"><div class="si"><?= ikon('file') ?></div><div><small>Jumlah Transaksi</small><b><?= (int) $kini['jml'] ?></b><span class="tr <?= $kelasTrx ?>"><?= e($teksTrx) ?></span></div></div>
    </div>
    <div class="row c2">
        <div class="card">
            <h3>Grafik Penjualan</h3>
            <svg class="linechart" viewBox="0 0 <?= $lebar ?> <?= $tinggi ?>" role="img" aria-label="Grafik penjualan per hari">
                <?php for ($k = 0; $k <= 3; $k++): ?>
                    <?php $yy = round($atas + $ph * (1 - $k / 3), 1); ?>
                    <line x1="<?= $kiri ?>" y1="<?= $yy ?>" x2="<?= $lebar - $kanan ?>" y2="<?= $yy ?>" class="gl"/>
                    <text x="<?= $kiri - 8 ?>" y="<?= $yy + 3 ?>" text-anchor="end" class="gt"><?= number_format($langkah * $k, 0, ',', '.') ?></text>
                <?php endfor; ?>
                <?php if ($n > 1): ?><polyline points="<?= $poligon ?>" class="gline"/><?php endif; ?>
                <?php foreach ($titik as $i => $t): ?>
                    <circle cx="<?= $t[0] ?>" cy="<?= $t[1] ?>" r="3.2" class="gdot"><title><?= e($label[$i] . ': ' . rupiah($nilai[$i])) ?></title></circle>
                    <?php if ($i % $selang === 0): ?><text x="<?= $t[0] ?>" y="<?= $tinggi - 8 ?>" text-anchor="middle" class="gt"><?= e($label[$i]) ?></text><?php endif; ?>
                <?php endforeach; ?>
            </svg>
        </div>
        <div class="card">
            <h3>Penjualan per Kategori</h3>
            <?php if (!$perKategori): ?><p class="muted">Belum ada penjualan pada periode ini.</p><?php endif; ?>
            <div class="donut-box">
                <svg class="donut" viewBox="0 0 42 42" role="img" aria-label="Penjualan per kategori">
                    <circle cx="21" cy="21" r="15.91549431" fill="none" stroke="#EDF3EF" stroke-width="6"/>
                    <?php $geser = 25; ?>
                    <?php foreach ($perKategori as $i => $k): ?>
                        <?php $persen = $totalKat > 0 ? (float) $k['omzet'] / $totalKat * 100 : 0; ?>
                        <circle cx="21" cy="21" r="15.91549431" fill="none" stroke="<?= $warna[$i % count($warna)] ?>" stroke-width="6" stroke-dasharray="<?= round($persen, 2) ?> <?= round(100 - $persen, 2) ?>" stroke-dashoffset="<?= round($geser, 2) ?>"/>
                        <?php $geser -= $persen; ?>
                    <?php endforeach; ?>
                    <text x="21" y="22.4" text-anchor="middle" class="dt"><?= e(rupiah($totalKat)) ?></text>
                </svg>
                <div class="legend-donut">
                    <?php foreach ($perKategori as $i => $k): ?>
                        <span><i class="dot" style="background:<?= $warna[$i % count($warna)] ?>"></i><?= e($k['kategori']) ?> <b><?= $totalKat > 0 ? round((float) $k['omzet'] / $totalKat * 100) : 0 ?>%</b></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card tw">
        <h3 class="pad-h">Produk Terlaris</h3>
        <table>
            <tr><th>No</th><th>Nama Produk</th><th>Jumlah Terjual</th><th>Total Penjualan</th></tr>
            <?php foreach ($terlaris as $i => $t): ?>
                <tr><td><?= $i + 1 ?></td><td><b><?= e($t['nama_produk']) ?></b></td><td><?= e(format_qty($t['qty'])) ?> <?= e($t['satuan']) ?></td><td><?= e(rupiah($t['omzet'])) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$terlaris): ?><tr><td colspan="4" class="muted">Belum ada data.</td></tr><?php endif; ?>
        </table>
    </div>
    <div class="head-actions noprint"><button type="button" class="btn right" onclick="window.print()"><?= ikon('printer') ?> Cetak Laporan</button></div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
