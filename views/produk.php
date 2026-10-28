<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$q = trim($_GET['q'] ?? '');
$sql = 'SELECT id_produk, nama_produk, kategori, harga, satuan, stok, stok_minimum, status FROM produk';
$param = [];
if ($q !== '') {
    $sql .= ' WHERE nama_produk LIKE ?';
    $param[] = '%' . $q . '%';
}
$sql .= ' ORDER BY status, nama_produk';
$st = db()->prepare($sql);
$st->execute($param);
$daftar = $st->fetchAll();

$pageTitle = 'Produk';
$active = 'produk';
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">
    <div class="tools">
        <form method="get" class="inline">
            <input name="q" value="<?= e($q) ?>" placeholder="🔍 Cari produk...">
            <button class="btn" type="submit">Cari</button>
        </form>
        <?php if (is_pemilik()): ?>
            <a class="btn right" href="<?= BASE_URL ?>views/produk_form.php">+ Tambah Produk</a>
        <?php endif; ?>
    </div>
    <div class="card tw">
        <table>
            <tr><th>No</th><th>Produk</th><th>Kategori</th><th>Harga</th><th>Satuan</th><th>Stok</th><th>Status</th><?php if (is_pemilik()): ?><th>Aksi</th><?php endif; ?></tr>
            <?php foreach ($daftar as $i => $p): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td class="flex"><img class="thumb" src="<?= e(gambar_produk($p['nama_produk'])) ?>" alt=""><b><?= e($p['nama_produk']) ?></b></td>
                    <td><?= e($p['kategori']) ?></td>
                    <td><?= e(rupiah($p['harga'])) ?></td>
                    <td><?= e($p['satuan']) ?></td>
                    <td><?= e(format_qty($p['stok'])) ?></td>
                    <td><span class="bd <?= $p['status'] === 'aktif' ? 'ok' : 'no' ?>"><?= e($p['status']) ?></span></td>
                    <?php if (is_pemilik()): ?>
                        <td class="flex">
                            <a class="btn o s" href="<?= BASE_URL ?>views/produk_form.php?id=<?= (int) $p['id_produk'] ?>">Edit</a>
                            <form method="post" action="<?= BASE_URL ?>actions/produk_status.php" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id_produk" value="<?= (int) $p['id_produk'] ?>">
                                <button class="btn o s" type="submit"><?= $p['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (!$daftar): ?><tr><td colspan="8" class="muted">Produk tidak ditemukan.</td></tr><?php endif; ?>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
