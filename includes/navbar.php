<?php
// Sidebar + topbar. Variabel yang dipakai: $active (menu aktif) dan $pageTitle.
$active = $active ?? '';
$menu = [
    'dashboard' => ['Dashboard', '🏠'],
    'kasir'     => ['Kasir', '🛒'],
    'produk'    => ['Produk', '🍎'],
    'stok'      => ['Stok', '📦'],
    'transaksi' => ['Transaksi', '🧾'],
    'laporan'   => ['Laporan', '📊'],
];
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
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            <span class="chip-u"><?= htmlspecialchars($_SESSION['user']['nama'] ?? '') ?> • <?= ucfirst($_SESSION['user']['role'] ?? '') ?></span>
        </header>
