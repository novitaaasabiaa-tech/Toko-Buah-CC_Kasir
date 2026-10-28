<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$filter = $_GET['status'] ?? 'semua';
$kondisi = '';
if ($filter === 'aman') {
    $kondisi = 'AND stok > stok_minimum';
} elseif ($filter === 'menipis') {
    $kondisi = 'AND stok > 0 AND stok <= stok_minimum';
} elseif ($filter === 'habis') {
    $kondisi = 'AND stok <= 0';
} else {
    $filter = 'semua';
}
$daftar = db()->query("SELECT id_produk, nama_produk, stok, stok_minimum, satuan FROM produk WHERE status = 'aktif' $kondisi ORDER BY nama_produk")->fetchAll();
$semuaProduk = db()->query("SELECT id_produk, nama_produk, satuan FROM produk WHERE status = 'aktif' ORDER BY nama_produk")->fetchAll();

$pageTitle = 'Stok';
$active = 'stok';
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">
    <div class="tools">
        <?php foreach (['semua' => 'Semua', 'aman' => 'Aman', 'menipis' => 'Menipis', 'habis' => 'Habis'] as $k => $label): ?>
            <a class="pill <?= $filter === $k ? 'on' : '' ?>" href="?status=<?= $k ?>"><?= $label ?></a>
        <?php endforeach; ?>
        <a class="btn o right" href="<?= BASE_URL ?>views/stok_riwayat.php">Riwayat Stok</a>
    </div>
    <div class="split">
        <div class="card tw">
            <table>
                <tr><th>Produk</th><th>Stok</th><th>Minimum</th><th>Status</th></tr>
                <?php foreach ($daftar as $d): ?>
                    <?php [$label, $kelas] = status_stok($d['stok'], $d['stok_minimum']); ?>
                    <tr>
                        <td><b><?= e($d['nama_produk']) ?></b></td>
                        <td><?= e(format_qty($d['stok'])) ?> <?= e($d['satuan']) ?></td>
                        <td><?= e(format_qty($d['stok_minimum'])) ?></td>
                        <td><span class="bd <?= $kelas ?>"><?= $label ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$daftar): ?><tr><td colspan="4" class="muted">Tidak ada produk pada filter ini.</td></tr><?php endif; ?>
            </table>
        </div>
        <?php if (is_pemilik()): ?>
            <form class="card form" method="post" action="<?= BASE_URL ?>actions/stok_simpan.php">
                <?= csrf_field() ?>
                <h3>Catat Perubahan Stok</h3>
                <label class="lbl">Produk
                    <select name="id_produk" required>
                        <?php foreach ($semuaProduk as $sp): ?>
                            <option value="<?= (int) $sp['id_produk'] ?>"><?= e($sp['nama_produk']) ?> (<?= e($sp['satuan']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="lbl">Jenis
                    <select name="jenis">
                        <option value="masuk">Stok masuk (tambah)</option>
                        <option value="penyesuaian">Penyesuaian (boleh minus)</option>
                    </select>
                </label>
                <label class="lbl">Jumlah<input type="number" name="jumlah" step="0.1" required></label>
                <label class="lbl">Keterangan<input name="keterangan" maxlength="150" placeholder="Contoh: kiriman pemasok"></label>
                <button class="btn w" type="submit">Simpan</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
