// Skrip umum CC KASIR. Fitur interaktif (keranjang kasir, dll.) ditambahkan di tahap berikutnya.
document.addEventListener('DOMContentLoaded', function () {
    // Pesan error otomatis hilang setelah 6 detik.
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () { el.style.display = 'none'; }, 6000);
    });
});

// Pratinjau foto produk saat memilih berkas.
document.addEventListener('DOMContentLoaded', function () {
    var pilih = document.getElementById('foto');
    var gambar = document.getElementById('foto-preview');
    if (!pilih || !gambar) { return; }
    pilih.addEventListener('change', function () {
        var berkas = pilih.files[0];
        if (!berkas) { return; }
        if (berkas.size > 8 * 1024 * 1024) {
            alert('Ukuran foto maksimal 8 MB.');
            pilih.value = '';
            return;
        }
        gambar.src = URL.createObjectURL(berkas);
        gambar.style.display = 'block';
    });
});

// Jam di bar atas, dan pilihan periode otomatis menjadi "Rentang tanggal" bila tanggal diubah.
document.addEventListener('DOMContentLoaded', function () {
    var jam = document.getElementById('jam');
    if (jam) {
        var tampil = function () {
            var d = new Date();
            jam.textContent = ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
        };
        tampil();
        setInterval(tampil, 20000);
    }
    document.querySelectorAll('.filters input[type=date]').forEach(function (el) {
        el.addEventListener('change', function () {
            var pilih = document.getElementById('periode');
            if (pilih) { pilih.value = 'custom'; }
        });
    });
});
