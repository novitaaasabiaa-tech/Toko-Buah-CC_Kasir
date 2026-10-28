// Skrip umum CC KASIR. Fitur interaktif (keranjang kasir, dll.) ditambahkan di tahap berikutnya.
document.addEventListener('DOMContentLoaded', function () {
    // Pesan error otomatis hilang setelah 6 detik.
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () { el.style.display = 'none'; }, 6000);
    });
});
