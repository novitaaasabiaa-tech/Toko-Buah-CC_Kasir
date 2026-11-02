
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if (!is_pemilik()) {
    http_response_code(403);
    exit('Akses ditolak.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('views/pengguna.php');
    exit;
}

csrf_check();

$id = (int) ($_POST['id_user'] ?? 0);
$idLogin = (int) ($_SESSION['user']['id_user'] ?? 0);

// Mencegah akun yang sedang login dihapus
if ($id <= 0 || $id === $idLogin) {
    redirect('views/pengguna.php');
    exit;
}

// Pastikan pengguna tersedia
$cek = db()->prepare(
    'SELECT id_user FROM user WHERE id_user = ?'
);
$cek->execute([$id]);

if (!$cek->fetch()) {
    redirect('views/pengguna.php');
    exit;
}

// Hapus pengguna
$hapus = db()->prepare(
    'DELETE FROM user WHERE id_user = ?'
);
$hapus->execute([$id]);

redirect('views/pengguna.php');
exit;
