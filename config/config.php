<?php
// Konfigurasi umum aplikasi CC KASIR (untuk lokal XAMPP).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Makassar'); // WITA (Palopo)

define('APP_NAME', 'CC KASIR');
define('MITRA_NAME', 'Toko Buah CC');
define('ROOT_PATH', dirname(__DIR__));

// BASE_URL dihitung OTOMATIS dari lokasi folder proyek di htdocs,
// sehingga nama folder boleh apa saja (disarankan: cc_kasir).
$docRoot = rtrim(str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$rootDir = rtrim(str_replace('\\', '/', ROOT_PATH), '/');
if ($docRoot !== '' && stripos($rootDir, $docRoot) === 0) {
    define('BASE_URL', substr($rootDir, strlen($docRoot)) . '/');
} else {
    define('BASE_URL', '/cc_kasir/'); // cadangan bila deteksi otomatis gagal
}

// true = pesan error database ditampilkan rinci (membantu saat belajar).
// Ubah menjadi false bila aplikasi dipakai sungguhan.
define('APP_DEBUG', true);

// Koneksi database (default XAMPP: user root tanpa password)
define('DB_HOST', 'localhost');
define('DB_NAME', 'cc_kasir');
define('DB_USER', 'root');
define('DB_PASS', '');
