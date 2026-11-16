# 🛒 Sistem Supermarket & Point of Sale (POS) Modern

Aplikasi web kasir dan manajemen inventaris supermarket lengkap berbasis **PHP** dan database **MySQL** dengan antarmuka modern menggunakan **Bootstrap 5.3**, **Bootstrap Icons**, dan **SweetAlert2**.

---

## 📋 Fitur Utama Sistem

1. **Dashboard Eksekutif**:
   - Ringkasan omzet penjualan hari ini secara realtime.
   - Total produk terdaftar dan valuasi aset stok.
   - Peringatan stok kritis / menipis (&le; 10) yang memerlukan restock segera.
   - Ranking 5 produk terlaris (*Top Selling Products*) berdasarkan jumlah transaksi.

2. **Manajemen Data Master (CRUD)**:
   - **Kategori Produk**: Tambah, edit, hapus kategori dengan proteksi relasi integritas (kategori tidak dapat dihapus jika masih menaungi produk).
   - **Produk & Stok**:
     * Pencarian instan berdasarkan barcode dan nama produk.
     * Filter kategori dan filter status stok (Aman, Menipis, Habis).
     * Input barcode via barcode scanner fisik atau tombol acak (*Auto Generate*).
     * Kalkulasi otomatis margin keuntungan dan estimasi laba kotor secara realtime saat input harga beli dan harga jual.
     * Satuan fleksibel (Pcs, Bungkus, Botol, Pouch, Karung, Renteng, dll).

3. **Terminal Kasir / Point of Sale (POS) Interaktif**:
   - **Dukungan Barcode Scanner Fisik**: Input barcode otomatis mendeteksi enter dan langsung menambahkan barang ke keranjang kasir disertai efek audio beep kasir asli.
   - **Pencarian Cepat & Tab Kategori**: Kasir dapat memilih produk langsung dari grid visual atau mencari nama barang.
   - **Perhitungan Otomatis**:
     * Subtotal per item dan subtotal keseluruhan secara otomatis saat qty diubah (+ / -).
     * Integrasi **Promo Diskon** (persen `%` atau nominal `Rp`).
     * Integrasi **Sistem Loyalty Member** (1 poin = potongan Rp 100, dan otomatis mendapatkan poin baru setiap belanja Rp 10.000).
     * Pilihan multi metode bayar: `TUNAI`, `QRIS`, `DEBIT`, `TRANSFER`.
     * Tombol cepat pecahan uang tunai (Uang Pas, 10Rb, 20Rb, 50Rb, 100Rb, 200Rb) dan perhitungan kembalian otomatis.
   - **Penyesuaian Stok Otomatis (Auto Deduction)**:
     * Dijalankan dalam transaksi database ACID (`beginTransaction`, `commit`, `rollBack`).
     * Saat transaksi berstatus `SELESAI`, stok produk di tabel `produk` langsung berkurang sesuai kuantitas yang dibeli.
     * Dilengkapi validasi server-side anti minus: menolak transaksi jika stok fisik di gudang tidak mencukupi.

4. **Struk Pembayaran Termal & Riwayat Transaksi**:
   - Tampilan modal struk kasir termal format standar (58mm/80mm) yang siap dicetak langsung (`window.print()`).
   - Riwayat seluruh transaksi lengkap dengan filter tanggal dan pencarian nomor struk.
   - Tombol cetak ulang struk (*Reprint Receipt*) kapan saja.

---

## 🗄️ Struktur Database (MySQL)

Sistem menggunakan 8 tabel yang saling berelasi:

| No | Nama Tabel | Kolom |
|---|---|---|
| 1 | `kategori` | `id`, `nama_kategori` |
| 2 | `supplier` | `id`, `nama_supplier`, `kontak_person`, `no_telepon`, `alamat` |
| 3 | `pengguna` | `id`, `nama_pengguna`, `jabatan`, `username`, `password` |
| 4 | `member` | `id`, `nama_member`, `no_telepon`, `poin`, `tanggal_daftar` |
| 5 | `promo` | `id`, `nama_promo`, `jenis_diskon`, `nilai_diskon`, `tanggal_mulai`, `tanggal_selesai` |
| 6 | `produk` | `id`, `barcode`, `nama_produk`, `id_kategori`, `harga_beli`, `harga_jual`, `stok`, `satuan` |
| 7 | `transaksi` | `id`, `nomor_struk`, `id_kasir`, `id_member`, `id_promo`, `subtotal`, `total_diskon`, `poin_dipakai`, `poin_didapat`, `total_akhir`, `metode_bayar`, `jumlah_bayar`, `kembalian`, `status`, `waktu_transaksi` |
| 8 | `detail_transaksi` | `id`, `id_transaksi`, `id_produk`, `harga_satuan`, `jumlah`, `subtotal` |

---

## 🚀 Panduan Instalasi & Menjalankan

### Cara 1: Menggunakan XAMPP / Laragon (Rekomendasi MySQL)

1. Pastikan service **Apache** dan **MySQL** di XAMPP / Laragon sudah dalam status **Running**.
2. Letakkan folder project ini di dalam direktori `htdocs` (misal: `C:/xampp/htdocs/databases_supermarket`).
3. Konfigurasi kredensial database di file `config/database.php`:
   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_PORT', '3306');
   define('DB_NAME', 'supermarket_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
4. Buka browser dan akses skrip wizard otomatis:
   ```
   http://localhost/databases_supermarket/setup.php
   ```
   Klik tombol **"Buat Database MySQL & Import Data"**. Skrip akan otomatis membuat database `supermarket_db`, 8 tabel, dan mengisi data awal.
   
   *(Atau alternatif manual: Anda bisa langsung mengimpor file `database.sql` melalui phpMyAdmin).*

5. Selesai! Buka aplikasi di:
   ```
   http://localhost/databases_supermarket/index.php
   ```

---

### Cara 2: Menjalankan Langsung via PHP Built-in Server

Anda juga bisa menjalankan aplikasi secara instan melalui terminal command line:

```bash
cd "c:\Users\Andrew\Documents\New folder\databases_supermarket"
php -S localhost:8000
```

Lalu buka browser di `http://localhost:8000`. Jika server MySQL Anda belum diaktifkan, aplikasi memiliki fallback otomatis ke mode SQLite standalone yang telah terisi data produk.

---

## 🔐 Akun Default Kasir & Admin

| Username | Password | Jabatan |
|---|---|---|
| `admin` | `admin123` | Admin |
| `kasir1` | `admin123` | Kasir |

---

## 📂 Struktur Direktori Proyek

```
databases_supermarket/
│
├── config/
│   └── database.php              # Pengaturan koneksi PDO & session
├── includes/
│   ├── header.php                # Layout navigasi & styling Bootstrap 5
│   ├── footer.php                # Layout footer & script SweetAlert2
│   └── functions.php             # Format Rupiah, sanitasi input, nomor struk unik
├── api/
│   ├── search_product.php        # API pencarian produk & scanner barcode
│   ├── get_member.php            # API data member & saldo poin
│   ├── process_transaction.php   # API checkout POS, kalkulasi & auto update stok
│   └── get_transaction_detail.php# API rincian transaksi untuk cetak ulang struk
├── kategori/
│   ├── index.php                 # List data kategori & jumlah produk terkait
│   ├── create.php                # Tambah kategori baru
│   └── edit.php                  # Edit nama kategori
├── produk/
│   ├── index.php                 # Master produk, barcode, harga, stok, filter
│   ├── create.php                # Tambah produk & kalkulator live margin laba
│   └── edit.php                  # Edit data produk & stok
├── database.sql                  # File DDL & DML skema MySQL lengkap
├── setup.php                     # Wizard web inisialisasi database 1-klik
├── kasir.php                     # Layar Kasir / Point of Sale Interaktif
├── riwayat.php                   # Riwayat transaksi penjualan & reprint struk
└── index.php                     # Dashboard utama & ringkasan analitik
```
