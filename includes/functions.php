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

// Alamat gambar produk; memakai logo bila foto produk belum ada.
function gambar_produk(string $nama): string
{
    $berkas = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $nama), '-'));
    $path = 'assets/images/produk/' . $berkas . '.jpg';
    if (file_exists(ROOT_PATH . '/' . $path)) {
        return BASE_URL . $path;
    }
    return BASE_URL . 'assets/images/logo-toko-buah-cc.png';
}

// Pesan error untuk pengguna; bila APP_DEBUG aktif, ditambah detail dari database.
function pesan_db(string $umum, Throwable $ex): string
{
    return APP_DEBUG ? $umum . ' [' . $ex->getMessage() . ']' : $umum;
}
