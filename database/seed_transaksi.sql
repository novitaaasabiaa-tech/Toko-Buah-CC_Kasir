-- CC KASIR - Data contoh transaksi (OPSIONAL, hanya untuk mengisi dashboard & laporan saat uji coba).
-- Jalankan setelah schema.sql dan seed.sql. Tanggal dibuat relatif terhadap hari ini.
USE cc_kasir;

INSERT INTO transaksi (kode_transaksi, tanggal, id_user, total, bayar, kembalian, metode_bayar) VALUES
('DEMO-001', NOW(), 2, 62000, 70000, 8000, 'tunai'),
('DEMO-002', NOW(), 2, 55000, 55000, 0, 'transfer'),
('DEMO-003', DATE_SUB(NOW(), INTERVAL 1 DAY), 2, 119000, 120000, 1000, 'tunai'),
('DEMO-004', DATE_SUB(NOW(), INTERVAL 2 DAY), 2, 66000, 70000, 4000, 'tunai'),
('DEMO-005', DATE_SUB(NOW(), INTERVAL 3 DAY), 2, 61000, 100000, 39000, 'tunai');

INSERT INTO transaksi_detail (id_transaksi, id_produk, harga_satuan, qty, subtotal) VALUES
((SELECT id_transaksi FROM transaksi WHERE kode_transaksi = 'DEMO-001'), 1, 25000, 2, 50000),
((SELECT id_transaksi FROM transaksi WHERE kode_transaksi = 'DEMO-001'), 3, 12000, 1, 12000),
((SELECT id_transaksi FROM transaksi WHERE kode_transaksi = 'DEMO-002'), 4, 55000, 1, 55000),
((SELECT id_transaksi FROM transaksi WHERE kode_transaksi = 'DEMO-003'), 1, 25000, 3, 75000),
((SELECT id_transaksi FROM transaksi WHERE kode_transaksi = 'DEMO-003'), 2, 22000, 2, 44000),
((SELECT id_transaksi FROM transaksi WHERE kode_transaksi = 'DEMO-004'), 6, 15000, 2, 30000),
((SELECT id_transaksi FROM transaksi WHERE kode_transaksi = 'DEMO-004'), 3, 12000, 3, 36000),
((SELECT id_transaksi FROM transaksi WHERE kode_transaksi = 'DEMO-005'), 5, 18000, 2, 36000),
((SELECT id_transaksi FROM transaksi WHERE kode_transaksi = 'DEMO-005'), 1, 25000, 1, 25000);
