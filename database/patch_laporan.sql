ALTER TABLE tbl_detail_transaksi
  ADD COLUMN IF NOT EXISTS harga_modal DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER diskon_item;

CREATE TABLE IF NOT EXISTS tbl_pengeluaran (
  id_pengeluaran BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  no_pengeluaran VARCHAR(50) NOT NULL UNIQUE,
  kategori_pengeluaran ENUM('listrik','transportasi','perlengkapan','gaji','sewa','lainnya') NOT NULL DEFAULT 'lainnya',
  deskripsi VARCHAR(255) NOT NULL,
  jumlah DECIMAL(14,2) NOT NULL,
  tanggal_pengeluaran DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_pengguna INT UNSIGNED NULL,
  bukti VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pengeluaran_pengguna FOREIGN KEY (id_pengguna)
    REFERENCES tbl_pengguna(id_pengguna) ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_pengeluaran_tanggal (tanggal_pengeluaran),
  INDEX idx_pengeluaran_kategori (kategori_pengeluaran)
) ENGINE=InnoDB;
