<?php
/**
 * GTB BANK — api/login.php
 * Étape 1 : vérifie identifiants, génère OTP 2FA
 */
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/session.php';
require_once __DIR__ . '/../../backend/security.php';
require_once __DIR__ . '/../../backend/helpers.php';

Session::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    json_response(['success' => false, 'error' => 'Méthode non autorisée'], 405);

$data     = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$email    = strtolower(trim($data['email']    ?? ''));
$password = (string)($data['password']        ?? '');
$csrf     = (string)($data['csrf_token']      ?? '');

if (!Security::csrfCheck($csrf))
    json_response(['success' => false, 'error' => 'Jeton de sécurité invalide. Rafraîchissez la page.'], 403);

if ($email === '' || $password === '')
    json_response(['success' => false, 'error' => 'Email et mot de passe requis.']);

// Throttling anti-brute force
$ip       = Security::clientIp();
$attempts = (int) DB::scalar(
    "SELECT COUNT(*) FROM login_attempts
     WHERE (email = :e OR ip_address = :ip) AND success = 0
     AND attempted_at > DATE_SUB(NOW(), INTERVAL :win SECOND)",
    ['e' => $email, 'ip' => $ip, 'win' => LOGIN_THROTTLE_WIN]
);
if ($attempts >= LOGIN_THROTTLE_MAX)
    json_response(['success' => false, 'error' => 'Trop de tentatives. Réessayez dans 15 minutes.'], 429);

// ── Vérification admin (table admins, pas de 2FA) ──
$admin = DB::one(
    "SELECT id, email, password_hash, first_name, last_name, role, status
     FROM admins WHERE email = :e AND status = 'active' LIMIT 1",
    ['e' => $email]
);

if ($admin && Security::verifyPassword($password, $admin['password_hash'])) {
    DB::insertInto('login_attempts', [
        'email'        => $email,
        'ip_address'   => $ip,
        'success'      => 1,
        'attempted_at' => date('Y-m-d H:i:s'),
    ]);
    // Connexion directe sans 2FA pour les admins
    Session::loginAdmin($admin);
    json_response(['success' => true, 'redirect' => GTB_BASE_URL . '/admin/index.php']);
}

// ── Vérification utilisateur classique (table users, avec 2FA) ──
$user = DB::one(
    "SELECT id, email, password_hash,
            COALESCE(prenom, first_name, '') AS prenom,
            COALESCE(nom,    last_name,  '') AS nom,
            telephone, role, is_active, two_fa_enabled
     FROM users WHERE email = :e LIMIT 1",
    ['e' => $email]
);

if (!$user || !$user['is_active'] || !Security::verifyPassword($password, $user['password_hash'])) {
    usleep(random_int(200_000, 500_000));
    DB::insertInto('login_attempts', [
        'email'        => $email,
        'ip_address'   => $ip,
        'success'      => 0,
        'attempted_at' => date('Y-m-d H:i:s'),
    ]);
    json_response(['success' => false, 'error' => 'Identifiants invalides.']);
}

DB::insertInto('login_attempts', [
    'email'        => $email,
    'ip_address'   => $ip,
    'success'      => 1,
    'attempted_at' => date('Y-m-d H:i:s'),
]);

// Connexion directe sans 2FA si désactivé (comptes de test)
if (!$user['two_fa_enabled']) {
    $fullUser = DB::one(
        "SELECT id, email, COALESCE(first_name, prenom, '') AS first_name,
                COALESCE(last_name, nom, '') AS last_name,
                role, client_number, avatar_url, kyc_status, status
         FROM users WHERE id = :id LIMIT 1",
        ['id' => $user['id']]
    );
    Session::loginUser($fullUser);
    $redirect = GTB_BASE_URL . (($fullUser['role'] ?? 'user') === 'admin' ? '/admin/index.php' : '/dashboard/index.php');
    json_response(['success' => true, 'redirect' => $redirect]);
}

$otp   = Security::randomDigits(OTP_LENGTH);
$prenom = $user['prenom'] ?: $user['nom'] ?: 'Client';
DB::insertInto('otp_codes', [
    'user_id'    => $user['id'],
    'code_hash'  => password_hash($otp, PASSWORD_BCRYPT, ['cost' => 8]),
    'expires_at' => date('Y-m-d H:i:s', time() + OTP_LIFETIME),
    'used'       => 0,
    'created_at' => date('Y-m-d H:i:s'),
]);

Session::setPending2FA((int) $user['id'], 'login');
send_otp_email($email, $prenom, $otp);

json_response(['success' => true, 'requires_2fa' => true, 'email_mask' => mask_email($email)]);

function mask_email(string $email): string {
    [$local, $domain] = explode('@', $email, 2);
    $masked = substr($local, 0, 2) . str_repeat('•', max(2, strlen($local) - 2));
    return $masked . '@' . $domain;
}
