<?php
require_once __DIR__ . '/../config/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/functions.php';
require_login();

$pageTitle = 'Stok';
$active = 'stok';
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>
<div class="view">
    <div class="card">
        <h3>Halaman Stok</h3>
        <p class="muted">Kerangka halaman sudah terhubung ke header, sidebar, footer, CSS, dan JS. Fitur stok akan dibangun pada tahap pengembangan berikutnya.</p>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
