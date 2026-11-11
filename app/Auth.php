<?php
declare(strict_types=1);

final class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']['id_pengguna']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function attempt(PDO $db, string $username, string $password): bool
    {
        $stmt = $db->prepare("SELECT id_pengguna, nama_lengkap, username, password_hash, role, status_pengguna FROM tbl_pengguna WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if (!$user || $user['status_pengguna'] !== 'aktif' || !in_array($user['role'], ['admin', 'kasir', 'pelanggan'], true) || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id_pengguna' => (int)$user['id_pengguna'],
            'nama_lengkap' => $user['nama_lengkap'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
        $db->prepare('UPDATE tbl_pengguna SET terakhir_login = NOW() WHERE id_pengguna = ?')->execute([$user['id_pengguna']]);
        log_activity($db, (int)$user['id_pengguna'], 'login', 'tbl_pengguna', (int)$user['id_pengguna'], 'Pengguna berhasil login');
        return true;
    }

    public static function logout(PDO $db): void
    {
        if (self::check()) {
            log_activity($db, (int)$_SESSION['user']['id_pengguna'], 'logout', 'tbl_pengguna', (int)$_SESSION['user']['id_pengguna'], 'Pengguna logout');
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? '') === 'admin';
    }

    public static function canUseOperations(): bool
    {
        return in_array(self::user()['role'] ?? '', ['admin', 'kasir'], true);
    }
}
