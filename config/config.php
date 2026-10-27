<?php
// Konfigurasi umum aplikasi CC KASIR (untuk lokal XAMPP).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'CC KASIR');
define('MITRA_NAME', 'Toko Buah CC');

// Sesuaikan dengan nama folder proyek di C:\xampp\htdocs\
define('BASE_URL', '/cc-kasir/');
define('ROOT_PATH', dirname(__DIR__));

// Koneksi database (default XAMPP: user root tanpa password)
define('DB_HOST', 'localhost');
define('DB_NAME', 'cc_kasir');
define('DB_USER', 'root');
define('DB_PASS', '');
