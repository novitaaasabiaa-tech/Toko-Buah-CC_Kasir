# CC KASIR — Aplikasi Kasir & Pengelolaan Stok Toko Buah CC

Proyek mata kuliah **Project Mandiri** (Politeknik Dewantara, Teknologi Rekayasa Multimedia).

## Mitra
**Toko Buah CC** — usaha penjualan buah di Palopo (pemilik: Bertun Masero).

## Nama yang dipakai (semua konsisten)
| Bagian | Nama |
|---|---|
| Folder proyek di htdocs | `cc_kasir` |
| Database MySQL | `cc_kasir` |
| Alamat di browser | `http://localhost/cc_kasir/` |
| Repository GitHub | `cc_kasir` |

## Tech Stack
- PHP 8 (native, PDO) · MySQL/MariaDB · HTML5 · CSS3 · JavaScript
- Server lokal: XAMPP (Apache + MySQL) · Editor: Visual Studio Code · Git & GitHub

## Struktur Folder
```
cc_kasir/
├── index.php            # pintu masuk (arahkan ke login/dashboard)
├── assets/              # css/, js/, images/ (logo & foto produk), icons/, fonts/
├── config/              # config.php (pengaturan), database.php (koneksi PDO)
├── database/            # cc_kasir.sql (SATU FILE IMPOR), schema.sql, seed.sql, seed_transaksi.sql
├── includes/            # bootstrap, header, navbar, footer, auth, functions
├── views/               # login, dashboard, kasir, struk, produk, produk_form, stok, stok_riwayat, transaksi, laporan
├── actions/             # proses form: logout, transaksi_simpan, produk_simpan, produk_status, stok_simpan
└── docs/                # dokumentasi (sitemap, user flow, blueprint)
```

## Cara Menjalankan di Lokal (XAMPP)
1. Buka **XAMPP Control Panel**, klik **Start** pada **Apache** dan **MySQL**.
2. Salin folder `cc_kasir` ke `C:\xampp\htdocs\` sehingga menjadi `C:\xampp\htdocs\cc_kasir\`.
3. Buka `http://localhost/phpmyadmin`. Di halaman utama (tanpa memilih database), klik tab **Import**.
4. Pilih file **`database/cc_kasir.sql`** lalu klik **Import** (Go). Satu file ini sudah berisi struktur 5 tabel dan data awal.
5. Di panel kiri harus muncul database **cc_kasir** dengan tabel `user`, `produk`, `transaksi`, `transaksi_detail`, `stok_log`. Klik `produk` > Browse: ada 6 baris.
6. (Opsional) Import `database/seed_transaksi.sql` untuk data transaksi contoh agar dashboard dan laporan berisi grafik.
7. Buka **`http://localhost/cc_kasir/`** di browser.

> Alamat folder terdeteksi otomatis, jadi nama folder di htdocs boleh diganti. Bila diganti, alamat browser mengikuti nama folder.

## Akun Demo
| Peran | Username | Password |
|---|---|---|
| Pemilik | `pemilik` | `admin123` |
| Kasir | `kasir` | `kasir123` |

> Akun dan data hanya contoh untuk pengembangan. Ganti sebelum dipakai di toko.

## Jika Data Tidak Tersimpan
- Pesan error database ditampilkan rinci (mode `APP_DEBUG` di `config/config.php`). Baca pesan merah di bagian atas halaman.
- Pastikan `cc_kasir.sql` sudah diimpor tanpa pesan error merah di phpMyAdmin.
- Pastikan alamat yang dibuka sama dengan nama folder, misalnya `http://localhost/cc_kasir/`.

## Fitur
- Login (peran Pemilik & Kasir), logout, perlindungan CSRF
- Dashboard: omzet hari ini, jumlah transaksi, grafik 7 hari, stok menipis
- Produk: cari, tambah, ubah, aktif/nonaktif (Pemilik); Kasir hanya melihat
- Kasir: keranjang interaktif, total & kembalian otomatis, simpan transaksi (stok berkurang otomatis), struk cetak
- Stok: filter status, stok masuk/penyesuaian (Pemilik), riwayat stok
- Transaksi: riwayat + filter tanggal/kode (Kasir: hanya hari ini), detail/struk
- Laporan (Pemilik): harian/mingguan/bulanan/rentang tanggal, grafik, produk terlaris, cetak
