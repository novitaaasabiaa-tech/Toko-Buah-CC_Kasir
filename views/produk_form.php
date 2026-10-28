<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_pemilik();

$id = (int) ($_GET['id'] ?? 0);
$p = ['id_produk' => 0, 'nama_produk' => '', 'kategori' => 'Buah Segar', 'harga' => '', 'satuan' => 'kg', 'stok' => 0, 'stok_minimum' => 0, 'status' => 'aktif'];
if ($id > 0) {
    $st = db()->prepare('SELECT * FROM produk WHERE id_produk = ?');
    $st->execute([$id]);
    $baris = $st->fetch();
    if (!$baris) {
        set_flash('error', 'Produk tidak ditemukan.');
        redirect('views/produk.php');
    }
    $p = $baris;
}

$pageTitle = $id > 0 ? 'Edit Produk' : 'Tambah Produk';
$active = 'produk';
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">
    <form class="card form" method="post" action="<?= BASE_URL ?>actions/produk_simpan.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id_produk" value="<?= (int) $p['id_produk'] ?>">
        <label class="lbl">Nama produk<input name="nama_produk" value="<?= e($p['nama_produk']) ?>" required></label>
        <label class="lbl">Kategori
            <input name="kategori" list="daftar-kategori" value="<?= e($p['kategori']) ?>" required>
            <datalist id="daftar-kategori"><option value="Buah Segar"><option value="Buah Impor"><option value="Paket"></datalist>
        </label>
        <div class="two">
            <label class="lbl">Harga per satuan (Rp)<input type="number" name="harga" min="1" step="100" value="<?= e($p['harga']) ?>" required></label>
            <label class="lbl">Satuan (kg / buah / sisir / pack)<input name="satuan" value="<?= e($p['satuan']) ?>" required></label>
        </div>
        <div class="two">
            <?php if ($id === 0): ?>
                <label class="lbl">Stok awal<input type="number" name="stok" min="0" step="0.1" value="<?= e($p['stok']) ?>"></label>
            <?php else: ?>
                <div class="lbl">Stok saat ini<div class="static"><?= e(format_qty($p['stok'])) ?> (ubah lewat menu Stok)</div></div>
            <?php endif; ?>
            <label class="lbl">Stok minimum (batas menipis)<input type="number" name="stok_minimum" min="0" step="0.1" value="<?= e($p['stok_minimum']) ?>"></label>
        </div>
        <label class="lbl">Status
            <select name="status">
                <option value="aktif" <?= $p['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="nonaktif" <?= $p['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </label>
        <div class="btnrow">
            <button class="btn" type="submit">Simpan</button>
            <a class="btn o" href="<?= BASE_URL ?>views/produk.php">Batal</a>
        </div>
    </form>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
