# WarungMadura — Admin Website

Website admin untuk sistem minimarket WarungMadura. Dibuat dengan PHP Native OOP, PDO, MariaDB, Bootstrap 5.3.8, JavaScript, dan Chart.js 4.6.1.

## Fitur

- Login berbasis role Admin, Kasir, dan Pelanggan/User dengan password hash bcrypt. Akun pelanggan diarahkan ke portal terpisah dan tidak dapat membuka panel Admin/Kasir.
- Dashboard penjualan: kartu ringkasan, grafik penjualan 7 hari, diagram produk per kategori, transaksi terbaru, dan peringatan stok minimum.
- Produk: kategori berbentuk chip yang bisa dipilih untuk memfilter katalog, pencarian nama/SKU/barcode, filter status, pagination, modal tambah/edit, import massal CSV, serta upload gambar JPG/PNG/WebP maksimal 5 MB. Nama produk menjadi tempat mencantumkan merek.
- Kategori: tambah, edit, aktif/nonaktif (Admin).
- Pelanggan/member: tambah pelanggan, pencarian, pagination, melihat saldo simulasi, dan top-up simulasi (Admin).
- Pengguna: kelola akun Admin, Kasir, dan Pelanggan/User; buat akun member otomatis beserta saldo simulasi, ubah role/password, dan aktif/nonaktif (Admin).
- Portal Pelanggan: katalog produk, keranjang, checkout Duitku sandbox ambil-di-toko, status pembayaran otomatis, profil member, saldo virtual, dan riwayat transaksi milik akun tersebut.
- Pembayaran Duitku: invoice dibuat di server, pelanggan memilih metode pembayaran di halaman Duitku, dan callback bertanda tangan memproses pesanan otomatis.
- Supplier dan stok masuk: catat barang masuk satu atau beberapa produk sekaligus; stok dan riwayat mutasi diperbarui dalam transaksi SQL.
- Kasir/POS: cari dan filter produk, keranjang, hitung total, pembayaran tunai/Tabungan/E-Wallet simulasi.
- Laporan: filter tanggal, cari nama pelanggan atau kode transaksi, status pembayaran, pembaruan status pesanan, serta cetak ulang struk.
- Struk thermal berukuran 77 × 297 mm.
- Responsive untuk desktop, tablet, dan ponsel.

## 1. Persyaratan

- XAMPP dengan Apache dan MariaDB/MySQL aktif.
- PHP 8.1 atau lebih baru disarankan.
- Ekstensi PHP `pdo_mysql` aktif.
- Database `db_warungmadura` sudah dibuat dari file SQL paket database.
- Koneksi internet untuk memuat Bootstrap, Bootstrap Icons, Chart.js, dan Google Fonts dari CDN. (Website inti berjalan lokal, tetapi tampilan CDN perlu koneksi internet.)

## 2. Instalasi

1. Pastikan database `db_warungmadura` sudah di-import dari `db_warungmadura.sql`.
2. Salin folder aplikasi ke `C:\xampp\htdocs\` (contoh: `C:\xampp\htdocs\WarungMadura\`).
3. Buka `config/config.php` dan sesuaikan koneksi jika konfigurasi XAMPP berbeda:
   - `DB_HOST` — default `127.0.0.1`
   - `DB_PORT` — default `3306`
   - `DB_NAME` — `db_warungmadura`
   - `DB_USER` — default `root`
   - `DB_PASS` — default kosong untuk instalasi XAMPP umum
   - `BASE_URL` — ditentukan otomatis dari lokasi `index.php`; tidak perlu diubah saat nama folder berbeda.
4. Jalankan Apache dan MySQL dari XAMPP Control Panel.
5. Buka `http://localhost/WarungMadura/` (sesuaikan `WarungMadura` dengan nama folder aplikasi).
6. Jika database lama sudah terpasang, jalankan `database/patch_laporan.sql` sekali melalui phpMyAdmin untuk menambah tabel pengeluaran dan modal barang pada transaksi.
7. Jalankan `database/patch_pembayaran_qris_user.sql` untuk menambahkan kolom bukti pembayaran QRIS pada database lama.
8. Jika folder `vendor` belum tersedia, pastikan Composer sudah terpasang lalu jalankan `composer install` untuk mengaktifkan ekspor PDF dan Excel.
9. Untuk database lama, jalankan `database/patch_duitku.sql` melalui phpMyAdmin. Jalankan ulang jika patch sebelumnya sudah pernah diterapkan; patch ini menambahkan URL invoice dan nomor Virtual Account untuk portal serta panel Admin/Kasir.
10. Kredensial sandbox tersimpan di `config/duitku.local.php` dan dikecualikan dari Git. Callback Duitku harus dapat diakses dari internet; untuk pengujian lokal gunakan alamat publik HTTPS/tunnel dan daftarkan URL callback tersebut pada dashboard Duitku. Setelah siap produksi, ganti `DUITKU_SANDBOX` menjadi `false` dan gunakan kredensial live.

> Jangan import database `db_warungmadura.sql` berulang kali jika sudah berhasil. `CREATE TABLE IF NOT EXISTS` tidak mengosongkan tabel yang ada, tetapi data seed `INSERT IGNORE` tidak memperbarui password lama.

## 3. Akun demo

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `AdminWM2026!` |
| Kasir | `kasir` | `KasirWM2026!` |
| Kasir 2 | `kasir2` | `Kasir2WM2026!` |
| Alya Putri | `userdemo1` | `UserDemo2026!` |
| Bima Pratama | `userdemo2` | `UserDemo2026!` |
| Nadia Safitri | `userdemo3` | `UserDemo2026!` |

Akun demo dibuat untuk pengujian lokal. Ketiga akun pelanggan memakai password bersama `UserDemo2026!`. Admin dapat membuat akun Kasir dan Pelanggan/User melalui menu **Pengguna & Hak Akses**. Untuk akun pelanggan baru, sistem otomatis membuat nomor member serta saldo Tabungan dan E-Wallet virtual. Password disimpan dengan bcrypt menggunakan `password_hash()` dan diverifikasi dengan `password_verify()`. Ganti password demo sebelum aplikasi dipakai sungguhan.

## 4. Hak akses

### Admin

Akses penuh: dashboard, tambah/edit/nonaktif produk, import produk, kategori, pelanggan, top-up saldo simulasi, pengguna, supplier, stok, POS, laporan, dan cetak nota. Nama produk mencakup merek bila diperlukan; tidak ada field merek terpisah.

### Kasir

Boleh melihat katalog dan stok, menambahkan pelanggan, mencatat stok masuk, menjalankan transaksi POS, melihat laporan, memperbarui status proses pesanan, dan mencetak nota. Kasir **tidak bisa menambah, mengedit, mengimport, atau menonaktifkan produk**, mengelola pengguna, mengubah kategori, mengelola supplier, atau melakukan top-up saldo.

### Pelanggan / User

Login diarahkan ke portal pelanggan, bukan dashboard Admin. Pelanggan dapat melihat katalog, membuat pesanan ambil-di-toko, lalu memilih metode pembayaran di halaman Duitku. Status lunas dan pemrosesan hanya ditetapkan setelah callback Duitku lolos validasi tanda tangan. Pelanggan hanya dapat melihat transaksi milik akunnya; akses ke halaman operasional/Admin diblokir.

Produk tidak dihapus permanen. Admin menonaktifkan produk agar data transaksi lama tetap tersimpan. Perubahan stok dilakukan lewat penerimaan barang/POS supaya mutasi stok tercatat.

## 5. Laporan dan rekap

Menu **Laporan** menampilkan enam kartu laporan:

- Penjualan: jumlah transaksi, total penjualan, dan barang terlaris.
- Pembelian barang: penerimaan stok beserta supplier dan total biaya.
- Pengeluaran: pencatatan listrik, transportasi, perlengkapan, gaji, sewa, dan lainnya.
- Arus kas: penjualan sebagai pemasukan serta pembelian/pengeluaran sebagai uang keluar.
- Stok: sisa stok dan daftar barang yang perlu dibeli.
- Laba rugi: penjualan dikurangi harga modal barang terjual dan pengeluaran operasional.

Setiap laporan dapat difilter berdasarkan tanggal, dibuka dalam halaman pratinjau cetak khusus, dan diunduh sebagai PDF atau Excel tanpa memakai dialog Windows Print. Kasir dapat menambah serta mengedit pengeluaran.
Laporan penjualan, pembelian, pengeluaran, arus kas, dan laba rugi dibatasi ke aktivitas kasir yang sedang login; Admin dapat memilih satu kasir atau melihat gabungan. Laporan stok menunjukkan stok toko yang dipakai bersama, bukan stok terpisah per kasir.

## 6. Import produk massal

1. Buka menu Produk → Import CSV.
2. Unduh `import/template_produk.csv`.
3. Isi satu baris per produk. Kolom wajib: `sku`, `nama_produk`, `nama_kategori`, `harga_jual`.
4. Nama kategori harus sama persis dengan data kategori di database. Kolom `nama_merk` dari template lama bersifat opsional dan diabaikan.
5. Upload CSV. SKU yang sudah ada dilewati, bukan ditimpa.

## 7. Cetak struk 77 × 297 mm

Buka Laporan Penjualan, klik **Cetak** pada transaksi. Jendela nota menyediakan tombol cetak dan mengatur ukuran halaman 77 × 297 mm. Di dialog print, pilih printer EPSON, orientasi Portrait, dan matikan header/footer browser bila opsi tersebut tersedia. Pastikan ukuran kertas 77 × 297 mm atau ukuran kustom yang sama sudah tersedia di driver printer.

## 8. Catatan penting

- Tabungan dan E-Wallet hanya saldo virtual. QRIS DANA di POS menggunakan gambar QRIS Merchant yang diunggah Admin; kasir harus memeriksa pembayaran di aplikasi DANA Bisnis dan mengonfirmasi secara manual. Di portal pelanggan, bukti transfer QRIS diperiksa Admin/Kasir melalui menu **Verifikasi QRIS**. Pesanan baru menjadi lunas setelah disetujui. Stok ditahan saat checkout dan dikembalikan bila pesanan dibatalkan atau bukti ditolak.
- Screenshot bukti pembayaran dibatasi ke JPG, PNG, atau WebP maksimal 5 MB dan disimpan pada folder yang tidak dapat diakses langsung. Bukti hanya dapat dilihat oleh pemilik transaksi serta Admin/Kasir yang berwenang.
- Menu POS membuat transaksi toko dengan status lunas setelah validasi stok dan pembayaran. Pengurangan stok, detail transaksi, mutasi stok, dan mutasi saldo diproses dalam transaksi database agar konsisten.
- Pembatalan transaksi lunas tidak disediakan dari form status sederhana karena harus disertai proses refund dan pengembalian stok yang terpisah.
- QRIS DANA di POS tetap diverifikasi manual oleh kasir. Portal pelanggan memakai Duitku; callback perlu URL publik yang bisa dijangkau server Duitku dan ditandai sebagai lunas hanya setelah signature dan nominal tervalidasi.
- Link demo sukses yang disediakan Duitku adalah untuk pengujian sandbox. Aplikasi memakai `returnUrl` portal sendiri agar pelanggan kembali ke riwayat transaksi; redirect browser tidak dijadikan bukti pembayaran.

## Struktur folder

```text
WarungMadura_Admin/
├── app/                 # Database PDO, autentikasi, CSRF, dan aksi
├── assets/              # CSS dan JavaScript
├── config/              # Konfigurasi database dan URL
├── database/            # SQL database dan patch akun demo
├── import/              # Template CSV produk
├── pages/               # Halaman admin, kasir, laporan, dan nota
├── partials/             # Layout sidebar, header, footer
├── index.php             # Router aplikasi
└── README.md
```
