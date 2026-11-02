
// =====================================
// CC KASIR - JAVASCRIPT UMUM
// =====================================

document.addEventListener('DOMContentLoaded', function () {

    // Pesan otomatis hilang
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () {
            el.style.display = 'none';
        }, 6000);
    });

    // Pratinjau foto produk
    var pilihFoto = document.getElementById('foto');
    var gambarPreview = document.getElementById('foto-preview');

    if (pilihFoto && gambarPreview) {
        pilihFoto.addEventListener('change', function () {
            var berkas = pilihFoto.files[0];

            if (!berkas) return;

            if (berkas.size > 8 * 1024 * 1024) {
                alert('Ukuran foto maksimal 8 MB.');
                pilihFoto.value = '';
                return;
            }

            gambarPreview.src = URL.createObjectURL(berkas);
            gambarPreview.style.display = 'block';
        });
    }

    // Jam navbar
    var jam = document.getElementById('jam');

    if (jam) {
        function tampilJam() {
            var d = new Date();

            jam.textContent =
                ('0' + d.getHours()).slice(-2) + ':' +
                ('0' + d.getMinutes()).slice(-2);
        }

        tampilJam();
        setInterval(tampilJam, 20000);
    }

    // Filter periode laporan
    document.querySelectorAll('.filters input[type=date]').forEach(function (el) {
        el.addEventListener('change', function () {
            var periode = document.getElementById('periode');
            if (periode) periode.value = 'custom';
        });
    });

    // =====================================
    // KERANJANG KASIR
    // =====================================

    var form = document.getElementById('form-kasir');

    if (!form) return;

    var daftarProduk = Array.isArray(window.PRODUK) ? window.PRODUK : [];
    var keranjang = [];

    var cartList = document.getElementById('cart-list');
    var cartTotal = document.getElementById('cart-total');
    var inputItems = document.getElementById('items');
    var inputBayar = document.getElementById('bayar');
    var kembalian = document.getElementById('kembalian');
    var pencarian = document.getElementById('cari');
    var gridProduk = document.getElementById('grid');
    var tabs = document.getElementById('tabs');

    function rupiah(angka) {
        return 'Rp ' + Math.round(Number(angka) || 0)
            .toLocaleString('id-ID');
    }

    function formatJumlah(angka) {
        return Number(angka).toLocaleString('id-ID', {
            maximumFractionDigits: 2
        });
    }

    function totalBelanja() {
        var total = keranjang.reduce(function (jumlah, item) {
            return jumlah + (item.harga * item.qty);
        }, 0);

        return Math.round(total);
    }

    function gambarKosong() {
        return '<p class="muted">Keranjang masih kosong.</p>';
    }

    function renderKeranjang() {
        if (!keranjang.length) {
            cartList.innerHTML = gambarKosong();
        } else {
            cartList.innerHTML = keranjang.map(function (item) {
                var subtotal = Math.round(item.harga * item.qty);

                return `
                    <div class="cart-item">
                        <div class="cart-product">
                            <b>${escapeHTML(item.nama)}</b>
                            <small>${rupiah(item.harga)}/${escapeHTML(item.satuan)}</small>
                        </div>

                        <div class="cart-price">
                            ${rupiah(item.harga)}
                        </div>

                        <div class="cart-qty">
                            <button type="button"
                                data-kurang="${item.id}">−</button>

                            <input
                                type="text"
                                inputmode="decimal"
                                value="${formatJumlah(item.qty)}"
                                data-qty="${item.id}"
                                aria-label="Jumlah ${escapeHTML(item.nama)}"
                            >

                            <button type="button"
                                data-tambah="${item.id}">+</button>
                        </div>

                        <div class="cart-subtotal">
                            <b>${rupiah(subtotal)}</b>
                            <button type="button" class="cart-hapus"
                                data-hapus="${item.id}">×</button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        cartTotal.textContent = rupiah(totalBelanja());

        inputItems.value = JSON.stringify(keranjang.map(function (item) {
            return {
                id: item.id,
                qty: Number(item.qty.toFixed(2))
            };
        }));

        hitungKembalian();
    }

    function escapeHTML(teks) {
        return String(teks).replace(/[&<>"']/g, function (karakter) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[karakter];
        });
    }

    function tambahProduk(id) {
        var produk = daftarProduk.find(function (p) {
            return p.id === id;
        });

        if (!produk || produk.stok <= 0) return;

        var item = keranjang.find(function (p) {
            return p.id === id;
        });

        if (item) {
            ubahJumlah(id, 1);
        } else {
            keranjang.push({
                id: produk.id,
                nama: produk.nama,
                harga: produk.harga,
                satuan: produk.satuan,
                qty: 1,
                stok: produk.stok
            });

            renderKeranjang();
        }
    }

    // Ubah jumlah menggunakan angka desimal
    function ubahJumlah(id, perubahan) {
        var item = keranjang.find(function (p) {
            return p.id === id;
        });

        if (!item) return;

        var jumlahBaru = Math.round((item.qty + perubahan) * 100) / 100;

        if (jumlahBaru <= 0) {
            keranjang = keranjang.filter(function (p) {
                return p.id !== id;
            });
        } else if (jumlahBaru > item.stok) {
            alert('Jumlah melebihi stok yang tersedia.');
            return;
        } else {
            item.qty = jumlahBaru;
        }

        renderKeranjang();
    }

    // Input jumlah manual
    cartList.addEventListener('change', function (event) {
        var input = event.target.closest('[data-qty]');

        if (!input) return;

        var id = Number(input.dataset.qty);
        var item = keranjang.find(function (p) {
            return p.id === id;
        });

        if (!item) return;

        var nilai = input.value.trim().replace(',', '.');
        var jumlahBaru = Number(nilai);

        if (
            nilai === '' ||
            !Number.isFinite(jumlahBaru) ||
            jumlahBaru <= 0
        ) {
            alert('Masukkan jumlah pembelian yang benar.');
            renderKeranjang();
            return;
        }

        if (!/^\d+(\.\d{1,2})?$/.test(nilai)) {
            alert('Jumlah maksimal 2 angka di belakang koma.');
            renderKeranjang();
            return;
        }

        if (jumlahBaru > item.stok) {
            alert('Jumlah melebihi stok yang tersedia. Stok tersedia: ' +
                formatJumlah(item.stok));
            renderKeranjang();
            return;
        }

        item.qty = Math.round(jumlahBaru * 100) / 100;

        renderKeranjang();
    });

    function hitungKembalian() {
        var total = totalBelanja();
        var bayar = Number(inputBayar.value) || 0;

        kembalian.textContent = rupiah(Math.max(0, bayar - total));
    }

    // Klik produk
    if (gridProduk) {
        gridProduk.addEventListener('click', function (event) {
            var produk = event.target.closest('[data-add]');
            if (!produk) return;

            tambahProduk(Number(produk.dataset.add));
        });
    }

    // Tombol tambah, kurang, dan hapus
    cartList.addEventListener('click', function (event) {
        var tambah = event.target.closest('[data-tambah]');
        var kurang = event.target.closest('[data-kurang]');
        var hapus = event.target.closest('[data-hapus]');

        if (tambah) {
            ubahJumlah(Number(tambah.dataset.tambah), 1);
        }

        if (kurang) {
            ubahJumlah(Number(kurang.dataset.kurang), -1);
        }

        if (hapus) {
            var id = Number(hapus.dataset.hapus);

            keranjang = keranjang.filter(function (p) {
                return p.id !== id;
            });

            renderKeranjang();
        }
    });

    // Hapus semua
    var tombolKosong = document.getElementById('kosongkan');

    if (tombolKosong) {
        tombolKosong.addEventListener('click', function () {
            keranjang = [];
            renderKeranjang();
        });
    }

    // Pembayaran
    inputBayar.addEventListener('input', hitungKembalian);

    // Filter pencarian
    function filterProduk() {
        var kata = pencarian.value.toLowerCase();
        var kategoriAktif = tabs.querySelector('.tab.on');
        var kategori = kategoriAktif ? kategoriAktif.dataset.kat : '';

        gridProduk.querySelectorAll('.prod').forEach(function (el) {
            var cocokNama = el.dataset.nama.includes(kata);
            var cocokKategori = !kategori || el.dataset.kat === kategori;

            el.style.display = cocokNama && cocokKategori ? '' : 'none';
        });
    }

    pencarian.addEventListener('input', filterProduk);

    // Filter kategori
    tabs.addEventListener('click', function (event) {
        var tombol = event.target.closest('.tab');
        if (!tombol) return;

        tabs.querySelectorAll('.tab').forEach(function (tab) {
            tab.classList.remove('on');
        });

        tombol.classList.add('on');
        filterProduk();
    });

    // Pilihan pembayaran
    form.querySelectorAll('input[name="metode"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            form.querySelectorAll('.seg').forEach(function (seg) {
                seg.classList.toggle(
                    'on',
                    seg.querySelector('input').checked
                );
            });
        });
    });

    // Validasi sebelum simpan
    form.addEventListener('submit', function (event) {
        if (!keranjang.length) {
            event.preventDefault();
            alert('Keranjang masih kosong. Silakan pilih produk.');
            return;
        }

        var total = totalBelanja();
        var bayar = Number(inputBayar.value) || 0;
        var metode = form.querySelector('input[name="metode"]:checked');

        if (!metode) {
            event.preventDefault();
            alert('Pilih metode pembayaran.');
            return;
        }

        for (var i = 0; i < keranjang.length; i++) {
            if (
                keranjang[i].qty <= 0 ||
                keranjang[i].qty > keranjang[i].stok
            ) {
                event.preventDefault();
                alert('Jumlah pembelian tidak valid atau melebihi stok.');
                return;
            }
        }

        if (metode.value === 'tunai' && bayar < total) {
            event.preventDefault();
            alert('Jumlah pembayaran masih kurang.');
            return;
        }

        if (metode.value === 'transfer') {
            inputBayar.value = total;
        }
    });

    renderKeranjang();
});
