<?php
/**
 * GTB BANK — api/forgot_password.php
 * Génère un OTP de réinitialisation de mot de passe
 */
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/session.php';
require_once __DIR__ . '/../../backend/security.php';
require_once __DIR__ . '/../../backend/helpers.php';

Session::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    json_response(['success' => false, 'error' => 'Méthode non autorisée'], 405);

$data  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$email = strtolower(trim($data['email'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    json_response(['success' => false, 'error' => 'Email invalide.']);

// Toujours répondre "ok" pour éviter l'énumération
$user = DB::one(
    "SELECT id, COALESCE(first_name, prenom, 'Client') AS prenom
     FROM users WHERE email = :e AND is_active = 1 LIMIT 1",
    ['e' => $email]
);

if ($user) {
    $otp = Security::randomDigits(OTP_LENGTH);

    DB::insertInto('otp_codes', [
        'user_id'    => $user['id'],
        'code_hash'  => password_hash($otp, PASSWORD_BCRYPT, ['cost' => 8]),
        'expires_at' => date('Y-m-d H:i:s', time() + OTP_LIFETIME),
        'used'       => 0,
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    $_SESSION['reset_password'] = [
        'user_id' => $user['id'],
        'email'   => $email,
        'created' => time(),
    ];

    $prenom = $user['prenom'];
    send_otp_email($email, $prenom, $otp);

    if (GTB_DEBUG) {
        error_log("[GTB-DEV] Reset OTP for {$email}: {$otp}");
    }
}

json_response(['success' => true, 'message' => 'Si cet email existe, un code vous a été envoyé.']);
