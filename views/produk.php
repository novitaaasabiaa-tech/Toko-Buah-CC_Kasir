<?php
require_once __DIR__ . '/../config/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/functions.php';
require_login();

$daftar = db()->query('SELECT nama_produk, kategori, harga, satuan, stok, status FROM produk ORDER BY nama_produk')->fetchAll();

$pageTitle = 'Produk';
$active = 'produk';
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">

    <div class="card tw">
        <table>
            <tr><th>No</th><th>Produk</th><th>Kategori</th><th>Harga</th><th>Satuan</th><th>Stok</th><th>Status</th></tr>
            <?php foreach ($daftar as $i => $p): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td style="display:flex;gap:10px;align-items:center">
                    <img class="thumb" src="<?= BASE_URL ?>assets/images/produk/<?= strtolower(e($p['nama_produk'])) ?>.jpg" alt="" onerror="this.style.display='none'">
                    <b><?= e($p['nama_produk']) ?></b>
                </td>
                <td><?= e($p['kategori']) ?></td>
                <td><?= rupiah($p['harga']) ?></td>
                <td><?= e($p['satuan']) ?></td>
                <td><?= e($p['stok']) ?></td>
                <td><?= e($p['status']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
