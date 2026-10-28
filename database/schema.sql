-- =====================================================
-- CC KASIR - Struktur database (MySQL / MariaDB XAMPP)
-- Nama database: cc_kasir
-- Aman diimpor berulang: tabel lama dihapus lalu dibuat ulang.
-- =====================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS cc_kasir CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE cc_kasir;

DROP TABLE IF EXISTS stok_log;
DROP TABLE IF EXISTS transaksi_detail;
DROP TABLE IF EXISTS transaksi;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  id_user INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('pemilik','kasir') NOT NULL DEFAULT 'kasir',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE produk (
  id_produk INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nama_produk VARCHAR(100) NOT NULL,
  kategori VARCHAR(50) NOT NULL,
  harga INT NOT NULL DEFAULT 0,
  satuan VARCHAR(20) NOT NULL DEFAULT 'kg',
  stok DECIMAL(10,2) NOT NULL DEFAULT 0,
  stok_minimum DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE transaksi (
  id_transaksi INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  kode_transaksi VARCHAR(20) NOT NULL UNIQUE,
  tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_user INT NOT NULL,
  total INT NOT NULL DEFAULT 0,
  bayar INT NOT NULL DEFAULT 0,
  kembalian INT NOT NULL DEFAULT 0,
  metode_bayar ENUM('tunai','transfer') NOT NULL DEFAULT 'tunai',
  CONSTRAINT fk_transaksi_user FOREIGN KEY (id_user) REFERENCES `user`(id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE transaksi_detail (
  id_detail INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_transaksi INT NOT NULL,
  id_produk INT NOT NULL,
  harga_satuan INT NOT NULL,
  qty DECIMAL(10,2) NOT NULL,
  subtotal INT NOT NULL,
  CONSTRAINT fk_detail_transaksi FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
  CONSTRAINT fk_detail_produk FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stok_log (
  id_log INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_produk INT NOT NULL,
  id_user INT NOT NULL,
  jenis ENUM('masuk','keluar','penyesuaian') NOT NULL,
  jumlah DECIMAL(10,2) NOT NULL,
  keterangan VARCHAR(150) NULL,
  tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_produk FOREIGN KEY (id_produk) REFERENCES produk(id_produk),
  CONSTRAINT fk_log_user FOREIGN KEY (id_user) REFERENCES `user`(id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
