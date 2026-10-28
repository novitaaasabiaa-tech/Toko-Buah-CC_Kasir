-- =====================================================
-- CC KASIR - SATU FILE IMPOR: struktur + data awal (MySQL / MariaDB XAMPP)
-- Nama database: cc_kasir
-- Impor file ini SAJA di phpMyAdmin (tab Import). Aman diulang: tabel lama dihapus lalu dibuat ulang.
-- Akun demo: pemilik / admin123  dan  kasir / kasir123
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

-- ----- DATA AWAL -----
INSERT INTO `user` (nama, username, password, role) VALUES
('Pemilik Toko', 'pemilik', '$2y$10$hA2LfSqt.yrGEqlZgOfTkuOf.AI/DWZWlvVeabkdgmhAuSRLhhZXy', 'pemilik'),
('Novita', 'kasir', '$2y$10$ylcdv0sFEAyrokVwj8jPV.DSWf3vPrQsPr6dr.03Jn7xkWRZYIYsC', 'kasir');

INSERT INTO produk (nama_produk, kategori, harga, satuan, stok, stok_minimum, status) VALUES
('Apel', 'Buah Segar', 25000, 'kg', 40, 10, 'aktif'),
('Jeruk', 'Buah Segar', 22000, 'kg', 8, 10, 'aktif'),
('Semangka', 'Buah Segar', 12000, 'kg', 30, 10, 'aktif'),
('Anggur', 'Buah Impor', 55000, 'kg', 5, 8, 'aktif'),
('Melon', 'Buah Segar', 18000, 'kg', 0, 6, 'aktif'),
('Pisang', 'Buah Segar', 15000, 'sisir', 24, 8, 'aktif');
