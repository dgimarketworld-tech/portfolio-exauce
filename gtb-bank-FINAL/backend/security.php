<?php
/**
 * ════════════════════════════════════════════════════════════════
 *  GTB BANK — Sécurité
 *  Hashing, CSRF, tokens, OTP, sanitization
 * ════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

final class Security
{
    // ─────────────────────────────────────────────────────────
    //  MOTS DE PASSE
    // ─────────────────────────────────────────────────────────
    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_ALGO, PASSWORD_OPTIONS);
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ALGO, PASSWORD_OPTIONS);
    }

    /**
     * Évalue la robustesse d'un mot de passe.
     * Retourne ['score' => 0-4, 'feedback' => 'message']
     */
    public static function passwordStrength(string $pwd): array
    {
        $score = 0;
        if (strlen($pwd) >= 8)            $score++;
        if (strlen($pwd) >= 12)           $score++;
        if (preg_match('/[A-Z]/', $pwd))  $score++;
        if (preg_match('/[a-z]/', $pwd))  $score++;
        if (preg_match('/[0-9]/', $pwd))  $score++;
        if (preg_match('/[^A-Za-z0-9]/', $pwd)) $score++;
        $score = min(4, intdiv($score, 1));

        $labels = ['Très faible', 'Faible', 'Moyen', 'Bon', 'Excellent'];
        return ['score' => $score, 'label' => $labels[$score] ?? 'Inconnu'];
    }

    // ─────────────────────────────────────────────────────────
    //  TOKENS ALÉATOIRES
    // ─────────────────────────────────────────────────────────
    public static function randomToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    public static function randomDigits(int $length = 6): string
    {
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $out .= random_int(0, 9);
        }
        return $out;
    }

    /** HMAC SHA-256 avec APP_SECRET. */
    public static function hmac(string $data): string
    {
        return hash_hmac('sha256', $data, APP_SECRET);
    }

    /** Comparaison constante-time. */
    public static function hashEquals(string $a, string $b): bool
    {
        return hash_equals($a, $b);
    }

    // ─────────────────────────────────────────────────────────
    //  CSRF
    // ─────────────────────────────────────────────────────────
    public static function csrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }
        if (empty($_SESSION[CSRF_TOKEN_NAME])
            || empty($_SESSION[CSRF_TOKEN_NAME . '_time'])
            || (time() - $_SESSION[CSRF_TOKEN_NAME . '_time']) > CSRF_LIFETIME) {
            $_SESSION[CSRF_TOKEN_NAME]          = self::randomToken(32);
            $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    public static function csrfField(): string
    {
        $token = self::csrfToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $token . '">';
    }

    public static function csrfCheck(?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE)       return false;
        if (empty($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) return false;
        if ((time() - ($_SESSION[CSRF_TOKEN_NAME . '_time'] ?? 0)) > CSRF_LIFETIME) return false;
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    public static function verifyCsrf(?string $token): bool
    {
        return self::csrfCheck($token);
    }

    /** Vérifie le CSRF sur la requête POST courante, sinon 403. */
    public static function requireCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $token = $_POST[CSRF_TOKEN_NAME] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if (!self::csrfCheck($token)) {
            http_response_code(419);
            die('CSRF token invalide ou expiré. Veuillez recharger la page.');
        }
    }

    // ─────────────────────────────────────────────────────────
    //  SANITIZATION
    // ─────────────────────────────────────────────────────────
    public static function e(?string $str): string
    {
        return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function clean(?string $str): string
    {
        return trim((string) preg_replace('/[\x00-\x1F\x7F]/u', '', $str ?? ''));
    }

    public static function email(?string $email): ?string
    {
        $email = self::clean(strtolower($email ?? ''));
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    // ─────────────────────────────────────────────────────────
    //  OTP — Génération, envoi, vérification
    // ─────────────────────────────────────────────────────────

    /**
     * Génère un OTP, le stocke en DB (hashé) et retourne le code clair.
     * Le code clair sera affiché/envoyé via le canal voulu.
     */
    public static function generateOtp(
        string $purpose,
        ?int $userId = null,
        ?int $adminId = null,
        string $channel = 'email',
        string $recipient = '',
        ?string $refObject = null,
        ?int $refObjectId = null
    ): string {
        $code = self::randomDigits(OTP_LENGTH);
        $hash = password_hash($code, PASSWORD_DEFAULT);

        DB::insertInto('otp_codes', [
            'user_id'        => $userId,
            'admin_id'       => $adminId,
            'purpose'        => $purpose,
            'channel'        => $channel,
            'recipient'      => $recipient,
            'code_hash'      => $hash,
            'attempts'       => 0,
            'max_attempts'   => OTP_MAX_TRIES,
            'ref_object'     => $refObject,
            'ref_object_id'  => $refObjectId,
            'used'           => 0,
            'expires_at'     => date('Y-m-d H:i:s', time() + OTP_LIFETIME),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        return $code;
    }

    /**
     * Vérifie un OTP. Retourne true si OK et marque le code comme consommé.
     */
    public static function verifyOtp(
        string $purpose,
        string $code,
        ?int $userId = null,
        ?int $adminId = null
    ): bool {
        $sql = "SELECT * FROM otp_codes
                WHERE purpose = :p
                  AND used = 0
                  AND expires_at > NOW()";
        $params = ['p' => $purpose];

        if ($userId !== null)  { $sql .= " AND user_id  = :u"; $params['u'] = $userId; }
        if ($adminId !== null) { $sql .= " AND admin_id = :a"; $params['a'] = $adminId; }

        $sql .= " ORDER BY id DESC LIMIT 1";
        $row = DB::one($sql, $params);

        if (!$row) return false;

        if ($row['attempts'] >= $row['max_attempts']) {
            DB::update("UPDATE otp_codes SET used = 1 WHERE id = :id", ['id' => $row['id']]);
            return false;
        }

        if (!password_verify($code, $row['code_hash'])) {
            DB::update("UPDATE otp_codes SET attempts = attempts + 1 WHERE id = :id", ['id' => $row['id']]);
            return false;
        }

        DB::update(
            "UPDATE otp_codes SET used = 1 WHERE id = :id",
            ['id' => $row['id']]
        );
        return true;
    }

    /** Nettoie les OTP expirés (à appeler via cron). */
    public static function purgeExpiredOtp(): int
    {
        return DB::update(
            "DELETE FROM otp_codes WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 DAY)"
        );
    }

    // ─────────────────────────────────────────────────────────
    //  AUDIT LOG
    // ─────────────────────────────────────────────────────────
    public static function audit(
        string $action,
        string $actorType = 'system',
        ?int $actorId = null,
        ?string $actorEmail = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $details = null,
        string $severity = 'info'
    ): void {
        try {
            DB::insertInto('audit_log', [
                'actor_type'  => $actorType,
                'actor_id'    => $actorId,
                'actor_email' => $actorEmail,
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'details'     => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
                'ip_address'  => self::clientIp(),
                'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'severity'    => $severity,
            ]);
        } catch (\Throwable $e) {
            error_log('[AUDIT] ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────
    //  CLIENT
    // ─────────────────────────────────────────────────────────
    public static function clientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $h) {
            if (!empty($_SERVER[$h])) {
                $ip = explode(',', $_SERVER[$h])[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }
}
// ── CSRF admin helpers (aliases) ──────────────────────────────────
function csrf_token_admin(): string {
    return Security::csrfToken();
}
function verifyCsrfAdmin(string $token): bool {
    return Security::verifyCsrf($token);
}

function csrf_token(): string {
    return Security::csrfToken();
}
function csrf_field(): string {
    return Security::csrfField();
}
