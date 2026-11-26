-- Bisa dijalankan ulang di db_warungmadura; kolom yang sudah ada akan dilewati.
ALTER TABLE tbl_transaksi
  MODIFY metode_pembayaran ENUM('tunai','tabungan','ewallet','qris_dana','duitku') NULL;

ALTER TABLE tbl_pembayaran
  MODIFY metode_pembayaran ENUM('tunai','tabungan','ewallet','qris_dana','duitku') NOT NULL;

ALTER TABLE tbl_pembayaran
  ADD COLUMN IF NOT EXISTS kode_metode_gateway VARCHAR(10) NULL AFTER metode_pembayaran,
  ADD COLUMN IF NOT EXISTS url_pembayaran VARCHAR(500) NULL AFTER referensi_simulasi,
  ADD COLUMN IF NOT EXISTS va_number VARCHAR(80) NULL AFTER url_pembayaran;
