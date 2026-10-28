<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

function require_login(): void
{
    if (empty($_SESSION['user'])) {
        redirect('views/login.php');
    }
}

function is_pemilik(): bool
{
    return ($_SESSION['user']['role'] ?? '') === 'pemilik';
}

// Untuk halaman/aksi yang hanya boleh diakses pemilik.
function require_pemilik(): void
{
    require_login();
    if (!is_pemilik()) {
        set_flash('error', 'Halaman ini khusus pemilik toko.');
        redirect('views/dashboard.php');
    }
}
