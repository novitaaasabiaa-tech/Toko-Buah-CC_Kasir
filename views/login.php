<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'views/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    try {
        $st = db()->prepare('SELECT id_user, nama, username, password, role FROM `user` WHERE username = ? LIMIT 1');
        $st->execute([$username]);
        $u = $st->fetch();
        if ($u && password_verify($password, $u['password'])) {
            unset($u['password']);
            $_SESSION['user'] = $u;
            header('Location: ' . BASE_URL . 'views/dashboard.php');
            exit;
        }
        $error = 'Username atau password salah.';
    } catch (PDOException $ex) {
        $error = pesan_db('Koneksi database gagal. Pastikan MySQL di XAMPP menyala dan database cc_kasir sudah diimport.', $ex);
    }
}

$pageTitle = 'Login';
include ROOT_PATH . '/includes/header.php';
?>
<div class="login">
    <div class="lf">
        <img src="<?= BASE_URL ?>assets/images/logo-toko-buah-cc.png" alt="Logo Toko Buah CC">
        <h2><?= APP_NAME ?></h2>
        <p>Segar, Teratur, Siap Melayani</p>
    </div>
    <div class="lr">
        <form method="post" action="">
            <h1>Masuk ke Akun</h1>
            <p class="muted">Gunakan akun yang diberikan pemilik toko</p>
            <?php if ($error !== ''): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <label>Username<input name="username" placeholder="Masukkan username" required autofocus></label>
            <label>Password<input name="password" type="password" placeholder="••••••••" required></label>
            <button class="btn w" type="submit">Masuk</button>
        </form>
    </div>
</div>
<script src="<?= BASE_URL ?>assets/js/app.js"></script>
</body>
</html>
