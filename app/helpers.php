<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function product_image_src(mixed $image): ?string
{
    $image = trim((string)$image);
    if ($image === '') return null;

    if (filter_var($image, FILTER_VALIDATE_URL)) {
        $scheme = strtolower((string)parse_url($image, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true) ? $image : null;
    }

    if (preg_match('#\Auploads/(?:products/)?[A-Za-z0-9._-]+\.(?:jpe?g|png|webp)\z#i', $image)) {
        return BASE_URL . '/' . $image;
    }
    return null;
}

function url(string $page = 'dashboard', array $params = []): string
{
    $query = array_merge(['page' => $page], $params);
    return BASE_URL . '/index.php?' . http_build_query($query);
}

function redirect(string $page = 'dashboard', array $params = []): never
{
    header('Location: ' . url($page, $params));
    exit;
}

function money(mixed $amount): string
{
    return 'Rp ' . number_format((float)$amount, 0, ',', '.');
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

function status_badge(string $status): string
{
    $map = [
        'aktif' => 'success', 'lunas' => 'success', 'selesai' => 'success',
        'diterima' => 'success', 'berhasil' => 'success',
        'menunggu' => 'warning', 'belum_bayar' => 'warning', 'draft' => 'secondary',
        'diproses' => 'info', 'siap_diambil' => 'primary',
        'nonaktif' => 'secondary', 'dibatalkan' => 'danger', 'gagal' => 'danger',
        'dikembalikan' => 'dark', 'nonaktif' => 'secondary'
    ];
    $label = ucwords(str_replace('_', ' ', $status));
    return '<span class="badge text-bg-' . ($map[$status] ?? 'secondary') . '">' . e($label) . '</span>';
}

function pagination(int $total, int $page, int $perPage, string $pageName, array $params = []): void
{
    $pages = max(1, (int)ceil($total / $perPage));
    if ($pages <= 1) return;
    $start = max(1, $page - 2);
    $end = min($pages, $page + 2);
    echo '<nav aria-label="Pagination"><ul class="pagination pagination-sm mb-0">';
    $prev = max(1, $page - 1);
    $next = min($pages, $page + 1);
    echo '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '"><a class="page-link" href="' . e(url($pageName, array_merge($params, ['p' => $prev]))) . '">‹</a></li>';
    for ($i = $start; $i <= $end; $i++) {
        echo '<li class="page-item ' . ($i === $page ? 'active' : '') . '"><a class="page-link" href="' . e(url($pageName, array_merge($params, ['p' => $i]))) . '">' . $i . '</a></li>';
    }
    echo '<li class="page-item ' . ($page >= $pages ? 'disabled' : '') . '"><a class="page-link" href="' . e(url($pageName, array_merge($params, ['p' => $next]))) . '">›</a></li>';
    echo '</ul></nav>';
}

function log_activity(PDO $db, ?int $userId, string $activity, ?string $table = null, ?int $rowId = null, ?string $description = null): void
{
    $stmt = $db->prepare('INSERT INTO tbl_log_aktivitas (id_pengguna, aktivitas, nama_tabel, id_data, keterangan, alamat_ip) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $activity, $table, $rowId, $description, $_SERVER['REMOTE_ADDR'] ?? null]);
}

function require_admin(): void
{
    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        flash('danger', 'Menu ini hanya dapat diakses Admin.');
        redirect('dashboard');
    }
}
