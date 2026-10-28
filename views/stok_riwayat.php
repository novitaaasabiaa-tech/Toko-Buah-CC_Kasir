<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$daftar = db()->query('SELECT l.tanggal, p.nama_produk, l.jenis, l.jumlah, l.keterangan, u.nama AS pengguna FROM stok_log l JOIN produk p ON p.id_produk = l.id_produk JOIN `user` u ON u.id_user = l.id_user ORDER BY l.tanggal DESC, l.id_log DESC LIMIT 100')->fetchAll();

$pageTitle = 'Riwayat Stok';
$active = 'stok';
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">
    <div class="tools"><a class="btn o" href="<?= BASE_URL ?>views/stok.php">← Kembali ke Stok</a></div>
    <div class="card tw">
        <table>
            <tr><th>Waktu</th><th>Produk</th><th>Jenis</th><th>Jumlah</th><th>Keterangan</th><th>Oleh</th></tr>
            <?php foreach ($daftar as $d): ?>
                <tr>
                    <td><?= e(date('d/m/Y H:i', strtotime($d['tanggal']))) ?></td>
                    <td><b><?= e($d['nama_produk']) ?></b></td>
                    <td><span class="bd <?= $d['jenis'] === 'keluar' ? 'lw' : 'ok' ?>"><?= e($d['jenis']) ?></span></td>
                    <td><?= e(format_qty($d['jumlah'])) ?></td>
                    <td><?= e($d['keterangan']) ?></td>
                    <td><?= e($d['pengguna']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$daftar): ?><tr><td colspan="6" class="muted">Belum ada riwayat stok.</td></tr><?php endif; ?>
        </table>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
