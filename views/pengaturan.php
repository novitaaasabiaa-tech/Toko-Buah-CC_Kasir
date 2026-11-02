
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$db = db();

$pageTitle = 'Pengaturan';
$pageSub = 'Kelola informasi toko dan akun CC KASIR.';
$active = 'pengguna';

$namaUser = $_SESSION['user']['nama'] ?? '';
$role = $_SESSION['user']['role'] ?? '';

/* Buat tabel pengaturan jika belum tersedia */
$db->exec("
    CREATE TABLE IF NOT EXISTS pengaturan_toko (
        id INT PRIMARY KEY,
        nama_toko VARCHAR(150) NOT NULL,
        alamat TEXT,
        telepon VARCHAR(30),
        logo VARCHAR(255) DEFAULT NULL
    )
");

/* Tambahkan kolom logo untuk tabel lama */
$kolomLogo = $db->query("
    SHOW COLUMNS FROM pengaturan_toko LIKE 'logo'
")->fetch();

if (!$kolomLogo) {
    $db->exec("
        ALTER TABLE pengaturan_toko
        ADD COLUMN logo VARCHAR(255) DEFAULT NULL
    ");
}

/* Ambil pengaturan toko */
$pengaturan = [
    'nama_toko' => 'Toko Buah CC',
    'alamat' => '',
    'telepon' => '',
    'logo' => ''
];

$data = $db->query("
    SELECT * FROM pengaturan_toko WHERE id = 1
")->fetch(PDO::FETCH_ASSOC);

if ($data) {
    $pengaturan = array_merge($pengaturan, $data);
}

/* Proses simpan pengaturan */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $namaToko = trim($_POST['nama_toko'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');

    $logo = $pengaturan['logo'];

    if ($namaToko === '') {
        set_flash('error', 'Nama toko wajib diisi.');
        redirect('views/pengaturan.php');
    }

    /* Proses upload logo jika ada */
    if (
        isset($_FILES['logo']) &&
        $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES['logo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            set_flash('error', 'Upload logo gagal.');
            redirect('views/pengaturan.php');
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            set_flash('error', 'Ukuran logo maksimal 2 MB.');
            redirect('views/pengaturan.php');
        }

        $info = getimagesize($file['tmp_name']);

        if (!$info) {
            set_flash('error', 'File yang diunggah bukan gambar.');
            redirect('views/pengaturan.php');
        }

        $jenis = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp'
        ];

        $mime = $info['mime'] ?? '';

        if (!isset($jenis[$mime])) {
            set_flash('error', 'Format logo harus PNG, JPG, atau WEBP.');
            redirect('views/pengaturan.php');
        }

        $folder = ROOT_PATH . '/assets/images/';

        if (!is_dir($folder)) {
            set_flash('error', 'Folder penyimpanan logo tidak ditemukan.');
            redirect('views/pengaturan.php');
        }

        $namaFile = 'logo-cc-' . bin2hex(random_bytes(8))
                  . '.' . $jenis[$mime];

        if (!move_uploaded_file(
            $file['tmp_name'],
            $folder . $namaFile
        )) {
            set_flash('error', 'Logo gagal disimpan.');
            redirect('views/pengaturan.php');
        }

        $logo = $namaFile;
    }

    try {
        $st = $db->prepare("
            INSERT INTO pengaturan_toko
            (id, nama_toko, alamat, telepon, logo)
            VALUES (1, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                nama_toko = VALUES(nama_toko),
                alamat = VALUES(alamat),
                telepon = VALUES(telepon),
                logo = VALUES(logo)
        ");

        $st->execute([
            $namaToko,
            $alamat,
            $telepon,
            $logo
        ]);

        set_flash('success', 'Pengaturan toko berhasil disimpan.');
        redirect('views/pengaturan.php');

    } catch (PDOException $e) {
        set_flash('error', 'Gagal menyimpan pengaturan toko.');
        redirect('views/pengaturan.php');
    }
}

include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>

<div class="view settings-page">

    <div class="card settings-card">

        <div class="settings-heading">
            <?= ikon('store') ?>
            <div>
                <h3>Informasi Toko</h3>
                <p>Informasi identitas Toko Buah CC.</p>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">

            <div class="settings-form">

                <div class="field">
                    <label>Nama Toko</label>
                    <input
                        type="text"
                        name="nama_toko"
                        value="<?= e($pengaturan['nama_toko']) ?>"
                        placeholder="Nama toko"
                        required
                    >
                </div>

                <div class="field">
                    <label>Alamat Toko</label>
                    <textarea
                        name="alamat"
                        rows="3"
                        placeholder="Masukkan alamat toko"
                    ><?= e($pengaturan['alamat']) ?></textarea>
                </div>

                <div class="field">
                    <label>Nomor Telepon</label>
                    <input
                        type="text"
                        name="telepon"
                        value="<?= e($pengaturan['telepon']) ?>"
                        placeholder="Nomor telepon toko"
                    >
                </div>

                <div class="field">
                    <label>Logo Toko</label>

                    <div class="logo-preview">
                        <?php if (!empty($pengaturan['logo'])): ?>
                            <img
                                src="<?= BASE_URL ?>assets/images/<?= e($pengaturan['logo']) ?>"
                                alt="Logo Toko"
                            >
                        <?php else: ?>
                            <img
                                src="<?= BASE_URL ?>assets/images/logo-toko-buah-cc.png"
                                alt="Logo Toko"
                            >
                        <?php endif; ?>
                    </div>

                    <input
                        type="file"
                        name="logo"
                        accept="image/png,image/jpeg,image/webp"
                    >

                    <small>Format PNG, JPG, WEBP. Maksimal 2 MB.</small>
                </div>

            </div>

            <div class="settings-actions">
                <button type="submit" class="btn">
                    <?= ikon('save') ?>
                    Simpan Pengaturan
                </button>
            </div>

        </form>

    </div>

    <div class="card settings-card">

        <div class="settings-heading">
            <?= ikon('user') ?>
            <div>
                <h3>Informasi Akun</h3>
                <p>Informasi pengguna yang sedang masuk.</p>
            </div>
        </div>

        <div class="account-info">
            <div>
                <small>Nama Pengguna</small>
                <b><?= e($namaUser) ?></b>
            </div>

            <div>
                <small>Hak Akses</small>
                <b><?= e(ucfirst($role)) ?></b>
            </div>
        </div>

    </div>

</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
