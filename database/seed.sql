-- CC KASIR - Data awal untuk pengujian (data contoh, bukan data asli toko).
-- Akun demo: pemilik / admin123  dan  kasir / kasir123  (GANTI sebelum dipakai sungguhan).
USE 'cc-kasir';

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
