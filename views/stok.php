<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$q = trim($_GET['q'] ?? '');
$kat = trim($_GET['kategori'] ?? '');
$filter = $_GET['status'] ?? 'semua';
$sql = "SELECT id_produk, nama_produk, kategori, stok, stok_minimum, satuan FROM produk WHERE status = 'aktif'";
$param = [];
if ($q !== '') {
    $sql .= ' AND nama_produk LIKE ?';
    $param[] = '%' . $q . '%';
}
if ($kat !== '') {
    $sql .= ' AND kategori = ?';
    $param[] = $kat;
}
if ($filter === 'aman') {
    $sql .= ' AND stok > stok_minimum';
} elseif ($filter === 'menipis') {
    $sql .= ' AND stok > 0 AND stok <= stok_minimum';
} elseif ($filter === 'habis') {
    $sql .= ' AND stok <= 0';
} else {
    $filter = 'semua';
}
$sql .= ' ORDER BY nama_produk';
$st = db()->prepare($sql);
$st->execute($param);
$daftar = $st->fetchAll();
$semuaKategori = db()->query("SELECT DISTINCT kategori FROM produk WHERE status = 'aktif' ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);
$semuaProduk = db()->query("SELECT id_produk, nama_produk, satuan FROM produk WHERE status = 'aktif' ORDER BY nama_produk")->fetchAll();

$pageTitle = 'Data Stok';
$active = 'stok';
$tabAktif = 'tersedia';
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">
    <?php include ROOT_PATH . '/includes/tab_stok.php'; ?>
    <div class="split">
        <div class="stack">
            <form method="get" class="tools">
                <label class="searchbar grow"><?= ikon('search') ?><input name="q" value="<?= e($q) ?>" placeholder="Cari produk..."></label>
                <select name="kategori" class="pilih" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($semuaKategori as $k): ?>
                        <option value="<?= e($k) ?>" <?= $kat === $k ? 'selected' : '' ?>><?= e($k) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="status" class="pilih" onchange="this.form.submit()">
                    <?php foreach (['semua' => 'Semua Status', 'aman' => 'Aman', 'menipis' => 'Menipis', 'habis' => 'Habis'] as $k => $label): ?>
                        <option value="<?= $k ?>" <?= $filter === $k ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <div class="card tw">
                <table>
                    <tr><th>No</th><th>Nama Produk</th><th>Kategori</th><th>Stok</th><th>Satuan</th><th>Status</th></tr>
                    <?php foreach ($daftar as $i => $d): ?>
                        <?php [$label, $kelas] = status_stok($d['stok'], $d['stok_minimum']); ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="flex"><img class="thumb" src="<?= e(gambar_produk($d['nama_produk'])) ?>" alt=""><b><?= e($d['nama_produk']) ?></b></td>
                            <td><?= e($d['kategori']) ?></td>
                            <td><?= e(format_qty($d['stok'])) ?></td>
                            <td><?= e($d['satuan']) ?></td>
                            <td><span class="bd <?= $kelas ?>"><?= $label ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$daftar): ?><tr><td colspan="6" class="muted">Tidak ada produk pada filter ini.</td></tr><?php endif; ?>
                </table>
            </div>
            <div class="legend-stok">
                <span><i class="dot ok"></i>Aman (di atas stok minimum)</span>
                <span><i class="dot lw"></i>Menipis (sama dengan atau di bawah minimum)</span>
                <span><i class="dot no"></i>Habis (0)</span>
            </div>
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
                <label class="lbl">Jumlah<input type="number" name="jumlah" step="0.01" required></label>
                <label class="lbl">Keterangan<input name="keterangan" maxlength="150" placeholder="Contoh: kiriman pemasok"></label>
                <button class="btn w" type="submit">Simpan</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
