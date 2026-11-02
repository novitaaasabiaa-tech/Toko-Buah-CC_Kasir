<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$produk = db()->query("SELECT id_produk, nama_produk, kategori, harga, satuan, stok FROM produk WHERE status = 'aktif' ORDER BY nama_produk")->fetchAll();
$kategori = db()->query("SELECT DISTINCT kategori FROM produk WHERE status = 'aktif' ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);
$dataJs = [];
foreach ($produk as $p) {
    $dataJs[] = [
        'id'     => (int) $p['id_produk'],
        'nama'   => $p['nama_produk'],
        'harga'  => (int) $p['harga'],
        'satuan' => $p['satuan'],
        'stok'   => (float) $p['stok'],
        'gambar' => gambar_produk($p['nama_produk']),
    ];
}

$pageTitle = 'Kasir';
$active = 'kasir';
$extraJs = ['kasir.js'];
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">
    <div class="kasir-grid">
        <section class="kasir-left">
            <label class="searchbar"><?= ikon('search') ?><input id="cari" type="search" placeholder="Cari produk..."></label>
            <div class="tabs" id="tabs">
                <button type="button" class="tab on" data-kat="">Semua</button>
                <?php foreach ($kategori as $k): ?>
                    <button type="button" class="tab" data-kat="<?= e(strtolower($k)) ?>"><?= e($k) ?></button>
                <?php endforeach; ?>
            </div>
            <div class="grid" id="grid">
                <?php foreach ($produk as $p): ?>
                    <?php $habis = (float) $p['stok'] <= 0; ?>
                    <div class="prod <?= $habis ? 'habis' : '' ?>" data-nama="<?= e(strtolower($p['nama_produk'])) ?>" data-kat="<?= e(strtolower($p['kategori'])) ?>" <?= $habis ? '' : 'data-add="' . (int) $p['id_produk'] . '"' ?>>
                        <img src="<?= e(gambar_produk($p['nama_produk'])) ?>" alt="<?= e($p['nama_produk']) ?>">
                        <b><?= e($p['nama_produk']) ?></b>
                        <small><?= e(rupiah($p['harga'])) ?>/<?= e($p['satuan']) ?></small>
                        <?php if ($habis): ?><span class="tag-habis">Habis</span><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <form class="card cart" id="form-kasir" method="post" action="<?= BASE_URL ?>actions/transaksi_simpan.php">
            <?= csrf_field() ?>
            <input type="hidden" name="items" id="items" value="[]">
            <div class="cart-top"><h3>Keranjang</h3><button type="button" class="link-red" id="kosongkan">Hapus Semua</button></div>
            <div class="chead"><span>Produk</span><span>Harga</span><span>Jumlah</span><span>Subtotal</span></div>
            <div id="cart-list"></div>
            <div class="sum total"><span>Total</span><span id="cart-total">Rp 0</span></div>
            <h4>Pembayaran</h4>
            <div class="segs">
                <label class="seg on"><input type="radio" name="metode" value="tunai" checked>Tunai</label>
                <label class="seg"><input type="radio" name="metode" value="transfer">Transfer</label>
            </div>
            <div class="pay-grid">
                <label class="lbl">Jumlah Bayar<span class="rp"><i>Rp</i><input type="number" id="bayar" name="bayar" min="0" step="1" placeholder="0"></span></label>
                <div class="lbl">Kembalian<div class="kembali" id="kembalian">Rp 0</div></div>
            </div>
            <button type="submit" class="btn w big"><?= ikon('printer') ?> Simpan Transaksi</button>
        </form>
    </div>
</div>
<script>
window.PRODUK = <?= json_encode($dataJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<?php include ROOT_PATH . '/includes/footer.php'; ?>