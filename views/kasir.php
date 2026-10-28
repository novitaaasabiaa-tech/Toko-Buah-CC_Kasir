<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$produk = db()->query("SELECT id_produk, nama_produk, kategori, harga, satuan, stok FROM produk WHERE status = 'aktif' ORDER BY nama_produk")->fetchAll();
$dataJs = [];
foreach ($produk as $p) {
    $dataJs[] = [
        'id'     => (int) $p['id_produk'],
        'nama'   => $p['nama_produk'],
        'harga'  => (int) $p['harga'],
        'satuan' => $p['satuan'],
        'stok'   => (float) $p['stok'],
    ];
}

$pageTitle = 'Kasir — Transaksi Baru';
$active = 'kasir';
$extraJs = ['kasir.js'];
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">
    <div class="kasir">
        <div>
            <div class="tools">
                <input id="cari" type="search" placeholder="🔍 Cari nama produk...">
            </div>
            <div class="grid" id="grid">
                <?php foreach ($produk as $p): ?>
                    <?php $habis = (float) $p['stok'] <= 0; ?>
                    <div class="prod" data-nama="<?= e(strtolower($p['nama_produk'])) ?>">
                        <img src="<?= e(gambar_produk($p['nama_produk'])) ?>" alt="<?= e($p['nama_produk']) ?>">
                        <div>
                            <b><?= e($p['nama_produk']) ?></b>
                            <small><?= e(rupiah($p['harga'])) ?> / <?= e($p['satuan']) ?> • stok <?= e(format_qty($p['stok'])) ?></small>
                            <button type="button" class="btn s w" data-add="<?= (int) $p['id_produk'] ?>" <?= $habis ? 'disabled' : '' ?>>
                                <?= $habis ? 'Habis' : '+ Tambah' ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <form class="card cart" id="form-kasir" method="post" action="<?= BASE_URL ?>actions/transaksi_simpan.php">
            <?= csrf_field() ?>
            <input type="hidden" name="items" id="items" value="[]">
            <h3>🛒 Keranjang</h3>
            <div id="cart-list"></div>
            <div class="tot"><span>TOTAL</span><span id="cart-total">Rp 0</span></div>
            <label class="lbl">Metode bayar
                <select name="metode"><option value="tunai">Tunai</option><option value="transfer">Transfer</option></select>
            </label>
            <label class="lbl">Bayar
                <input type="number" id="bayar" name="bayar" min="0" step="500" placeholder="Nominal bayar">
            </label>
            <div class="kmb"><span>Kembalian</span><span id="kembalian">Rp 0</span></div>
            <button type="submit" class="btn y w big">Simpan & Cetak Struk</button>
            <button type="button" class="btn o w" id="kosongkan">Batal / Kosongkan</button>
        </form>
    </div>
</div>
<script>const PRODUK = <?= json_encode($dataJs, JSON_UNESCAPED_UNICODE) ?>;</script>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
