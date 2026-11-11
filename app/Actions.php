<?php
declare(strict_types=1);

final class Actions
{
    public static function handle(PDO $db): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        Csrf::verify();
        $action = (string)($_POST['action'] ?? '');

        try {
            if ($action === 'login') {
                $username = trim((string)($_POST['username'] ?? ''));
                $password = (string)($_POST['password'] ?? '');
                if (Auth::attempt($db, $username, $password)) {
                    flash('success', 'Login berhasil. Selamat datang di WarungMadura.');
                    redirect((Auth::user()['role'] ?? '') === 'pelanggan' ? 'portal' : 'dashboard');
                }
                flash('danger', 'Username, password, atau status akun tidak sesuai.');
                redirect('login');
            }

            if (!Auth::check()) redirect('login');
            if ((Auth::user()['role'] ?? '') === 'pelanggan') redirect('portal');
            $userId = (int)Auth::user()['id_pengguna'];

            switch ($action) {
                case 'save_product': self::saveProduct($db, $userId); break;
                case 'deactivate_product': self::deactivateProduct($db, $userId); break;
                case 'import_products': self::importProducts($db, $userId); break;
                case 'save_category': self::saveCategory($db, $userId); break;
                case 'toggle_category': self::toggleCategory($db, $userId); break;
                case 'save_customer': self::saveCustomer($db, $userId); break;
                case 'save_user': self::saveUser($db, $userId); break;
                case 'toggle_user': self::toggleUser($db, $userId); break;
                case 'save_supplier': self::saveSupplier($db, $userId); break;
                case 'receive_stock': self::receiveStock($db, $userId); break;
                case 'checkout': self::checkout($db, $userId); break;
                case 'update_order_status': self::updateOrderStatus($db, $userId); break;
                case 'topup_balance': self::topupBalance($db, $userId); break;
                default:
                    flash('warning', 'Aksi tidak dikenali.');
                    redirect('dashboard');
            }
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[WarungMadura] ' . $error->getMessage());
            $friendly = $error instanceof PDOException && str_contains($error->getMessage(), 'Duplicate')
                ? 'Data sudah digunakan. Periksa SKU, barcode, username, atau nama yang harus unik.'
                : 'Terjadi kendala saat menyimpan data. Periksa isian dan coba kembali.';
            flash('danger', $friendly);
            redirect((string)($_POST['_return'] ?? 'dashboard'));
        }
    }

    private static function back(string $page, string $message, string $type = 'success', array $params = []): never
    {
        flash($type, $message);
        redirect($page, $params);
    }

    private static function adminOnly(): void
    {
        if (!Auth::isAdmin()) self::back('dashboard', 'Aksi ini hanya dapat dilakukan Admin.', 'danger');
    }

    private static function saveProduct(PDO $db, int $userId): void
    {
        $id = (int)($_POST['id_produk'] ?? 0);
        if ($id > 0) self::adminOnly();
        $sku = trim((string)($_POST['sku'] ?? ''));
        $name = trim((string)($_POST['nama_produk'] ?? ''));
        $category = (int)($_POST['id_kategori'] ?? 0);
        $priceBuy = (float)($_POST['harga_beli'] ?? 0);
        $priceSell = (float)($_POST['harga_jual'] ?? 0);
        $stock = max(0, (int)($_POST['stok'] ?? 0));
        $minimum = max(0, (int)($_POST['stok_minimum'] ?? 5));
        $unit = trim((string)($_POST['satuan'] ?? 'pcs')) ?: 'pcs';
        $barcode = trim((string)($_POST['barcode'] ?? '')) ?: null;
        $description = trim((string)($_POST['deskripsi'] ?? '')) ?: null;
        $image = trim((string)($_POST['gambar'] ?? '')) ?: null;
        if ($image !== null && product_image_src($image) === null) {
            self::back('products', 'URL gambar harus menggunakan alamat http atau https yang valid.', 'warning');
        }
        if ($sku === '' || $name === '' || $category < 1 || $priceSell <= 0 || $priceBuy < 0) {
            self::back('products', 'Lengkapi SKU, nama, kategori, dan harga jual yang valid.', 'warning');
        }
        $uploadedImage = self::saveProductImage();
        if ($uploadedImage !== null) $image = $uploadedImage;
        $check = $db->prepare("SELECT COUNT(*) FROM tbl_kategori WHERE id_kategori=? AND status_kategori='aktif'");
        $check->execute([$category]);
        if ((int)$check->fetchColumn() === 0) self::back('products', 'Kategori tidak ditemukan atau tidak aktif.', 'warning');
        if ($id > 0) {
            $stmt = $db->prepare('UPDATE tbl_produk SET id_kategori=?, id_merk=NULL, sku=?, barcode=?, nama_produk=?, deskripsi=?, harga_beli=?, harga_jual=?, satuan=?, stok_minimum=?, gambar=? WHERE id_produk=?');
            $stmt->execute([$category, $sku, $barcode, $name, $description, $priceBuy, $priceSell, $unit, $minimum, $image, $id]);
            log_activity($db, $userId, 'edit_produk', 'tbl_produk', $id, 'Admin mengubah produk ' . $name);
            self::back('products', 'Produk berhasil diperbarui.');
        }
        $stmt = $db->prepare('INSERT INTO tbl_produk (id_kategori,id_merk,sku,barcode,nama_produk,deskripsi,harga_beli,harga_jual,stok,satuan,stok_minimum,gambar) VALUES (?,NULL,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$category, $sku, $barcode, $name, $description, $priceBuy, $priceSell, $stock, $unit, $minimum, $image]);
        $newId = (int)$db->lastInsertId();
        if ($stock > 0) {
            $db->prepare("INSERT INTO tbl_mutasi_stok (id_produk,id_pengguna,jenis_mutasi,jumlah,stok_sebelum,stok_sesudah,keterangan) VALUES (?,?, 'penyesuaian', ?,0,?,'Stok awal produk baru')")
                ->execute([$newId, $userId, $stock, $stock]);
        }
        log_activity($db, $userId, 'tambah_produk', 'tbl_produk', $newId, 'Menambahkan produk ' . $name);
        self::back('products', 'Produk baru berhasil ditambahkan.');
    }

    private static function saveProductImage(): ?string
    {
        $file = $_FILES['gambar_file'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        if (in_array(($file['error'] ?? UPLOAD_ERR_OK), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            self::back('products', 'Ukuran gambar melebihi batas upload. Ukuran maksimal 5 MB.', 'warning');
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            self::back('products', 'Gambar gagal diunggah. Pilih ulang file gambar dan coba kembali.', 'warning');
        }
        if ((int)($file['size'] ?? 0) > 5 * 1024 * 1024) {
            self::back('products', 'Ukuran gambar maksimal 5 MB.', 'warning');
        }
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            self::back('products', 'File gambar tidak valid. Pilih file gambar dari perangkat ini.', 'warning');
        }

        $imageInfo = getimagesize($file['tmp_name']);
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$imageInfo || !isset($extensions[$mime]) || ($imageInfo['mime'] ?? '') !== $mime) {
            self::back('products', 'Format gambar harus JPG, PNG, atau WebP.', 'warning');
        }

        $directory = __DIR__ . '/../uploads/products';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            self::back('products', 'Folder penyimpanan gambar tidak dapat dibuat. Periksa izin folder uploads.', 'danger');
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
            self::back('products', 'Gambar tidak dapat disimpan. Periksa izin folder uploads/products.', 'danger');
        }
        return 'uploads/products/' . $filename;
    }

    private static function deactivateProduct(PDO $db, int $userId): void
    {
        self::adminOnly();
        $id = (int)($_POST['id_produk'] ?? 0);
        if ($id < 1) self::back('products', 'Produk tidak valid.', 'warning');
        $db->prepare("UPDATE tbl_produk SET status_produk='nonaktif' WHERE id_produk=?")->execute([$id]);
        log_activity($db, $userId, 'nonaktif_produk', 'tbl_produk', $id, 'Produk dinonaktifkan, bukan dihapus permanen');
        self::back('products', 'Produk dinonaktifkan. Riwayat transaksi tetap tersimpan.');
    }

    private static function importProducts(PDO $db, int $userId): void
    {
        if (!Auth::canUseOperations()) self::adminOnly();
        if (empty($_FILES['csv_file']['tmp_name']) || !is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
            self::back('products', 'Pilih file CSV terlebih dahulu.', 'warning');
        }
        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$handle) self::back('products', 'File CSV tidak dapat dibaca.', 'danger');
        $header = fgetcsv($handle);
        if (!$header) { fclose($handle); self::back('products', 'File CSV kosong.', 'warning'); }
        $header = array_map(static fn($x) => strtolower(trim(str_replace("\xEF\xBB\xBF", '', (string)$x))), $header);
        if (array_diff(['sku','nama_produk','nama_kategori','harga_jual'], $header)) {
            fclose($handle); self::back('products', 'Header CSV tidak sesuai template.', 'warning');
        }
        $indexes = array_flip($header);
        $inserted = 0; $skipped = 0; $rowNo = 1;
        $db->beginTransaction();
        while (($row = fgetcsv($handle)) !== false) {
            $rowNo++;
            if (count(array_filter($row, static fn($v) => trim((string)$v) !== '')) === 0) continue;
            $get = static fn(string $key, string $default = '') => trim((string)($row[$indexes[$key]] ?? $default));
            $sku = $get('sku'); $name = $get('nama_produk'); $catName = $get('nama_kategori');
            $sell = (float)$get('harga_jual', '0');
            if ($sku === '' || $name === '' || $catName === '' || $sell <= 0) { $skipped++; continue; }
            $cat = $db->prepare("SELECT id_kategori FROM tbl_kategori WHERE nama_kategori=? AND status_kategori='aktif' LIMIT 1");
            $cat->execute([$catName]); $catId = $cat->fetchColumn();
            if (!$catId) { $skipped++; continue; }
            $check = $db->prepare('SELECT COUNT(*) FROM tbl_produk WHERE sku=?'); $check->execute([$sku]);
            if ((int)$check->fetchColumn() > 0) { $skipped++; continue; }
            $stmt = $db->prepare('INSERT INTO tbl_produk (id_kategori,id_merk,sku,barcode,nama_produk,deskripsi,harga_beli,harga_jual,stok,satuan,stok_minimum,gambar) VALUES (?,NULL,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([(int)$catId, $sku, $get('barcode') ?: null, $name, $get('deskripsi') ?: null, (float)$get('harga_beli','0'), $sell, max(0,(int)$get('stok','0')), $get('satuan','pcs') ?: 'pcs', max(0,(int)$get('stok_minimum','5')), $get('gambar') ?: null]);
            $productId = (int)$db->lastInsertId();
            $initialStock = max(0, (int)$get('stok','0'));
            if ($initialStock > 0) {
                $db->prepare("INSERT INTO tbl_mutasi_stok (id_produk,id_pengguna,jenis_mutasi,jumlah,stok_sebelum,stok_sesudah,keterangan) VALUES (?,?, 'penyesuaian', ?,0,?,'Stok awal dari import CSV')")
                    ->execute([$productId, $userId, $initialStock, $initialStock]);
            }
            $inserted++;
        }
        fclose($handle);
        $db->commit();
        log_activity($db, $userId, 'import_produk_csv', 'tbl_produk', null, "Import CSV: {$inserted} ditambahkan, {$skipped} dilewati");
        self::back('products', "Import selesai: {$inserted} produk ditambahkan, {$skipped} baris dilewati.");
    }

    private static function saveCategory(PDO $db, int $userId): void
    {
        self::adminOnly();
        $id = (int)($_POST['id_kategori'] ?? 0);
        $name = trim((string)($_POST['nama_kategori'] ?? ''));
        $desc = trim((string)($_POST['deskripsi'] ?? '')) ?: null;
        if ($name === '') self::back('categories', 'Nama kategori wajib diisi.', 'warning');
        if ($id > 0) {
            $db->prepare('UPDATE tbl_kategori SET nama_kategori=?,deskripsi=? WHERE id_kategori=?')->execute([$name,$desc,$id]);
            log_activity($db,$userId,'edit_kategori','tbl_kategori',$id,'Mengubah kategori '.$name);
        } else {
            $db->prepare('INSERT INTO tbl_kategori (nama_kategori,deskripsi) VALUES (?,?)')->execute([$name,$desc]);
            log_activity($db,$userId,'tambah_kategori','tbl_kategori',(int)$db->lastInsertId(),'Menambahkan kategori '.$name);
        }
        self::back('categories', 'Kategori berhasil disimpan.');
    }

    private static function toggleCategory(PDO $db, int $userId): void
    {
        self::adminOnly();
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("UPDATE tbl_kategori SET status_kategori=IF(status_kategori='aktif','nonaktif','aktif') WHERE id_kategori=?");
        $stmt->execute([$id]);
        log_activity($db,$userId,'ubah_status_kategori','tbl_kategori',$id,'Mengubah status kategori');
        self::back('categories', 'Kategori berhasil diperbarui.');
    }

    private static function saveCustomer(PDO $db, int $userId): void
    {
        $id = (int)($_POST['id_pelanggan'] ?? 0);
        if ($id > 0) self::adminOnly();
        $name = trim((string)($_POST['nama_pelanggan'] ?? ''));
        $phone = trim((string)($_POST['no_hp'] ?? '')) ?: null;
        $email = trim((string)($_POST['email'] ?? '')) ?: null;
        $address = trim((string)($_POST['alamat'] ?? '')) ?: null;
        $city = trim((string)($_POST['kota'] ?? '')) ?: null;
        $member = trim((string)($_POST['no_member'] ?? ''));
        if ($name === '') self::back('customers', 'Nama pelanggan wajib diisi.', 'warning');
        if ($id > 0) {
            $db->prepare('UPDATE tbl_pelanggan SET nama_pelanggan=?,no_hp=?,email=?,alamat=?,kota=? WHERE id_pelanggan=?')->execute([$name,$phone,$email,$address,$city,$id]);
            log_activity($db,$userId,'edit_pelanggan','tbl_pelanggan',$id,'Mengubah pelanggan '.$name);
        } else {
            if ($member === '') $member = 'WM' . date('ymd') . strtoupper(bin2hex(random_bytes(2)));
            $db->prepare('INSERT INTO tbl_pelanggan (no_member,nama_pelanggan,no_hp,email,alamat,kota) VALUES (?,?,?,?,?,?)')->execute([$member,$name,$phone,$email,$address,$city]);
            $customerId = (int)$db->lastInsertId();
            foreach (['tabungan','ewallet'] as $type) {
                $db->prepare('INSERT INTO tbl_saldo_pelanggan (id_pelanggan,jenis_saldo,saldo) VALUES (?,?,0)')->execute([$customerId,$type]);
            }
            log_activity($db,$userId,'tambah_pelanggan','tbl_pelanggan',$customerId,'Pelanggan baru '.$name);
        }
        self::back('customers', 'Data pelanggan berhasil disimpan.');
    }

    private static function saveUser(PDO $db, int $userId): void
    {
        self::adminOnly();
        $id = (int)($_POST['id_pengguna'] ?? 0);
        $name = trim((string)($_POST['nama_lengkap'] ?? ''));
        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? '')) ?: null;
        $phone = trim((string)($_POST['no_hp'] ?? '')) ?: null;
        $role = (string)($_POST['role'] ?? 'kasir');
        $password = (string)($_POST['password'] ?? '');

        if ($name === '' || $username === '' || !in_array($role, ['admin', 'kasir', 'pelanggan'], true)) {
            self::back('users', 'Nama, username, dan role wajib diisi dengan benar.', 'warning');
        }
        if ($id === 0 && strlen($password) < 8) {
            self::back('users', 'Password akun baru minimal 8 karakter.', 'warning');
        }
        if ($password !== '' && strlen($password) < 8) {
            self::back('users', 'Password minimal 8 karakter.', 'warning');
        }
        if ($id === $userId && $role !== 'admin') {
            self::back('users', 'Role akun Admin yang sedang digunakan tidak dapat diubah.', 'warning');
        }

        $db->beginTransaction();
        try {
            if ($id > 0) {
                $current = $db->prepare('SELECT id_pengguna, role FROM tbl_pengguna WHERE id_pengguna=? FOR UPDATE');
                $current->execute([$id]);
                $existing = $current->fetch();
                if (!$existing) throw new RuntimeException('Akun pengguna tidak ditemukan.');

                if ($password !== '') {
                    $db->prepare('UPDATE tbl_pengguna SET nama_lengkap=?,username=?,email=?,no_hp=?,role=?,password_hash=? WHERE id_pengguna=?')
                        ->execute([$name, $username, $email, $phone, $role, password_hash($password, PASSWORD_DEFAULT), $id]);
                } else {
                    $db->prepare('UPDATE tbl_pengguna SET nama_lengkap=?,username=?,email=?,no_hp=?,role=? WHERE id_pengguna=?')
                        ->execute([$name, $username, $email, $phone, $role, $id]);
                }
                $savedId = $id;
            } else {
                $db->prepare('INSERT INTO tbl_pengguna (nama_lengkap,username,email,no_hp,password_hash,role) VALUES (?,?,?,?,?,?)')
                    ->execute([$name, $username, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $role]);
                $savedId = (int)$db->lastInsertId();
            }

            if ($role === 'pelanggan') {
                $profile = $db->prepare('SELECT id_pelanggan FROM tbl_pelanggan WHERE id_pengguna=? FOR UPDATE');
                $profile->execute([$savedId]);
                $customerId = (int)($profile->fetchColumn() ?: 0);
                if ($customerId > 0) {
                    $db->prepare('UPDATE tbl_pelanggan SET nama_pelanggan=?,no_hp=?,email=? WHERE id_pelanggan=?')
                        ->execute([$name, $phone, $email, $customerId]);
                } else {
                    $memberNumber = 'WM' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
                    $db->prepare('INSERT INTO tbl_pelanggan (id_pengguna,no_member,nama_pelanggan,no_hp,email) VALUES (?,?,?,?,?)')
                        ->execute([$savedId, $memberNumber, $name, $phone, $email]);
                    $customerId = (int)$db->lastInsertId();
                }
                foreach (['tabungan', 'ewallet'] as $balanceType) {
                    $db->prepare('INSERT IGNORE INTO tbl_saldo_pelanggan (id_pelanggan,jenis_saldo,saldo) VALUES (?,?,0)')
                        ->execute([$customerId, $balanceType]);
                }
            } else {
                // Jika akun pelanggan diubah menjadi akun staf, nomor member tetap tersimpan,
                // tetapi profil member tidak lagi terhubung ke akun login staf.
                $db->prepare('UPDATE tbl_pelanggan SET id_pengguna=NULL WHERE id_pengguna=?')->execute([$savedId]);
            }

            log_activity($db, $userId, $id ? 'edit_pengguna' : 'tambah_pengguna', 'tbl_pengguna', $savedId, 'Akun ' . $username . ' (' . $role . ') disimpan');
            $db->commit();
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }

        self::back('users', $id ? 'Akun berhasil diperbarui.' : 'Akun baru berhasil dibuat.');
    }

    private static function toggleUser(PDO $db, int $userId): void
    {
        self::adminOnly();
        $id = (int)($_POST['id_pengguna'] ?? 0);
        if ($id === $userId) self::back('users','Kamu tidak dapat menonaktifkan akun yang sedang digunakan.','warning');
        $db->prepare("UPDATE tbl_pengguna SET status_pengguna=IF(status_pengguna='aktif','nonaktif','aktif') WHERE id_pengguna=?")->execute([$id]);
        log_activity($db,$userId,'ubah_status_pengguna','tbl_pengguna',$id,'Mengubah status akun');
        self::back('users','Status akun diperbarui.');
    }

    private static function saveSupplier(PDO $db, int $userId): void
    {
        self::adminOnly();
        $code = trim((string)($_POST['kode_supplier'] ?? ''));
        $name = trim((string)($_POST['nama_supplier'] ?? ''));
        $contact = trim((string)($_POST['nama_kontak'] ?? '')) ?: null;
        $phone = trim((string)($_POST['no_hp'] ?? '')) ?: null;
        $email = trim((string)($_POST['email'] ?? '')) ?: null;
        $address = trim((string)($_POST['alamat'] ?? '')) ?: null;
        if ($code === '' || $name === '') self::back('stock','Kode dan nama supplier wajib diisi.','warning');
        $db->prepare('INSERT INTO tbl_supplier (kode_supplier,nama_supplier,nama_kontak,no_hp,email,alamat) VALUES (?,?,?,?,?,?)')->execute([$code,$name,$contact,$phone,$email,$address]);
        log_activity($db,$userId,'tambah_supplier','tbl_supplier',(int)$db->lastInsertId(),'Supplier '.$name);
        self::back('stock','Supplier berhasil ditambahkan.');
    }

    private static function receiveStock(PDO $db, int $userId): void
    {
        if (!Auth::canUseOperations()) self::back('dashboard','Akses tidak diizinkan.','danger');
        $supplierId = (int)($_POST['id_supplier'] ?? 0) ?: null;
        $productIds = $_POST['id_produk'] ?? [];
        $qtys = $_POST['jumlah'] ?? [];
        $costs = $_POST['harga_beli'] ?? [];
        if (!is_array($productIds) || count($productIds) === 0) self::back('stock','Tambahkan minimal satu produk.','warning');
        $validLines = 0;
        foreach ($productIds as $index => $productValue) {
            if ((int)$productValue > 0 && (int)($qtys[$index] ?? 0) > 0) $validLines++;
        }
        if ($validLines === 0) self::back('stock','Masukkan produk dan jumlah barang yang valid.','warning');
        $db->beginTransaction();
        $number = 'TRM-' . date('Ymd-His') . '-' . random_int(100,999);
        $db->prepare("INSERT INTO tbl_penerimaan_barang (no_penerimaan,id_supplier,id_pengguna,status_penerimaan,catatan) VALUES (?,?,?,'diterima',?)")
            ->execute([$number,$supplierId,$userId,trim((string)($_POST['catatan'] ?? '')) ?: null]);
        $receiptId = (int)$db->lastInsertId();
        foreach ($productIds as $index => $productValue) {
            $productId = (int)$productValue; $qty = max(0,(int)($qtys[$index] ?? 0)); $cost = max(0,(float)($costs[$index] ?? 0));
            if ($productId < 1 || $qty < 1) continue;
            $product = $db->prepare('SELECT stok,nama_produk FROM tbl_produk WHERE id_produk=? AND status_produk="aktif" FOR UPDATE');
            $product->execute([$productId]); $row = $product->fetch();
            if (!$row) throw new RuntimeException('Produk stok masuk tidak ditemukan.');
            $before = (int)$row['stok']; $after = $before + $qty;
            $db->prepare('INSERT INTO tbl_detail_penerimaan_barang (id_penerimaan,id_produk,jumlah,harga_beli,subtotal) VALUES (?,?,?,?,?)')->execute([$receiptId,$productId,$qty,$cost,$qty*$cost]);
            $db->prepare('UPDATE tbl_produk SET stok=?, harga_beli=IF(? > 0, ?, harga_beli) WHERE id_produk=?')->execute([$after,$cost,$cost,$productId]);
            $db->prepare("INSERT INTO tbl_mutasi_stok (id_produk,id_pengguna,id_penerimaan,jenis_mutasi,jumlah,stok_sebelum,stok_sesudah,keterangan) VALUES (?,?,?,'stok_masuk',?,?,?,?)")
                ->execute([$productId,$userId,$receiptId,$qty,$before,$after,'Penerimaan '.$number]);
        }
        $db->commit();
        log_activity($db,$userId,'penerimaan_barang','tbl_penerimaan_barang',$receiptId,'Penerimaan '.$number);
        self::back('stock','Barang berhasil diterima dan stok diperbarui.');
    }

    private static function checkout(PDO $db, int $userId): void
    {
        if (!Auth::canUseOperations()) self::back('dashboard','Akses tidak diizinkan.','danger');
        $cart = json_decode((string)($_POST['cart_json'] ?? '[]'), true);
        if (!is_array($cart) || !$cart) self::back('transactions','Keranjang masih kosong.','warning');
        $customerType = (string)($_POST['customer_type'] ?? 'umum');
        if (!in_array($customerType,['umum','member'],true)) self::back('transactions','Jenis pelanggan tidak valid.','warning');
        $customerId = null;
        if ($customerType === 'member') {
            $selectedCustomerId = (int)($_POST['id_pelanggan'] ?? 0);
            if ($selectedCustomerId <= 0) self::back('transactions','Pilih data pelanggan member terlebih dahulu.','warning');
            $customerCheck = $db->prepare("SELECT id_pelanggan FROM tbl_pelanggan WHERE id_pelanggan=? AND status_member='aktif'");
            $customerCheck->execute([$selectedCustomerId]);
            $customerId = $customerCheck->fetchColumn();
            if (!$customerId) self::back('transactions','Data pelanggan member tidak ditemukan atau sudah nonaktif.','warning');
            $customerId = (int)$customerId;
        }
        $method = (string)($_POST['metode_pembayaran'] ?? 'tunai');
        if (!in_array($method,['tunai','tabungan','ewallet'],true)) self::back('transactions','Metode pembayaran tidak valid.','warning');
        if ($method !== 'tunai' && !$customerId) self::back('transactions','Pembayaran saldo membutuhkan pelanggan terdaftar.','warning');
        $cash = max(0,(float)($_POST['jumlah_bayar'] ?? 0));
        $normalized = [];
        foreach ($cart as $item) {
            $id = (int)($item['id'] ?? 0); $qty = (int)($item['qty'] ?? 0);
            if ($id > 0 && $qty > 0) $normalized[$id] = ($normalized[$id] ?? 0) + $qty;
        }
        if (!$normalized) self::back('transactions','Isi keranjang tidak valid.','warning');

        $db->beginTransaction();
        $items = []; $subtotal = 0.0;
        foreach ($normalized as $productId => $qty) {
            $stmt = $db->prepare("SELECT id_produk,sku,nama_produk,harga_jual,stok FROM tbl_produk WHERE id_produk=? AND status_produk='aktif' FOR UPDATE");
            $stmt->execute([$productId]); $product = $stmt->fetch();
            if (!$product) throw new RuntimeException('Ada produk yang tidak tersedia.');
            if ((int)$product['stok'] < $qty) throw new RuntimeException('Stok ' . $product['nama_produk'] . ' tidak mencukupi.');
            $line = (float)$product['harga_jual'] * $qty; $subtotal += $line;
            $items[] = ['product'=>$product,'qty'=>$qty,'line'=>$line];
        }
        $total = $subtotal;
        $balanceId = null; $balanceBefore = 0.0;
        if ($method === 'tunai') {
            if ($cash < $total) throw new RuntimeException('Uang pembayaran kurang dari total belanja.');
            $paid = $cash; $change = $cash - $total;
        } else {
            $balance = $db->prepare('SELECT id_saldo,saldo FROM tbl_saldo_pelanggan WHERE id_pelanggan=? AND jenis_saldo=? AND status_saldo="aktif" FOR UPDATE');
            $balance->execute([$customerId,$method]); $wallet = $balance->fetch();
            if (!$wallet) throw new RuntimeException('Saldo pelanggan untuk metode ini belum tersedia.');
            $balanceId = (int)$wallet['id_saldo']; $balanceBefore = (float)$wallet['saldo'];
            if ($balanceBefore < $total) throw new RuntimeException('Saldo pelanggan tidak mencukupi.');
            $paid = $total; $change = 0.0;
        }
        $code = 'WM-' . date('ymd-His') . '-' . random_int(100,999);
        $db->prepare("INSERT INTO tbl_transaksi (kode_transaksi,id_pelanggan,id_kasir,jenis_transaksi,status_pesanan,status_pembayaran,metode_pembayaran,metode_penerimaan,tanggal_transaksi,subtotal,diskon,biaya_pengiriman,total_belanja,jumlah_bayar,kembalian) VALUES (?,?,?,'toko','selesai','lunas',?,'ambil_toko',NOW(),?,0,0,?,?,?)")
            ->execute([$code,$customerId,$userId,$method,$subtotal,$total,$paid,$change]);
        $transactionId = (int)$db->lastInsertId();
        foreach ($items as $item) {
            $p = $item['product']; $qty = $item['qty']; $line = $item['line'];
            $db->prepare('INSERT INTO tbl_detail_transaksi (id_transaksi,id_produk,nama_produk,sku_produk,jumlah,harga_satuan,diskon_item,subtotal_item) VALUES (?,?,?,?,?,?,0,?)')
                ->execute([$transactionId,$p['id_produk'],$p['nama_produk'],$p['sku'],$qty,$p['harga_jual'],$line]);
            $before = (int)$p['stok']; $after = $before - $qty;
            $db->prepare('UPDATE tbl_produk SET stok=? WHERE id_produk=?')->execute([$after,$p['id_produk']]);
            $db->prepare("INSERT INTO tbl_mutasi_stok (id_produk,id_pengguna,id_transaksi,jenis_mutasi,jumlah,stok_sebelum,stok_sesudah,keterangan) VALUES (?,?,?,'penjualan',?,?,?,'Penjualan kasir')")
                ->execute([$p['id_produk'],$userId,$transactionId,-$qty,$before,$after]);
        }
        $db->prepare('INSERT INTO tbl_pembayaran (id_transaksi,metode_pembayaran,jumlah_bayar,status_pembayaran,referensi_simulasi,diproses_oleh,waktu_pembayaran) VALUES (?,?,?,"berhasil",?,?,NOW())')
            ->execute([$transactionId,$method,$paid,'SIM-' . strtoupper(bin2hex(random_bytes(4))),$userId]);
        if ($balanceId !== null) {
            $after = $balanceBefore - $total;
            $db->prepare('UPDATE tbl_saldo_pelanggan SET saldo=? WHERE id_saldo=?')->execute([$after,$balanceId]);
            $db->prepare("INSERT INTO tbl_mutasi_saldo (id_saldo,id_transaksi,id_pengguna,jenis_mutasi,arah,jumlah,saldo_sebelum,saldo_sesudah,keterangan) VALUES (?,?,?,'pembayaran','keluar',?,?,?,'Pembayaran transaksi toko')")
                ->execute([$balanceId,$transactionId,$userId,$total,$balanceBefore,$after]);
        }
        $db->commit();
        log_activity($db,$userId,'checkout','tbl_transaksi',$transactionId,'Transaksi '.$code);
        self::back('receipt','Transaksi berhasil.', 'success', ['id'=>$transactionId]);
    }

    private static function updateOrderStatus(PDO $db, int $userId): void
    {
        $id = (int)($_POST['id_transaksi'] ?? 0);
        $status = (string)($_POST['status_pesanan'] ?? '');
        $allowed = ['menunggu','diproses','siap_diambil','selesai','dibatalkan'];
        if (!in_array($status,$allowed,true)) self::back('reports','Status pesanan tidak valid.','warning');
        if (!Auth::isAdmin() && !in_array($status,['diproses','siap_diambil','selesai'],true)) self::back('reports','Kasir hanya dapat memperbarui proses pesanan.','danger');
        $current = $db->prepare('SELECT status_pesanan,status_pembayaran FROM tbl_transaksi WHERE id_transaksi=?');
        $current->execute([$id]); $currentRow = $current->fetch();
        if (!$currentRow) self::back('reports','Transaksi tidak ditemukan.','warning');
        if ($status === 'dibatalkan' && $currentRow['status_pembayaran'] === 'lunas') self::back('reports','Transaksi lunas tidak dapat dibatalkan dari menu ini karena perlu proses pengembalian stok dan saldo secara terpisah.','warning');
        $db->prepare('UPDATE tbl_transaksi SET status_pesanan=? WHERE id_transaksi=?')->execute([$status,$id]);
        log_activity($db,$userId,'ubah_status_transaksi','tbl_transaksi',$id,'Status pesanan menjadi '.$status);
        self::back('reports','Status pesanan diperbarui.');
    }

    private static function topupBalance(PDO $db, int $userId): void
    {
        self::adminOnly();
        $customerId = (int)($_POST['id_pelanggan'] ?? 0);
        $type = (string)($_POST['jenis_saldo'] ?? 'tabungan');
        $amount = (float)($_POST['jumlah'] ?? 0);
        if ($customerId < 1 || !in_array($type,['tabungan','ewallet'],true) || $amount <= 0) self::back('customers','Data top-up tidak valid.','warning');
        $db->beginTransaction();
        $stmt = $db->prepare('SELECT id_saldo,saldo FROM tbl_saldo_pelanggan WHERE id_pelanggan=? AND jenis_saldo=? FOR UPDATE');
        $stmt->execute([$customerId,$type]); $balance = $stmt->fetch();
        if (!$balance) {
            $db->prepare('INSERT INTO tbl_saldo_pelanggan (id_pelanggan,jenis_saldo,saldo) VALUES (?,?,0)')->execute([$customerId,$type]);
            $balance = ['id_saldo'=>(int)$db->lastInsertId(),'saldo'=>0];
        }
        $before = (float)$balance['saldo']; $after = $before + $amount;
        $db->prepare('UPDATE tbl_saldo_pelanggan SET saldo=? WHERE id_saldo=?')->execute([$after,$balance['id_saldo']]);
        $db->prepare("INSERT INTO tbl_mutasi_saldo (id_saldo,id_pengguna,jenis_mutasi,arah,jumlah,saldo_sebelum,saldo_sesudah,keterangan) VALUES (?,?, 'topup_simulasi','masuk',?,?,?,'Top-up saldo simulasi oleh Admin')")
            ->execute([$balance['id_saldo'],$userId,$amount,$before,$after]);
        $db->commit();
        log_activity($db,$userId,'topup_saldo','tbl_saldo_pelanggan',(int)$balance['id_saldo'],'Top-up simulasi pelanggan '.$customerId);
        self::back('customers','Top-up saldo simulasi berhasil dicatat.');
    }
}
