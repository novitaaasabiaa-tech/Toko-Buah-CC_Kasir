
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$db = db();

$id = (int) ($_GET['id'] ?? 0);

// Pengaturan toko
$pengaturan = [
    'nama_toko' => 'Toko Buah CC',
    'alamat' => '',
    'telepon' => '',
    'logo' => 'logo-toko-buah-cc.png'
];

try {
    $dataToko = $db->query("
        SELECT nama_toko, alamat, telepon, logo
        FROM pengaturan_toko
        WHERE id = 1
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if ($dataToko) {
        $pengaturan = array_merge($pengaturan, $dataToko);
    }
} catch (Throwable $e) {
    // Gunakan informasi toko bawaan jika tabel belum tersedia
}

// Pastikan nama file logo aman
$logoToko = !empty($pengaturan['logo'])
    ? basename($pengaturan['logo'])
    : 'logo-toko-buah-cc.png';

// Ambil transaksi
$st = $db->prepare(
    'SELECT t.*, u.nama AS kasir
     FROM transaksi t
     JOIN `user` u ON u.id_user = t.id_user
     WHERE t.id_transaksi = ?'
);
$st->execute([$id]);
$trx = $st->fetch();

if (!$trx) {
    set_flash('error', 'Transaksi tidak ditemukan.');
    redirect('views/transaksi.php');
}

// Ambil detail transaksi
$st = $db->prepare(
    'SELECT p.nama_produk, p.satuan, d.harga_satuan,
            d.qty, d.subtotal
     FROM transaksi_detail d
     JOIN produk p ON p.id_produk = d.id_produk
     WHERE d.id_transaksi = ?
     ORDER BY d.id_detail'
);
$st->execute([$id]);
$detail = $st->fetchAll();

$pageTitle = 'Struk Transaksi';
$active = 'transaksi';

include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>

<div class="view">
    <div class="card struk">

        <div class="struk-header">

            <img
                src="<?= BASE_URL ?>assets/images/<?= e($logoToko) ?>"
                alt="Logo Toko"
            >

            <h2><?= e($pengaturan['nama_toko']) ?></h2>

            <?php if (!empty($pengaturan['alamat'])): ?>
                <p><?= nl2br(e($pengaturan['alamat'])) ?></p>
            <?php endif; ?>

            <?php if (!empty($pengaturan['telepon'])): ?>
                <p>Telp: <?= e($pengaturan['telepon']) ?></p>
            <?php endif; ?>

            <p>Buah Segar, Hidup Lebih Sehat</p>

            <div class="garis"></div>

            <h3>STRUK PEMBELIAN</h3>
        </div>

        <div class="struk-info">
            <p>No: <?= e($trx['kode_transaksi']) ?></p>
            <p>Tanggal: <?= e(date('d/m/Y H:i', strtotime($trx['tanggal']))) ?></p>
            <p>Kasir: <?= e($trx['kasir']) ?></p>
        </div>

        <div class="garis"></div>

        <?php foreach ($detail as $d): ?>
            <div class="ln">
                <span>
                    <?= e($d['nama_produk']) ?><br>
                    <small>
                        <?= e(format_qty($d['qty'])) ?>
                        <?= e($d['satuan']) ?> ×
                        <?= e(rupiah($d['harga_satuan'])) ?>
                    </small>
                </span>

                <span><?= e(rupiah($d['subtotal'])) ?></span>
            </div>
        <?php endforeach; ?>

        <div class="garis"></div>

        <div class="ln b">
            <span>Total</span>
            <span><?= e(rupiah($trx['total'])) ?></span>
        </div>

        <div class="ln">
            <span>Metode Bayar</span>
            <span><?= e(ucfirst($trx['metode_bayar'])) ?></span>
        </div>

        <div class="ln">
            <span>Bayar</span>
            <span><?= e(rupiah($trx['bayar'])) ?></span>
        </div>

        <div class="ln b">
            <span>Kembalian</span>
            <span><?= e(rupiah($trx['kembalian'])) ?></span>
        </div>

        <div class="garis"></div>

        <div class="struk-footer">
            <b>Terima Kasih</b>
            <p>Selamat berbelanja kembali!</p>
        </div>

        <div class="btnrow noprint">
            <button type="button" class="btn o" onclick="window.print()">
                Cetak Struk
            </button>

            <a class="btn" href="<?= BASE_URL ?>views/kasir.php">
                Transaksi Baru
            </a>

            <a class="btn o" href="<?= BASE_URL ?>views/transaksi.php">
                Riwayat
            </a>
        </div>

    </div>
</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
