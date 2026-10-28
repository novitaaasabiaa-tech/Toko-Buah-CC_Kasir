<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$dari = valid_date($_GET['dari'] ?? '');
$sampai = valid_date($_GET['sampai'] ?? '');
$q = trim($_GET['q'] ?? '');
if (!is_pemilik()) {
    // Kasir hanya melihat transaksi hari ini.
    $dari = $sampai = date('Y-m-d');
}

$sql = 'SELECT t.id_transaksi, t.kode_transaksi, t.tanggal, t.total, t.metode_bayar, u.nama AS kasir FROM transaksi t JOIN `user` u ON u.id_user = t.id_user WHERE 1 = 1';
$param = [];
if ($dari !== '') {
    $sql .= ' AND DATE(t.tanggal) >= ?';
    $param[] = $dari;
}
if ($sampai !== '') {
    $sql .= ' AND DATE(t.tanggal) <= ?';
    $param[] = $sampai;
}
if ($q !== '') {
    $sql .= ' AND t.kode_transaksi LIKE ?';
    $param[] = '%' . $q . '%';
}
$sql .= ' ORDER BY t.tanggal DESC, t.id_transaksi DESC LIMIT 200';
$st = db()->prepare($sql);
$st->execute($param);
$daftar = $st->fetchAll();
$jumlah = 0;
foreach ($daftar as $d) {
    $jumlah += (int) $d['total'];
}

$pageTitle = 'Riwayat Transaksi';
$active = 'transaksi';
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">
    <?php if (is_pemilik()): ?>
        <form method="get" class="tools">
            <input type="date" name="dari" value="<?= e($dari) ?>">
            <input type="date" name="sampai" value="<?= e($sampai) ?>">
            <input name="q" value="<?= e($q) ?>" placeholder="🔍 Cari kode transaksi">
            <button class="btn" type="submit">Filter</button>
            <a class="btn o" href="<?= BASE_URL ?>views/transaksi.php">Reset</a>
        </form>
    <?php else: ?>
        <p class="muted">Menampilkan transaksi hari ini.</p>
    <?php endif; ?>
    <div class="card tw">
        <table>
            <tr><th>Kode</th><th>Tanggal</th><th>Kasir</th><th>Total</th><th>Metode</th><th>Aksi</th></tr>
            <?php foreach ($daftar as $d): ?>
                <tr>
                    <td><b><?= e($d['kode_transaksi']) ?></b></td>
                    <td><?= e(date('d/m/Y H:i', strtotime($d['tanggal']))) ?></td>
                    <td><?= e($d['kasir']) ?></td>
                    <td><?= e(rupiah($d['total'])) ?></td>
                    <td><?= e(ucfirst($d['metode_bayar'])) ?></td>
                    <td><a class="btn o s" href="<?= BASE_URL ?>views/struk.php?id=<?= (int) $d['id_transaksi'] ?>">Detail</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$daftar): ?><tr><td colspan="6" class="muted">Tidak ada transaksi.</td></tr><?php endif; ?>
        </table>
    </div>
    <p class="muted"><?= count($daftar) ?> transaksi • total <?= e(rupiah($jumlah)) ?></p>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
