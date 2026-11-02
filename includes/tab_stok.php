<?php
// Tab: Stok Tersedia | Stok Masuk | Stok Keluar. Variabel: $tabAktif ('tersedia', 'masuk', 'keluar').
$tabStok = [
    'tersedia' => ['Stok Tersedia', 'views/stok.php'],
    'masuk'    => ['Stok Masuk', 'views/stok_riwayat.php?jenis=masuk'],
    'keluar'   => ['Stok Keluar', 'views/stok_riwayat.php?jenis=keluar'],
];
?>
<div class="tabs">
    <?php foreach ($tabStok as $kunci => [$label, $alamat]): ?>
        <a class="tab <?= ($tabAktif ?? '') === $kunci ? 'on' : '' ?>" href="<?= BASE_URL . $alamat ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>
