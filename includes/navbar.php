<?php
// Sidebar + topbar + pesan flash. Variabel yang dipakai: $active dan $pageTitle.
$active = $active ?? '';
$menu = [
    'dashboard' => ['Dashboard', '🏠'],
    'kasir'     => ['Kasir', '🛒'],
    'produk'    => ['Produk', '🍎'],
    'stok'      => ['Stok', '📦'],
    'transaksi' => ['Transaksi', '🧾'],
    'laporan'   => ['Laporan', '📊'],
];
$flash = get_flash();
?>
<div class="app">
    <aside>
        <div class="brand">
            <img src="<?= BASE_URL ?>assets/images/logo-toko-buah-cc.png" alt="Logo Toko Buah CC">
            <div><b><?= APP_NAME ?></b><span><?= MITRA_NAME ?></span></div>
        </div>
        <?php foreach ($menu as $key => [$label, $icon]): ?>
            <?php if ($key === 'laporan' && !is_pemilik()) { continue; } ?>
            <a class="nav <?= $active === $key ? 'on' : '' ?>" href="<?= BASE_URL ?>views/<?= $key ?>.php">
                <span><?= $icon ?></span><?= $label ?>
            </a>
        <?php endforeach; ?>
        <a class="nav out" href="<?= BASE_URL ?>actions/logout.php"><span>🚪</span>Logout</a>
    </aside>
    <main>
        <header class="topbar">
            <h1><?= e($pageTitle) ?></h1>
            <span class="chip-u"><?= e($_SESSION['user']['nama'] ?? '') ?> • <?= e(ucfirst($_SESSION['user']['role'] ?? '')) ?></span>
        </header>
        <?php if ($flash): ?>
            <div class="alert <?= e($flash['tipe']) ?> flash"><?= e($flash['pesan']) ?></div>
        <?php endif; ?>
