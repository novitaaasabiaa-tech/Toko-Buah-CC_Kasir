
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if (!is_pemilik()) {
    http_response_code(403);
    exit('Akses ditolak.');
}

$errors = [];
$nama = '';
$username = '';
$role = 'kasir';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';
    $role = $_POST['role'] ?? 'kasir';

    if ($nama === '') {
        $errors[] = 'Nama wajib diisi.';
    }

    if ($username === '') {
        $errors[] = 'Username wajib diisi.';
    }

    if (!in_array($role, ['pemilik', 'kasir'], true)) {
        $errors[] = 'Peran tidak valid.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }

    if ($password !== $konfirmasi) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }

    if (!$errors) {
        $cek = db()->prepare(
            'SELECT COUNT(*) FROM user WHERE username = ?'
        );
        $cek->execute([$username]);

        if ($cek->fetchColumn() > 0) {
            $errors[] = 'Username sudah digunakan.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $simpan = db()->prepare(
            'INSERT INTO user (nama, username, password, role)
             VALUES (?, ?, ?, ?)'
        );

        $simpan->execute([
            $nama,
            $username,
            $hash,
            $role
        ]);

        redirect('views/pengguna.php');
        exit;
    }
}

$pageTitle = 'Tambah Pengguna';
$active = 'pengguna';

include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>

<div class="view">
    <div class="card" style="max-width:650px; padding:24px;">
        <h3>Tambah Pengguna</h3>
        <p class="muted">Masukkan data akun pengguna baru.</p>

        <?php if ($errors): ?>
            <div class="bd no" style="display:block; padding:12px; margin-bottom:16px;">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>

            <label class="lbl">
                Nama Lengkap
                <input type="text" name="nama"
                       value="<?= e($nama) ?>"
                       placeholder="Masukkan nama lengkap"
                       required>
            </label>

            <br>

            <label class="lbl">
                Username
                <input type="text" name="username"
                       value="<?= e($username) ?>"
                       placeholder="Masukkan username"
                       required>
            </label>

            <br>

            <label class="lbl">
                Password
                <input type="password" name="password"
                       placeholder="Minimal 8 karakter"
                       required minlength="8">
            </label>

            <br>

            <label class="lbl">
                Konfirmasi Password
                <input type="password" name="konfirmasi"
                       placeholder="Ulangi password"
                       required minlength="8">
            </label>

            <br>

            <label class="lbl">
                Peran Pengguna
                <select name="role" class="pilih">
                    <option value="kasir" <?= $role === 'kasir' ? 'selected' : '' ?>>
                        Kasir
                    </option>
                    <option value="pemilik" <?= $role === 'pemilik' ? 'selected' : '' ?>>
                        Pemilik
                    </option>
                </select>
            </label>

            <br><br>

            <div class="flex">
                <button type="submit" class="btn">
                    Simpan Pengguna
                </button>

                <a href="<?= BASE_URL ?>views/pengguna.php" class="btn o">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
