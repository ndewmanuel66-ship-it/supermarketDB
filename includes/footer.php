    </div>
</main>

<!-- Footer -->
<footer class="bg-white border-top py-3 mt-auto">
    <div class="container-fluid px-lg-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 small text-muted">
        <div>
            &copy; <?= date('Y') ?> <strong>Sistem Supermarket POS</strong> - Solusi Kasir & Manajemen Stok Modern.
        </div>
        <div class="d-flex align-items-center gap-3">
            <span><i class="bi bi-clock me-1"></i> <span id="currentRealtimeClock"><?= date('H:i:s') ?> WIB</span></span>
            <span>&bull;</span>
            <span class="text-success"><i class="bi bi-circle-fill" style="font-size: 8px;"></i> Online</span>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3.3 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Live Realtime Clock
    function updateLiveClock() {
        const el = document.getElementById('currentRealtimeClock');
        if (el) {
            const now = new Date();
            el.textContent = now.toLocaleTimeString('id-ID') + ' WIB';
        }
    }
    setInterval(updateLiveClock, 1000);

    // Konfirmasi Hapus Umum dengan SweetAlert2
    function confirmDelete(event, formOrUrl, title = 'Hapus Data Ini?') {
        event.preventDefault();
        Swal.fire({
            title: title,
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                if (typeof formOrUrl === 'string') {
                    window.location.href = formOrUrl;
                } else if (formOrUrl && formOrUrl.submit) {
                    formOrUrl.submit();
                }
            }
        });
    }

    // Format Number to Rupiah JS helper
    function formatRupiahJS(angka) {
        return 'Rp ' + Number(angka).toLocaleString('id-ID');
    }
</script>
</body>
</html>
