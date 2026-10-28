<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$st = db()->prepare('SELECT t.*, u.nama AS kasir FROM transaksi t JOIN `user` u ON u.id_user = t.id_user WHERE t.id_transaksi = ?');
$st->execute([$id]);
$trx = $st->fetch();
if (!$trx) {
    set_flash('error', 'Transaksi tidak ditemukan.');
    redirect('views/transaksi.php');
}
$st = db()->prepare('SELECT p.nama_produk, p.satuan, d.harga_satuan, d.qty, d.subtotal FROM transaksi_detail d JOIN produk p ON p.id_produk = d.id_produk WHERE d.id_transaksi = ? ORDER BY d.id_detail');
$st->execute([$id]);
$detail = $st->fetchAll();

$pageTitle = 'Struk Transaksi';
$active = 'transaksi';
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">
    <div class="card struk">
        <div class="okc">✓</div>
        <h2>Transaksi Berhasil</h2>
        <p class="muted center"><?= e($trx['kode_transaksi']) ?> • <?= e(date('d/m/Y H:i', strtotime($trx['tanggal']))) ?> • Kasir: <?= e($trx['kasir']) ?></p>
        <?php foreach ($detail as $d): ?>
            <div class="ln">
                <span><?= e($d['nama_produk']) ?> × <?= e(format_qty($d['qty'])) ?> <?= e($d['satuan']) ?><br><small class="muted"><?= e(rupiah($d['harga_satuan'])) ?></small></span>
                <span><?= e(rupiah($d['subtotal'])) ?></span>
            </div>
        <?php endforeach; ?>
        <div class="ln b"><span>Total</span><span><?= e(rupiah($trx['total'])) ?></span></div>
        <div class="ln"><span>Bayar (<?= e($trx['metode_bayar']) ?>)</span><span><?= e(rupiah($trx['bayar'])) ?></span></div>
        <div class="ln"><b>Kembalian</b><b><?= e(rupiah($trx['kembalian'])) ?></b></div>
        <div class="btnrow noprint">
            <button type="button" class="btn o" onclick="window.print()">Cetak Struk</button>
            <a class="btn" href="<?= BASE_URL ?>views/kasir.php">Transaksi Baru</a>
            <a class="btn o" href="<?= BASE_URL ?>views/transaksi.php">Riwayat</a>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
