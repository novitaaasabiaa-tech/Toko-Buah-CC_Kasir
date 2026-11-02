
<?php
// Sidebar + bar atas + judul halaman + pesan flash.
$active = $active ?? '';

$menu = [
    'dashboard' => ['Dashboard', 'home'],
    'kasir'     => ['Kasir', 'cart'],
    'produk'    => ['Produk', 'package'],
    'stok'      => ['Stok', 'archive'],
    'transaksi' => ['Transaksi', 'file'],
    'laporan'   => ['Laporan', 'chart'],
    'pengguna'  => ['Pengguna', 'user'],
];

$flash = get_flash();
$namaUser = $_SESSION['user']['nama'] ?? '';

// Ambil logo toko dari database
$logoToko = 'logo-toko-buah-cc.png';

try {
    $queryLogo = db()->query(
        "SELECT logo FROM pengaturan_toko WHERE id = 1 LIMIT 1"
    );

    $dataLogo = $queryLogo->fetch(PDO::FETCH_ASSOC);

    if (!empty($dataLogo['logo'])) {
        $logoToko = basename($dataLogo['logo']);
    }
} catch (Throwable $e) {
    // Gunakan logo bawaan jika data belum tersedia
}
?>

<div class="app">
    <aside>

        <div class="brand">
            <img
                src="<?= BASE_URL ?>assets/images/<?= e($logoToko) ?>"
                alt="Logo Toko Buah CC"
            >

            <div class="brand-text">
                <b>CC KASIR</b>
                <span>Toko Buah CC</span>
            </div>
        </div>

        <nav class="sidebar-menu">
            <?php foreach ($menu as $key => [$label, $ikon]): ?>
                <?php if ($key === 'laporan' && !is_pemilik()) continue; ?>
                <?php if ($key === 'pengguna' && !is_pemilik()) continue; ?>

                <a class="nav <?= $active === $key ? 'on' : '' ?>"
                   href="<?= BASE_URL ?>views/<?= $key ?>.php">
                    <?= ikon($ikon) ?>
                    <span><?= $label ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-bottom">
            <a class="nav" href="<?= BASE_URL ?>views/pengaturan.php">
                <?= ikon('settings') ?>
                <span>Pengaturan</span>
            </a>

            <a class="nav out" href="<?= BASE_URL ?>actions/logout.php">
                <?= ikon('logout') ?>
                <span>Keluar</span>
            </a>
        </div>
    </aside>

    <main>
        <header class="topbar">
            <span class="tb-item">
                <?= ikon('calendar') ?>
                <?= e(tanggal_indonesia()) ?>
            </span>

            <span class="tb-item" id="jam">
                <?= date('H:i') ?>
            </span>

            <span class="user">
                <span class="avatar">
                    <?= e(strtoupper(substr($namaUser, 0, 1))) ?>
                </span>
                <span>
                    <b><?= e($namaUser) ?></b>
                    <small>
                        <?= e(ucfirst($_SESSION['user']['role'] ?? '')) ?>
                    </small>
                </span>
            </span>
        </header>

        <div class="page-head">
            <div>
                <h1><?= e($pageTitle ?? '') ?></h1>

                <?php if (!empty($pageSub)): ?>
                    <p class="sub"><?= e($pageSub) ?></p>
                <?php endif; ?>
            </div>

            <?php if (!empty($pageActions)): ?>
                <div class="head-actions">
                    <?= $pageActions ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($flash): ?>
            <div class="alert <?= e($flash['tipe']) ?> flash">
                <?= e($flash['pesan']) ?>
            </div>
        <?php endif; ?>
