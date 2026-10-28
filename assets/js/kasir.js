// Logika keranjang halaman Kasir. Data produk berasal dari variabel PRODUK (dibuat di views/kasir.php).
(function () {
    var cart = {};   // { idProduk: jumlah }
    var byId = {};
    PRODUK.forEach(function (p) { byId[p.id] = p; });

    var elList = document.getElementById('cart-list');
    var elTotal = document.getElementById('cart-total');
    var elBayar = document.getElementById('bayar');
    var elKembali = document.getElementById('kembalian');
    var elItems = document.getElementById('items');
    var form = document.getElementById('form-kasir');
    var grid = document.getElementById('grid');
    var cari = document.getElementById('cari');

    function rp(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }

    function total() {
        var t = 0;
        Object.keys(cart).forEach(function (id) { t += Math.round(cart[id] * byId[id].harga); });
        return t;
    }

    function hitungKembalian() {
        var k = (parseFloat(elBayar.value) || 0) - total();
        elKembali.textContent = rp(Math.max(0, k));
    }

    function render() {
        elList.innerHTML = '';
        var ids = Object.keys(cart);
        if (ids.length === 0) {
            var kosong = document.createElement('p');
            kosong.className = 'muted pad';
            kosong.textContent = 'Belum ada produk. Klik "+ Tambah".';
            elList.appendChild(kosong);
        }
        ids.forEach(function (id) {
            var p = byId[id];
            var row = document.createElement('div');
            row.className = 'it';

            var info = document.createElement('div');
            var nama = document.createElement('b');
            nama.textContent = p.nama;
            var harga = document.createElement('small');
            harga.textContent = rp(p.harga) + ' / ' + p.satuan;
            info.appendChild(nama);
            info.appendChild(document.createElement('br'));
            info.appendChild(harga);

            var qty = document.createElement('input');
            qty.type = 'number';
            qty.step = '0.1';
            qty.min = '0.1';
            qty.max = p.stok;
            qty.value = cart[id];
            qty.className = 'qty-in';
            qty.addEventListener('change', function () {
                var v = parseFloat(qty.value);
                if (!(v > 0)) { delete cart[id]; } else { cart[id] = Math.min(v, p.stok); }
                render();
            });

            var hapus = document.createElement('button');
            hapus.type = 'button';
            hapus.className = 'qty-del';
            hapus.textContent = '×';
            hapus.addEventListener('click', function () { delete cart[id]; render(); });

            row.appendChild(info);
            row.appendChild(qty);
            row.appendChild(hapus);
            elList.appendChild(row);
        });
        elTotal.textContent = rp(total());
        hitungKembalian();
    }

    function tambah(id) {
        var p = byId[id];
        var q = (cart[id] || 0) + 1;
        if (q > p.stok) {
            alert('Stok ' + p.nama + ' hanya ' + p.stok + ' ' + p.satuan + '.');
            q = p.stok;
        }
        cart[id] = q;
        render();
    }

    grid.addEventListener('click', function (e) {
        var tombol = e.target.closest('[data-add]');
        if (tombol && !tombol.disabled) { tambah(tombol.getAttribute('data-add')); }
    });

    cari.addEventListener('input', function () {
        var kata = cari.value.toLowerCase().trim();
        grid.querySelectorAll('.prod').forEach(function (kartu) {
            kartu.style.display = kartu.getAttribute('data-nama').indexOf(kata) === -1 ? 'none' : '';
        });
    });

    elBayar.addEventListener('input', hitungKembalian);
    document.getElementById('kosongkan').addEventListener('click', function () { cart = {}; render(); });

    form.addEventListener('submit', function (e) {
        var t = total();
        var b = parseFloat(elBayar.value) || 0;
        if (t <= 0) { e.preventDefault(); alert('Keranjang masih kosong.'); return; }
        if (b < t) { e.preventDefault(); alert('Nominal bayar kurang dari total belanja.'); return; }
        elItems.value = JSON.stringify(Object.keys(cart).map(function (id) {
            return { id: parseInt(id, 10), qty: cart[id] };
        }));
    });

    render();
})();
