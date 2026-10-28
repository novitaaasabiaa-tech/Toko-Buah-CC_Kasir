<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$db = db();
$produkAktif = (int) $db->query("SELECT COUNT(*) FROM produk WHERE status = 'aktif'")->fetchColumn();
$stokMenipis = (int) $db->query("SELECT COUNT(*) FROM produk WHERE status = 'aktif' AND stok <= stok_minimum")->fetchColumn();
$trxHariIni = (int) $db->query("SELECT COUNT(*) FROM transaksi WHERE DATE(tanggal) = CURDATE()")->fetchColumn();
$omzetHariIni = (float) $db->query("SELECT COALESCE(SUM(total), 0) FROM transaksi WHERE DATE(tanggal) = CURDATE()")->fetchColumn();

// Penjualan 7 hari terakhir
$st = $db->prepare('SELECT DATE(tanggal) AS hari, SUM(total) AS omzet FROM transaksi WHERE DATE(tanggal) >= ? GROUP BY DATE(tanggal)');
$st->execute([date('Y-m-d', strtotime('-6 days'))]);
$peta = [];
foreach ($st->fetchAll() as $r) {
    $peta[$r['hari']] = (float) $r['omzet'];
}
$namaHari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
$tujuhHari = [];
$maks = 1;
for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    $nilai = $peta[$tgl] ?? 0;
    $tujuhHari[] = [$namaHari[(int) date('w', strtotime($tgl))], $nilai];
    if ($nilai > $maks) {
        $maks = $nilai;
    }
}
$menipis = $db->query("SELECT nama_produk, stok, satuan FROM produk WHERE status = 'aktif' AND stok <= stok_minimum ORDER BY stok LIMIT 6")->fetchAll();

$pageTitle = 'Dashboard';
$active = 'dashboard';
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">
    <div class="row c4">
        <div class="card stat"><div class="ico">💰</div><div><small>Penjualan Hari Ini</small><b><?= rupiah($omzetHariIni) ?></b></div></div>
        <div class="card stat"><div class="ico">🧾</div><div><small>Transaksi Hari Ini</small><b><?= $trxHariIni ?></b></div></div>
        <div class="card stat"><div class="ico">🍎</div><div><small>Produk Aktif</small><b><?= $produkAktif ?></b></div></div>
        <div class="card stat"><div class="ico warn">⚠️</div><div><small>Stok Menipis</small><b><?= $stokMenipis ?> item</b></div></div>
    </div>
    <div class="row c21">
        <div class="card">
            <h3>Penjualan 7 Hari Terakhir</h3>
            <div class="bars">
                <?php foreach ($tujuhHari as [$label, $nilai]): ?>
                    <div style="height:<?= max(3, round($nilai / $maks * 100)) ?>%" title="<?= e(rupiah($nilai)) ?>"><span><?= e($label) ?></span></div>
                <?php endforeach; ?>
            </div>
            <div class="bars-gap"></div>
        </div>
        <div class="card">
            <h3>Stok Menipis</h3>
            <?php if (!$menipis): ?><p class="muted">Semua stok aman.</p><?php endif; ?>
            <?php foreach ($menipis as $m): ?>
                <div class="ln"><span><?= e($m['nama_produk']) ?></span><span class="bd lw"><?= e(format_qty($m['stok'])) ?> <?= e($m['satuan']) ?></span></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
