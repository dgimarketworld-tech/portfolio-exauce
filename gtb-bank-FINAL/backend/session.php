<?php
/**
 * ════════════════════════════════════════════════════════════════
 *  GTB BANK — Gestion des sessions
 *  Sessions sécurisées avec rotation d'ID et timeout d'inactivité.
 * ════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

final class Session
{
    /** Démarre la session avec paramètres durcis. */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;

        // Cookie params
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => COOKIE_SECURE,
            'httponly' => COOKIE_HTTPONLY,
            'samesite' => COOKIE_SAMESITE,
        ]);

        session_name(SESSION_NAME);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);

        session_start();

        // Anti-fixation : régénérer l'ID périodiquement
        if (!isset($_SESSION['_init'])) {
            $_SESSION['_init']           = time();
            $_SESSION['_last_regen']     = time();
            $_SESSION['_last_activity']  = time();
            $_SESSION['_fingerprint']    = self::fingerprint();
            session_regenerate_id(true);
        } else {
            // Timeout d'inactivité
            if (time() - ($_SESSION['_last_activity'] ?? 0) > SESSION_LIFETIME) {
                self::destroy();
                self::start();
                return;
            }

            // Vérification fingerprint (légère, pas trop strict)
            if (($_SESSION['_fingerprint'] ?? '') !== self::fingerprint()) {
                self::destroy();
                self::start();
                return;
            }

            // Régénération périodique
            if (time() - ($_SESSION['_last_regen'] ?? 0) > SESSION_REGEN_EVERY) {
                session_regenerate_id(true);
                $_SESSION['_last_regen'] = time();
            }

            $_SESSION['_last_activity'] = time();
        }
    }

    /** Fingerprint léger (User-Agent uniquement). */
    private static function fingerprint(): string
    {
        return hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . APP_SECRET);
    }

    // ─────────────────────────────────────────────────────────
    //  Connexion / déconnexion
    // ─────────────────────────────────────────────────────────

    /** Connecte un USER (client) — appelé après vérification du mot de passe. */
    public static function loginUser(array $user): void
    {
        self::start();
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'            => (int) $user['id'],
            'client_number' => $user['client_number'],
            'email'         => $user['email'],
            'first_name'    => $user['first_name'],
            'last_name'     => $user['last_name'],
            'avatar_url'    => $user['avatar_url'] ?? null,
            'kyc_status'    => $user['kyc_status'] ?? 'pending',
            'logged_in_at'  => time(),
        ];
        unset($_SESSION['admin'], $_SESSION['_pending_2fa']);
        $_SESSION['_last_regen']    = time();
        $_SESSION['_last_activity'] = time();

        try {
            DB::update(
                "UPDATE users SET last_login_at = NOW(), last_login_ip = :ip, failed_logins = 0
                 WHERE id = :id",
                ['ip' => Security::clientIp(), 'id' => $user['id']]
            );
        } catch (Throwable $e) { error_log('[session] loginUser update: ' . $e->getMessage()); }
    }

    /** Connecte un ADMIN. */
    public static function loginAdmin(array $admin): void
    {
        self::start();
        session_regenerate_id(true);

        $_SESSION['admin'] = [
            'id'           => (int) $admin['id'],
            'email'        => $admin['email'],
            'first_name'   => $admin['first_name'],
            'last_name'    => $admin['last_name'],
            'role'         => $admin['role'],
            'logged_in_at' => time(),
        ];
        unset($_SESSION['user'], $_SESSION['_pending_2fa']);
        $_SESSION['_last_regen']    = time();
        $_SESSION['_last_activity'] = time();

        try { DB::update(
            "UPDATE admins SET last_login_at = NOW(), last_login_ip = :ip, failed_logins = 0
             WHERE id = :id",
            ['ip' => Security::clientIp(), 'id' => $admin['id']]
        ); } catch (Throwable $e) { error_log('[session] loginAdmin update: ' . $e->getMessage()); }
    }

    /** Détruit complètement la session. */
    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', [
                    'expires'  => time() - 42000,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'],
                ]);
            }
            session_destroy();
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Accesseurs
    // ─────────────────────────────────────────────────────────
    public static function isUser():  bool { return !empty($_SESSION['user']);  }
    public static function isAdmin(): bool { return !empty($_SESSION['admin']); }

    public static function user():  ?array { return $_SESSION['user']  ?? null; }
    public static function admin(): ?array { return $_SESSION['admin'] ?? null; }

    public static function userId():  ?int { return isset($_SESSION['user']['id'])  ? (int)$_SESSION['user']['id']  : null; }
    public static function adminId(): ?int { return isset($_SESSION['admin']['id']) ? (int)$_SESSION['admin']['id'] : null; }

    // ─────────────────────────────────────────────────────────
    //  Flash messages
    // ─────────────────────────────────────────────────────────
    public static function flash(string $key, ?string $value = null): ?string
    {
        self::start();
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $msg = $_SESSION['_flash'][$key] ?? null;
        if ($msg !== null) unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    public static function flashAll(): array
    {
        self::start();
        $msgs = $_SESSION['_flash'] ?? [];
        $_SESSION['_flash'] = [];
        return $msgs;
    }

    // ─────────────────────────────────────────────────────────
    //  2FA en attente (après login mais avant OTP)
    // ─────────────────────────────────────────────────────────
    public static function setPending2FA(int $userId, string $purpose = 'login'): void
    {
        self::start();
        $_SESSION['_pending_2fa'] = [
            'user_id' => $userId,
            'purpose' => $purpose,
            'time'    => time(),
        ];
    }

    public static function pending2FA(): ?array
    {
        self::start();
        $p = $_SESSION['_pending_2fa'] ?? null;
        if ($p && (time() - $p['time']) > 600) {
            // 10 min max pour valider l'OTP
            unset($_SESSION['_pending_2fa']);
            return null;
        }
        return $p;
    }

    public static function clearPending2FA(): void
    {
        unset($_SESSION['_pending_2fa']);
    }
}