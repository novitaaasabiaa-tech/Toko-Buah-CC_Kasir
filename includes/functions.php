<?php
// Fungsi bantu yang dipakai di banyak halaman.

function rupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function e($teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

// Mengubah teks angka dari form (boleh memakai koma) menjadi float.
function angka_input($nilai): float
{
    return (float) str_replace(',', '.', trim((string) $nilai));
}

// Menampilkan jumlah tanpa nol berlebih: 2.00 -> 2, 1.50 -> 1,5
function format_qty($qty): string
{
    return rtrim(rtrim(number_format((float) $qty, 2, ',', '.'), '0'), ',');
}

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . ltrim($path, '/'));
    exit;
}

// Pesan sekali tampil (flash message) setelah proses form.
function set_flash(string $tipe, string $pesan): void
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

// Perlindungan CSRF untuk semua form POST.
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        set_flash('error', 'Sesi formulir tidak valid. Silakan ulangi.');
        redirect('views/dashboard.php');
    }
}

function valid_date(string $tanggal): string
{
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) ? $tanggal : '';
}

// Status stok: [label, kelas CSS]
function status_stok($stok, $minimum): array
{
    if ((float) $stok <= 0) {
        return ['Habis', 'no'];
    }
    if ((float) $stok <= (float) $minimum) {
        return ['Menipis', 'lw'];
    }
    return ['Aman', 'ok'];
}


// Pesan error untuk pengguna; bila APP_DEBUG aktif, ditambah detail dari database.
function pesan_db(string $umum, Throwable $ex): string
{
    return APP_DEBUG ? $umum . ' [' . $ex->getMessage() . ']' : $umum;
}

// ===== Foto produk =====
// Nama berkas foto = nama produk huruf kecil, spasi menjadi "-" (contoh: "Buah Naga" -> buah-naga.jpg)
function slug_produk(string $nama): string
{
    return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $nama), '-'));
}

// Mencari berkas foto produk (jpg, jpeg, png, webp). Mengembalikan nama berkas atau null.
function cari_foto(string $slug): ?string
{
    if ($slug === '') {
        return null;
    }
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        if (file_exists(ROOT_PATH . '/assets/images/produk/' . $slug . '.' . $ext)) {
            return $slug . '.' . $ext;
        }
    }
    return null;
}

// Alamat gambar produk; memakai logo bila foto produk belum ada.
function gambar_produk(string $nama): string
{
    $berkas = cari_foto(slug_produk($nama));
    if ($berkas !== null) {
        $alamat = ROOT_PATH . '/assets/images/produk/' . $berkas;
        return BASE_URL . 'assets/images/produk/' . $berkas . '?v=' . filemtime($alamat);
    }
    return BASE_URL . 'assets/images/logo-toko-buah-cc.png';
}

function hapus_foto(string $slug): void
{
    if ($slug === '') {
        return;
    }
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $alamat = ROOT_PATH . '/assets/images/produk/' . $slug . '.' . $ext;
        if (is_file($alamat)) {
            @unlink($alamat);
        }
    }
}

// Saat nama produk diubah, nama berkas fotonya ikut diganti.
function pindah_foto(string $slugLama, string $slugBaru): void
{
    if ($slugLama === '' || $slugBaru === '' || $slugLama === $slugBaru) {
        return;
    }
    $lama = cari_foto($slugLama);
    if ($lama === null || cari_foto($slugBaru) !== null) {
        return;
    }
    $folder = ROOT_PATH . '/assets/images/produk/';
    @rename($folder . $lama, $folder . $slugBaru . '.' . pathinfo($lama, PATHINFO_EXTENSION));
}

// Menyimpan foto unggahan. Mengembalikan pesan error, atau null bila berhasil / tidak ada foto baru.
function simpan_foto_produk(array $berkas, string $slug): ?string
{
    $kode = $berkas['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($kode === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($kode !== UPLOAD_ERR_OK) {
        return 'Foto gagal diunggah (kode ' . $kode . '). Coba foto yang lebih kecil.';
    }
    if ($berkas['size'] > 8 * 1024 * 1024) {
        return 'Ukuran foto maksimal 8 MB.';
    }
    $info = @getimagesize($berkas['tmp_name']);
    $jenis = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if ($info === false || !isset($jenis[$info[2]])) {
        return 'Format foto harus JPG, PNG, atau WEBP.';
    }
    $folder = ROOT_PATH . '/assets/images/produk/';
    if (!is_dir($folder) && !@mkdir($folder, 0775, true)) {
        return 'Folder foto produk tidak dapat dibuat.';
    }

    // Bila ekstensi GD tersedia: perkecil (lebar maksimal 800 px) lalu simpan sebagai JPG.
    $pemuat = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'][$info[2]];
    if (function_exists('imagecreatetruecolor') && function_exists('imagejpeg') && function_exists($pemuat)) {
        $asal = @$pemuat($berkas['tmp_name']);
        if ($asal !== false) {
            if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data') && function_exists('imagerotate')) {
                $exif = @exif_read_data($berkas['tmp_name']);
                $arah = is_array($exif) ? ($exif['Orientation'] ?? 1) : 1;
                $sudut = [3 => 180, 6 => -90, 8 => 90][$arah] ?? 0;
                if ($sudut !== 0) {
                    $putar = imagerotate($asal, $sudut, 0);
                    if ($putar !== false) {
                        imagedestroy($asal);
                        $asal = $putar;
                    }
                }
            }
            $lebar = imagesx($asal);
            $tinggi = imagesy($asal);
            $skala = $lebar > 800 ? 800 / $lebar : 1;
            $lebarBaru = max(1, (int) round($lebar * $skala));
            $tinggiBaru = max(1, (int) round($tinggi * $skala));
            $baru = imagecreatetruecolor($lebarBaru, $tinggiBaru);
            imagefill($baru, 0, 0, imagecolorallocate($baru, 255, 255, 255));
            imagecopyresampled($baru, $asal, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebar, $tinggi);
            hapus_foto($slug);
            $berhasil = imagejpeg($baru, $folder . $slug . '.jpg', 85);
            imagedestroy($asal);
            imagedestroy($baru);
            return $berhasil ? null : 'Foto gagal disimpan. Periksa izin folder assets/images/produk.';
        }
    }

    // Cadangan bila GD tidak tersedia: simpan berkas apa adanya.
    hapus_foto($slug);
    if (move_uploaded_file($berkas['tmp_name'], $folder . $slug . '.' . $jenis[$info[2]])) {
        return null;
    }
    return 'Foto gagal disimpan. Periksa izin folder assets/images/produk.';
}

// ===== Tampilan =====
// Ikon garis (SVG) untuk sidebar dan tombol.
function ikon(string $nama): string
{
    $jalur = [
        'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'cart' => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
        'package' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
        'archive' => '<polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/>',
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'chart' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'alert' => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'dollar' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'printer' => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',

'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-1.5 2.6-.2-.1a1.7 1.7 0 0 0-1.8.3l-.1.1h-3l-.1-.2a1.7 1.7 0 0 0-1.5-1l-.2.1-2.6-1.5.1-.2a1.7 1.7 0 0 0-.3-1.8l-.1-.1v-3l.2-.1a1.7 1.7 0 0 0 1-1.5l-.1-.2 1.5-2.6.2.1a1.7 1.7 0 0 0 1.8-.3l.1-.1h3l.1.2a1.7 1.7 0 0 0 1.5 1l.2-.1 2.6 1.5-.1.2a1.7 1.7 0 0 0 .3 1.8l.1.1z"/>',
    ];
    return '<svg class="ik" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($jalur[$nama] ?? '') . '</svg>';
}

function tanggal_indonesia(?int $waktu = null): string
{
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $waktu = $waktu ?? time();
    return $hari[(int) date('w', $waktu)] . ', ' . date('j', $waktu) . ' ' . $bulan[(int) date('n', $waktu)] . ' ' . date('Y', $waktu);
}

// Perubahan persen dibanding periode sebelumnya.
function perubahan_persen(float $sekarang, float $sebelum, string $pembanding): array
{
    if ($sebelum <= 0) {
        return [
            $sekarang > 0
                ? 'Naik dari nol ' . $pembanding
                : 'Belum ada data ' . $pembanding,
            'flat'
        ];
    }

    $persen = (int) round(($sekarang - $sebelum) / $sebelum * 100);

    if ($persen > 0) {
        return ['↑ ' . $persen . '% ' . $pembanding, 'up'];
    }

    if ($persen < 0) {
        return ['↓ ' . abs($persen) . '% ' . $pembanding, 'down'];
    }

    return ['0% ' . $pembanding, 'flat'];
}
// Langkah sumbu grafik yang "rapi" (1, 2, 2,5, 5 x 10^n) agar ada 3 selang: 0 .. langkah x 3.
function nice_langkah(float $nilai): float
{
    if ($nilai <= 0) {
        return 1000;
    }
    $perTiga = $nilai / 3;
    $p = pow(10, floor(log10($perTiga)));
    foreach ([1, 2, 2.5, 5, 10] as $k) {
        if ($k * $p >= $perTiga) {
            return $k * $p;
        }
    }
    return 10 * $p;
}
