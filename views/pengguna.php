
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

// Halaman ini khusus pemilik toko
if (!is_pemilik()) {
    http_response_code(403);
    exit('Akses ditolak. Halaman ini hanya untuk pemilik toko.');
}

$q = trim($_GET['q'] ?? '');
$role = trim($_GET['role'] ?? '');

$sql = "SELECT id_user, nama, username, role, created_at
        FROM user
        WHERE 1 = 1";

$param = [];

if ($q !== '') {
    $sql .= " AND (nama LIKE ? OR username LIKE ?)";
    $param[] = '%' . $q . '%';
    $param[] = '%' . $q . '%';
}

if (in_array($role, ['pemilik', 'kasir'], true)) {
    $sql .= " AND role = ?";
    $param[] = $role;
}

$sql .= " ORDER BY role, nama";

$st = db()->prepare($sql);
$st->execute($param);
$daftar = $st->fetchAll();

$pageTitle = 'Data Pengguna';
$active = 'pengguna';

$pageActions = '<a class="btn" href="' . BASE_URL . 'views/pengguna_form.php">' . ikon('plus') . ' Tambah Pengguna</a>';

include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>

<div class="view">

    <form method="get" class="tools">
        <label class="searchbar grow">
            <?= ikon('search') ?>
            <input
                type="search"
                name="q"
                value="<?= e($q) ?>"
                placeholder="Cari nama atau username..."
            >
        </label>

        <select name="role" class="pilih"
                onchange="this.form.submit()">
            <option value="">Semua Peran</option>
            <option value="pemilik" <?= $role === 'pemilik' ? 'selected' : '' ?>>
                Pemilik
            </option>
            <option value="kasir" <?= $role === 'kasir' ? 'selected' : '' ?>>
                Kasir
            </option>
        </select>
    </form>

    <div class="card tw">
        <table>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Peran</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
            </tr>

            <?php foreach ($daftar as $i => $u): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><b><?= e($u['nama']) ?></b></td>
                    <td><?= e($u['username']) ?></td>
                    <td>
                        <span class="bd <?= $u['role'] === 'pemilik' ? 'ok' : '' ?>">
                            <?= e(ucfirst($u['role'])) ?>
                        </span>
                    </td>
                    <td>
                        <td class="flex">
    <a class="btn s"
       href="<?= BASE_URL ?>views/pengguna_edit.php?id=<?= (int) $u['id_user'] ?>">
        Edit
    </a>

    <?php if ((int) $u['id_user'] !== (int) $_SESSION['user']['id_user']): ?>
        <form method="post"
              action="<?= BASE_URL ?>actions/pengguna_hapus.php"
              class="inline"
              onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">

            <?= csrf_field() ?>

            <input type="hidden" name="id_user"
                   value="<?= (int) $u['id_user'] ?>">

            <button type="submit" class="btn s danger">
                Hapus
            </button>
        </form>
    <?php endif; ?>
</td>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$daftar): ?>
                <tr>
                    <td colspan="6" class="muted">
                        Pengguna tidak ditemukan.
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <p class="muted">
        Menampilkan <?= count($daftar) ?> pengguna
    </p>

</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
