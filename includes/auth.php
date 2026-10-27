<?php
require_once __DIR__ . '/../config/config.php';

function require_login(): void
{
    if (empty($_SESSION['user'])) {
        header('Location: ' . BASE_URL . 'views/login.php');
        exit;
    }
}

function is_pemilik(): bool
{
    return ($_SESSION['user']['role'] ?? '') === 'pemilik';
}
