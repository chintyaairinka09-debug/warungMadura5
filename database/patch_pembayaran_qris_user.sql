ALTER TABLE tbl_pembayaran
  ADD COLUMN IF NOT EXISTS bukti_pembayaran VARCHAR(255) NULL AFTER referensi_simulasi;
