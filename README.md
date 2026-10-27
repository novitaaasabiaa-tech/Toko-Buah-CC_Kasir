# CC KASIR — Aplikasi Kasir & Pengelolaan Stok Toko Buah CC

Proyek mata kuliah **Project Mandiri** (Politeknik Dewantara, Teknologi Rekayasa Multimedia).

## Mitra
**Toko Buah CC** — usaha penjualan buah di Palopo (pemilik: Bertun Masero).

## Tech Stack
- PHP 8 (native, PDO) · MySQL/MariaDB · HTML5 · CSS3 · JavaScript
- Server lokal: XAMPP (Apache + MySQL) · Editor: Visual Studio Code · Git & GitHub
- Desain: Figma / Canva

## Struktur Folder
```
cc-kasir/
├── index.php            # pintu masuk (arahkan ke login/dashboard)
├── assets/              # css/, js/, images/ (logo & foto produk), icons/, fonts/
├── config/              # config.php (konstanta), database.php (koneksi PDO)
├── database/            # schema.sql (struktur tabel), seed.sql (data awal)
├── includes/            # header.php, navbar.php, footer.php, auth.php, functions.php
├── views/               # halaman: login, dashboard, kasir, produk, stok, transaksi, laporan
├── actions/             # proses tanpa tampilan (logout.php)
└── docs/                # dokumentasi (sitemap, user flow, blueprint)
```

## Cara Menjalankan di Lokal (XAMPP)
1. Pasang **XAMPP**, lalu buka XAMPP Control Panel dan **Start** modul **Apache** dan **MySQL**.
2. Salin folder `cc-kasir` ke `C:\xampp\htdocs\` sehingga menjadi `C:\xampp\htdocs\cc-kasir\`.
3. Buka `http://localhost/phpmyadmin` → tab **Import** → pilih `database/schema.sql` → **Go**.
4. Import lagi file `database/seed.sql` (data contoh dan akun demo).
5. Jika nama folder bukan `cc-kasir`, ubah `BASE_URL` di `config/config.php`.
6. Buka `http://localhost/cc-kasir/` di browser.

## Akun Demo
| Peran | Username | Password |
|---|---|---|
| Pemilik | `pemilik` | `admin123` |
| Kasir | `kasir` | `kasir123` |

> Akun dan data hanya contoh untuk pengembangan. Ganti sebelum dipakai di toko.

## Status Pengembangan
- [x] Struktur folder, koneksi database, skema `.sql`
- [x] Login, logout, dashboard, daftar produk
- [ ] Kasir (keranjang & simpan transaksi), Stok, Transaksi, Laporan
