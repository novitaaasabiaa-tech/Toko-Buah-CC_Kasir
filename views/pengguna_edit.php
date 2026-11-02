
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_pemilik();

$id = (int) ($_GET['id'] ?? $_POST['id_user'] ?? 0);

$st = db()->prepare(
    'SELECT id_user, nama, username, role FROM user WHERE id_user = ?'
);
$st->execute([$id]);
$pengguna = $st->fetch();

if (!$pengguna) {
    exit('Data pengguna tidak ditemukan.');
}

$errors = [];
$nama = $pengguna['nama'];
$username = $pengguna['username'];
$role = $pengguna['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($nama === '') {
        $errors[] = 'Nama wajib diisi.';
    }

    if ($username === '') {
        $errors[] = 'Username wajib diisi.';
    }

    if (!in_array($role, ['pemilik', 'kasir'], true)) {
        $errors[] = 'Peran tidak valid.';
    }

    if ($password !== '' && strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }

    $cek = db()->prepare(
        'SELECT COUNT(*) FROM user
         WHERE username = ? AND id_user != ?'
    );
    $cek->execute([$username, $id]);

    if ($cek->fetchColumn() > 0) {
        $errors[] = 'Username sudah digunakan.';
    }

    if (!$errors) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $simpan = db()->prepare(
                'UPDATE user
                 SET nama = ?, username = ?, role = ?, password = ?
                 WHERE id_user = ?'
            );

            $simpan->execute([
                $nama, $username, $role, $hash, $id
            ]);
        } else {
            $simpan = db()->prepare(
                'UPDATE user
                 SET nama = ?, username = ?, role = ?
                 WHERE id_user = ?'
            );

            $simpan->execute([
                $nama, $username, $role, $id
            ]);
        }

        redirect('views/pengguna.php');
        exit;
    }
}

$pageTitle = 'Edit Pengguna';
$active = 'pengguna';

include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>

<div class="view">
    <div class="card" style="max-width:650px; padding:24px;">
        <h3>Edit Pengguna</h3>
        <p class="muted">Ubah informasi akun pengguna.</p>

        <?php if ($errors): ?>
            <div class="bd no" style="display:block; padding:12px;">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id_user" value="<?= $id ?>">

            <label class="lbl">
                Nama Lengkap
                <input type="text" name="nama"
                       value="<?= e($nama) ?>" required>
            </label>

            <br>

            <label class="lbl">
                Username
                <input type="text" name="username"
                       value="<?= e($username) ?>" required>
            </label>

            <br>

            <label class="lbl">
                Password Baru
                <input type="password" name="password"
                       placeholder="Kosongkan jika tidak diubah"
                       minlength="8">
            </label>

            <br>

            <label class="lbl">
                Peran
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
                    Simpan Perubahan
                </button>
                <a href="<?= BASE_URL ?>views/pengguna.php" class="btn o">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
